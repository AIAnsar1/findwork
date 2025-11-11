<?php


namespace App\Services;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use App\Models\{TelegramUser, Vacancy};
use SergiX44\Nutgram\Telegram\Types\Keyboard\{InlineKeyboardButton, InlineKeyboardMarkup};
use App\Helpers\{FormatForChannelTrait, TelegramUserLangTrait, CreationServiceTrait, ChoseState};


class CreateVacancyService
{
    use FormatForChannelTrait, TelegramUserLangTrait, CreationServiceTrait, ChoseState;

    protected array $enumVacancyFields = [
        'employment' => ['full', 'part', 'contract', 'temporary', 'intern'],
        'format' => ['office', 'remote', 'hybrid'],
    ];

    public function handle(Nutgram $bot, TelegramUser $user, string $callbackData, ?int $messageId = null)
    {
        if ($callbackData === 'vacancy:create')
        {
            $this->startCreation($bot, $user, 'vacancy', $messageId);
            return;
        }

        if (str_starts_with($callbackData, 'show_creation_menu:vacancy'))
        {
            $this->showCreationMenu($bot, 'vacancy', $bot->callbackQuery()->message->message_id);
            return;
        }

        if (str_starts_with($callbackData, 'edit_field:vacancy:')) 
        {
            [, , $field] = explode(':', $callbackData);
            $this->handleFieldEdit($bot, 'vacancy', $field);
            return;
        }

        if (str_starts_with($callbackData, 'set_enum:vacancy:')) 
        {
            [, , $field, $value] = explode(':', $callbackData);
            $this->handleEnumSelection($bot, 'vacancy', $field, $value);
            return;
        }

        if ($callbackData === 'save:vacancy') 
        {
            $this->saveVacancy($bot, $user);
            return;
        }
    }

    public function handleMessage(Nutgram $bot)
    {
        $editingField = $bot->getUserData('editing_field');
        $mode = $bot->getUserData('mode');
        $menuMessageId = $bot->getUserData('menu_message_id');

        if ($editingField && $mode === 'vacancy' && $menuMessageId) 
        {
            $data = $bot->getUserData('data', default: []);
            $data[$editingField] = $bot->message()->text;
            $bot->setUserData('data', $data);
            $bot->setUserData('editing_field', null);

            try {
                $bot->deleteMessage($bot->chatId(), $bot->message()->message_id);
            } catch (\Exception $e) { /* Ignore if user deletes message manually */ }

            $this->showCreationMenu($bot, 'vacancy', $menuMessageId);
        }
    }

    public function handleFieldEdit(Nutgram $bot, string $mode, string $field)
    {
        if (isset($this->enumFields[$mode][$field])) 
        {
            $this->askEnumOptions($bot, $mode, $field);
            return;
        }
        $bot->setUserData('editing_field', $field);
        $questions = $this->getQuestions($mode);
        $bot->editMessageText(
            text: $questions[$field] ?? "Введите значение для {$field}:",
            chat_id: $bot->callbackQuery()->message->chat->id,
            message_id: $bot->callbackQuery()->message->message_id
        );
    }

    public function handleEnumSelection(Nutgram $bot, string $mode, string $field, string $value)
    {
        $formData = $bot->getUserData('data', default: []);
        $formData[$field] = $value;
        $bot->setUserData('data', $formData);
        $this->showCreationMenu($bot, $mode, $bot->callbackQuery()->message->message_id);
    }

    public function saveVacancy(Nutgram $bot, TelegramUser $user)
    {
        $data = $bot->getUserData('data', default: []);
        $menuMessageId = $bot->getUserData('menu_message_id');

        $data['telegram_user_id'] = $user->id;
        $data['status'] = 'moderation';
        $vacancy = Vacancy::create($data);
        $this->sendForModeration($bot, 'vacancy', $vacancy);
        
        if ($menuMessageId) {
            $bot->editMessageText('✅ Отправлено на модерацию.', 
                chat_id: $bot->chatId(), 
                message_id: $menuMessageId
            );
        }
        $this->clearUserData($bot);
    }

    public function sendForModeration(Nutgram $bot, string $mode, $model)
    {
        $adminGroupId = config('nutgram.admin_controll_group_id');
        if (!$adminGroupId) return;

        $text = "Новая запись на модерацию:\n\n";
        $text .= "<b>Тип:</b> Вакансия\n";

        foreach ($model->toArray() as $key => $value) 
        {
            if ($value && !in_array($key, ['id', 'telegram_user_id', 'created_at', 'updated_at', 'status'])) 
            {
                $text .= "<b>{$key}:</b> {$value}\n";
            }
        }
        $keyboard = InlineKeyboardMarkup::make()->addRow(
            InlineKeyboardButton::make('✅ Одобрить', callback_data: "mod_approve:vacancy:{$model->id}"),
            InlineKeyboardButton::make('❌ Отклонить', callback_data: "mod_reject:vacancy:{$model->id}")
        );

        $bot->sendMessage($text, 
            chat_id: $adminGroupId,
            reply_markup: $keyboard,
            parse_mode: 'HTML'
        );
    }

    public function clearUserData(Nutgram $bot)
    {
        $bot->setUserData('mode', null);
        $bot->setUserData('data', null);
        $bot->setUserData('menu_message_id', null);
        $bot->setUserData('editing_field', null);
    }

    public function getEnumFields()
    {
        return $this->enumVacancyFields;
    }


    public function getSteps(string $mode)
    {
        return [
            'company',
            'position',
            'salary',
            'experience',
            'employment',
            'schedule',
            'work_hours',
            'format',
            'responsibilities',
            'requirements',
            'conditions',
            'benefits',
            'contact_name',
            'contact_phone',
            'contact_email',
            'contact_telegram',
            'address'
        ];
    }


    public function getQuestions(string $mode)
    {
        return [
            'company' => 'Название компании:',
            'position' => 'Должность:',
            'salary' => '💰 Зарплата ($):\n*(укажите только число)*',
            'experience' => 'Требуемый опыт:',
            'employment' => 'Тип занятости:',
            'schedule' => 'График:',
            'work_hours' => 'Рабочие часы:',
            'format' => 'Формат работы:',
            'responsibilities' => 'Обязанности:',
            'requirements' => 'Требования:',
            'conditions' => 'Условия:',
            'benefits' => 'Бонусы:',
            'contact_name' => 'Контактное лицо:',
            'contact_phone' => 'Контактный телефон:',
            'contact_email' => 'Контактный Email:',
            'contact_telegram' => 'Контактный Telegram:',
            'address' => 'Адрес:',
        ];
    }
}