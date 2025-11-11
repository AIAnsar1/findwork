<?php


namespace App\Services;


use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use App\Models\{TelegramUser, Resume};
use SergiX44\Nutgram\Telegram\Types\Keyboard\{InlineKeyboardButton, InlineKeyboardMarkup};
use App\Helpers\{FormatForChannelTrait, TelegramUserLangTrait, CreationServiceTrait, ChoseState};

class CreateResumeService
{
    use FormatForChannelTrait, TelegramUserLangTrait, CreationServiceTrait, ChoseState;

    protected array $enumFields = [
        'resume' => [
            'employment' => ['full', 'part', 'contract', 'temporary', 'intern'],
            'format' => ['office', 'remote', 'hybrid'],
        ],
    ];

    public function handle(Nutgram $bot, TelegramUser $user, string $callbackData, ?int $messageId = null)
    {
        if ($callbackData === 'resume:create') {
            $this->startCreation($bot, $user, 'resume', $messageId);
            return;
        }

        if (str_starts_with($callbackData, 'show_creation_menu:resume')) {
            $this->showCreationMenu($bot, 'resume', $bot->callbackQuery()->message->message_id);
            return;
        }

        if (str_starts_with($callbackData, 'select_region:')) {
            [, , $field, $region] = explode(':', $callbackData);
            $this->handleRegionSelection($bot, $region, $field);
            return;
        }

        if (str_starts_with($callbackData, 'manual_input:')) {
            [, , $field] = explode(':', $callbackData);
            $this->handleManualInput($bot, $field);
            return;
        }

        if (str_starts_with($callbackData, 'edit_field:resume:')) {
            [, , $field] = explode(':', $callbackData);
            $this->handleFieldEdit($bot, 'resume', $field);
            return;
        }

        if (str_starts_with($callbackData, 'set_enum:resume:')) {
            [, , $field, $value] = explode(':', $callbackData);
            $this->handleEnumSelection($bot, 'resume', $field, $value);
            return;
        }

        if ($callbackData === 'save:resume') {
            $this->saveResume($bot, $user);
            return;
        }
    }

    public function handleMessage(Nutgram $bot): void
    {
        $editingField = $bot->getUserData('editing_field');
        $mode = $bot->getUserData('mode');
        $menuMessageId = $bot->getUserData('menu_message_id');
        $manualInput = $bot->getUserData('manual_input');

        if ($editingField && $mode === 'resume' && $menuMessageId) {
            $data = $bot->getUserData('data', default: []);
            $partialAddress = $bot->getUserData('partial_address');

            if ($editingField === 'address' && $partialAddress) {
                // Step 2 of address input: combine region and manual input
                $data[$editingField] = $partialAddress . ', ' . $bot->message()->text;
                $bot->setUserData('partial_address', null);
            } elseif ($manualInput && $editingField === 'address') {
                // Manual input for the full address from the start
                $data[$editingField] = $bot->message()->text;
                $bot->setUserData('manual_input', false);
            } else {
                // Default input for all other fields
                $data[$editingField] = $bot->message()->text;
            }

            $bot->setUserData('data', $data);
            $bot->setUserData('editing_field', null);

            try {
                $bot->deleteMessage($bot->chatId(), $bot->message()->message_id);
            } catch (\Exception $e) {
                // Игнорируем если сообщение уже удалено
            }

            $this->showCreationMenu($bot, 'resume', $menuMessageId);
        }
    }

