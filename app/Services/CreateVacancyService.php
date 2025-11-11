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
        $lang = $this->tgLang($bot);
        if (isset($this->enumFields[$mode][$field])) 
        {
            $this->askEnumOptions($bot, $mode, $field);
            return;
        }
        $bot->setUserData('editing_field', $field);
        $questions = $this->getQuestions($lang);
        $bot->editMessageText(
            text: $questions[$field] ?? __('messages.enter_value_for', ['field' => $field], $lang),
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
        $lang = $this->tgLang($bot);
        $data = $bot->getUserData('data', default: []);
        $menuMessageId = $bot->getUserData('menu_message_id');

        $data['telegram_user_id'] = $user->id;
        $data['status'] = 'moderation';
        $vacancy = Vacancy::create($data);
        $this->sendForModeration($bot, 'vacancy', $vacancy);
        
        if ($menuMessageId) {
            $bot->editMessageText(
                __('messages.moderation.sent', [], $lang), 
                chat_id: $bot->chatId(), 
                message_id: $menuMessageId
            );
        }
        $this->clearUserData($bot);
    }

    public function sendForModeration(Nutgram $bot, string $mode, $model)
    {
        $adminGroupId = config('nutgram.admin_controlls_group_id');
        if (!$adminGroupId) return;

        $lang = $this->tgLang($bot);
        $questions = $this->getQuestions($lang);

        $text = __('messages.moderation.new_item', [], $lang) . "\n\n";
        $text .= "<b>" . __('messages.moderation.type', [], $lang) . ":</b> " . __('messages.moderation.type_vacancy', [], $lang) . "\n";

        foreach ($model->toArray() as $key => $value) 
        {
            if ($value && !in_array($key, ['id', 'telegram_user_id', 'created_at', 'updated_at', 'status'])) 
            {
                $label = rtrim($questions[$key] ?? $key, ':');
                $text .= "<b>{$label}:</b> {$value}\n";
            }
        }
        $keyboard = InlineKeyboardMarkup::make()->addRow(
            InlineKeyboardButton::make(__('messages.moderation.approve', [], $lang), callback_data: "mod_approve:vacancy:{$model->id}"),
            InlineKeyboardButton::make(__('messages.moderation.reject', [], $lang), callback_data: "mod_reject:vacancy:{$model->id}")
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


    public function getQuestions(string $lang)
    {
        return [
            'company' => __('messages.fields.company', [], $lang),
            'position' => __('messages.fields.position', [], $lang),
            'salary' => __('messages.fields.salary', [], $lang),
            'experience' => __('messages.fields.experience', [], $lang),
            'employment' => __('messages.fields.employment', [], $lang),
            'schedule' => __('messages.fields.schedule', [], $lang),
            'work_hours' => __('messages.fields.work_hours', [], $lang),
            'format' => __('messages.fields.format', [], $lang),
            'responsibilities' => __('messages.fields.responsibilities', [], $lang),
            'requirements' => __('messages.fields.requirements', [], $lang),
            'conditions' => __('messages.fields.conditions', [], $lang),
            'benefits' => __('messages.fields.benefits', [], $lang),
            'contact_name' => __('messages.fields.contact_name', [], $lang),
            'contact_phone' => __('messages.fields.contact_phone', [], $lang),
            'contact_email' => __('messages.fields.contact_email', [], $lang),
            'contact_telegram' => __('messages.fields.contact_telegram', [], $lang),
            'address' => __('messages.fields.address', [], $lang),
        ];
    }
}