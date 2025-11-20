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
        $lang = $this->tgLang($bot);
        if ($field === 'address') {
            $this->showRegionSelection($bot, $mode, $field, $lang, $bot->callbackQuery()->message->message_id);
            return;
        }

        if (isset($this->enumFields[$mode][$field])) {
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

    public function handleRegionSelection(Nutgram $bot, string $region, string $field)
    {
        $lang = $this->tgLang($bot);
        $regionName = $this->getRegionName($region, $lang);

        $bot->setUserData('partial_address', $regionName);
        $bot->setUserData('editing_field', $field);

        $text = __('messages.address.selected_region', ['regionName' => $regionName], $lang) . "\n\n" . __('messages.address.enter_rest', [], $lang);

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

        $text = __('messages.address.manual_input_prompt', [], $lang);

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
        $lang = $this->tgLang($bot);
        $data = $bot->getUserData('data', default: []);
        $menuMessageId = $bot->getUserData('menu_message_id');

        // Проверяем обязательные поля
        if (empty($data['position'])) {
            $bot->sendMessage(__('messages.errors.position_required', [], $lang));
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
                __('messages.moderation.sent', [], $lang), 
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

        $lang = $this->tgLang($bot);
        $questions = $this->getQuestions($lang);

        $text = __('messages.moderation.new_item', [], $lang) . "\n\n";
        $text .= "<b>" . __('messages.moderation.type', [], $lang) . ":</b> " . __('messages.moderation.type_resume', [], $lang) . "\n";
        
        foreach ($model->toArray() as $key => $value) {
            if ($value && !in_array($key, ['id', 'telegram_user_id', 'created_at', 'updated_at', 'status'])) {
                $label = rtrim($questions[$key] ?? $key, ':');
                $cleanLabel = rtrim($label, ':');
                $text .= "<b>{$cleanLabel}:</b> {$value}\n";
            }
        }

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(__('messages.moderation.approve', [], $lang), callback_data: "mod_approve:resume:{$model->id}"),
                InlineKeyboardButton::make(__('messages.moderation.reject', [], $lang), callback_data: "mod_reject:resume:{$model->id}")
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


    public function getQuestions(string $lang)
    {
        return [
            'full_name' => __('messages.resume.full_name', [], $lang),
            'age' => __('messages.resume.age', [], $lang),
            'address' => __('messages.resume.address', [], $lang),
            'position' => __('messages.resume.title', [], $lang),
            'salary' => __('messages.resume.salary', [], $lang),
            'employment' => __('messages.resume.employment', [], $lang),
            'format' => __('messages.resume.format', [], $lang),
            'experience_years' => __('messages.resume.years', [], $lang),
            'skills' => __('messages.resume.skills', [], $lang),
            'about' => __('messages.resume.about', [], $lang),
            'phone' => __('messages.resume.phone', [], $lang),
        ];
    }
}