    public function handleFieldEdit(Nutgram $bot, string $mode, string $field)
    {
        if ($field === 'address') {
            $lang = $this->tgLang($bot);
            $this->showRegionSelection($bot, $mode, $field, $lang, $bot->callbackQuery()->message->message_id);
            return;
        }

        if (isset($this->enumFields[$mode][$field])) {
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

    public function handleRegionSelection(Nutgram $bot, string $region, string $field)
    {
        $lang = $this->tgLang($bot);
        $regionName = $this->getRegionName($region, $lang);

        $bot->setUserData('partial_address', $regionName);
        $bot->setUserData('editing_field', $field);

        $text = match($lang) {
            'uz' => "Siz tanladingiz: <b>{$regionName}</b>.\n\nEndi manzilingizning qolgan qismini kiriting (masalan: Chirchiq sh., Navoiy k., 15-uy):",
            'en' => "You have selected: <b>{$regionName}</b>.\n\nNow enter the rest of your address (e.g., Chirchik city, Navoi st., 15):",
            default => "Вы выбрали: <b>{$regionName}</b>.\n\nТеперь введите остальную часть адреса (например: г. Чирчик, ул. Навои, д. 15):",
        };

        $bot->editMessageText(
            text: $text,
            chat_id: $bot->callbackQuery()->message->chat->id,
            message_id: $bot->callbackQuery()->message->message_id,
            parse_mode: ParseMode::HTML
        );
    }


    public function handleManualInput(Nutgram $bot, string $field): void
    {
        $lang = $this->tgLang($bot);
        
        $bot->setUserData('editing_field', $field);
        $bot->setUserData('manual_input', true);

        $text = match($lang) {
            'ru' => "✍️ *Введите ваш адрес вручную*\n\nПожалуйста, напишите ваш полный адрес (регион, город, район):",
            'uz' => "✍️ *Manzilingizni qoʻlda kiriting*\n\nIltimos, toʻliq manzilingizni yozing (viloyat, shahar, tuman):",
            'en' => "✍️ *Enter your address manually*\n\nPlease write your full address (region, city, district):",
            default => "✍️ *Enter your address manually*"
        };

        $bot->sendMessage(
            text: $text,
            parse_mode: 'HTML'
        );
    }

    public function handleEnumSelection(Nutgram $bot, string $mode, string $field, string $value)
    {
        $formData = $bot->getUserData('data', default: []);
        $formData[$field] = $value;
        $bot->setUserData('data', $formData);
        $this->showCreationMenu($bot, $mode, $bot->callbackQuery()->message->message_id);
    }

    public function saveResume(Nutgram $bot, TelegramUser $user)
    {
        $data = $bot->getUserData('data', default: []);
        $menuMessageId = $bot->getUserData('menu_message_id');

        // Проверяем обязательные поля
        if (empty($data['position'])) {
            $bot->sendMessage('❌ Пожалуйста, укажите должность.');
            return;
        }

        $data['telegram_user_id'] = $user->id;
        $data['status'] = 'moderation';

        // Преобразуем числовые поля
        if (isset($data['age'])) {
            $data['age'] = (int) $data['age'];
        }
        if (isset($data['salary'])) {
            $data['salary'] = (int) $data['salary'];
        }
        if (isset($data['experience_years'])) {
            $data['experience_years'] = (int) $data['experience_years'];
        }

        $resume = Resume::create($data);

        $this->sendForModeration($bot, 'resume', $resume);
        
        if ($menuMessageId) {
            $bot->editMessageText(
                '✅ Отправлено на модерацию.', 
                chat_id: $bot->chatId(), 
                message_id: $menuMessageId
            );
        }

        $this->clearUserData($bot);
    }

    public function sendForModeration(Nutgram $bot, string $mode, $model): void
    {
        $adminGroupId = config('nutgram.admin_controlls_group_id');
        if (!$adminGroupId) return;

        $text = "Новая запись на модерацию:\n\n";
        $text .= "<b>Тип:</b> Резюме\n";
        
        foreach ($model->toArray() as $key => $value) {
            if ($value && !in_array($key, ['id', 'telegram_user_id', 'created_at', 'updated_at', 'status'])) {
                $label = $this->getQuestions('resume')[$key] ?? $key;
                $cleanLabel = rtrim($label, ':');
                $text .= "<b>{$cleanLabel}:</b> {$value}\n";
            }
        }

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('✅ Одобрить', callback_data: "mod_approve:resume:{$model->id}"),
                InlineKeyboardButton::make('❌ Отклонить', callback_data: "mod_reject:resume:{$model->id}")
            );

        $bot->sendMessage(
            $text, 
            chat_id: $adminGroupId,
            reply_markup: $keyboard,
            parse_mode: 'HTML'
        );
    }

    public function clearUserData(Nutgram $bot): void
    {
        $bot->setUserData('mode', null);
        $bot->setUserData('data', null);
        $bot->setUserData('menu_message_id', null);
        $bot->setUserData('editing_field', null);
        $bot->setUserData('manual_input', null);
    }

    public function getEnumFields()
    {
        return $this->enumFields;
    }


    public function getSteps(string $mode)
    {
        return [
            'full_name',
            'age',
            'address',
            'position',
            'salary',
            'employment',
            'format',
            'experience_years',
            'skills',
            'about',
            'phone'
        ];
    }


    public function getQuestions(string $mode)
    {
        return [
            'full_name' => "Полное имя:",
            'age' => "Возраст:",
            'address' => "Регион:",
            'position' => "Должность:",
            'salary' => "💰 Желаемая зарплата ($):\n*(укажите только число)*",
            'employment' => "Тип занятости:",
            'format' => "Формат работы:",
            'experience_years' => "Опыт работы (лет):",
            'skills' => "Ключевые навыки:",
            'about' => "О себе:",
            'phone' => "Номер телефона:",
        ];
    }
}