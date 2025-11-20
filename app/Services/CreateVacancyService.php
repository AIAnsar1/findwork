<?php


namespace App\Services;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use Illuminate\Support\Facades\Log;
use App\Models\{TelegramUser, Vacancy};
use SergiX44\Nutgram\Telegram\Types\Keyboard\{InlineKeyboardButton, InlineKeyboardMarkup};
use App\Helpers\{FormatForChannelTrait, TelegramUserLangTrait, CreationServiceTrait, ChoseState};


class CreateVacancyService
{
    use FormatForChannelTrait, TelegramUserLangTrait, CreationServiceTrait, ChoseState;

    protected array $enumFields = [
        'vacancy' => [
            'employment' => ['full', 'part', 'contract', 'temporary', 'intern'],
            'format' => ['office', 'remote', 'hybrid'],
        ],
    ];

    public function handle(Nutgram $bot, TelegramUser $user, string $callbackData, ?int $messageId = null)
    {
        if ($callbackData === 'vacancy:create') {
            $this->startCreation($bot, $user, 'vacancy', $messageId);
            return;
        }

        if (str_starts_with($callbackData, 'show_creation_menu:vacancy')) {
            $this->showCreationMenu($bot, 'vacancy', $bot->callbackQuery()->message->message_id);
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

        if (str_starts_with($callbackData, 'edit_field:vacancy:')) {
            [, , $field] = explode(':', $callbackData);
            $this->handleFieldEdit($bot, 'vacancy', $field);
            return;
        }

        if (str_starts_with($callbackData, 'set_enum:vacancy:')) {
            [, , $field, $value] = explode(':', $callbackData);
            $this->handleEnumSelection($bot, 'vacancy', $field, $value);
            return;
        }

        if ($callbackData === 'save:vacancy') {
            $this->saveVacancy($bot, $user);
            return;
        }
    }

    public function handleMessage(Nutgram $bot): void
    {
        $editingField = $bot->getUserData('editing_field');
        $mode = $bot->getUserData('mode');
        $menuMessageId = $bot->getUserData('menu_message_id');
        $manualInput = $bot->getUserData('manual_input');

        if ($editingField && $mode === 'vacancy' && $menuMessageId) {
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

            $this->showCreationMenu($bot, 'vacancy', $menuMessageId);
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
            parse_mode: 'HTML' // Исправлено на строку
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

    public function saveVacancy(Nutgram $bot, TelegramUser $user)
    {
        $lang = $this->tgLang($bot);
        $data = $bot->getUserData('data', default: []);
        $menuMessageId = $bot->getUserData('menu_message_id');

        // 🔴 Обязательные поля для вакансии
        $requiredFields = ['company', 'position'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $label = $this->getQuestions($lang)[$field] ?? __('messages.' . $field);
                $bot->sendMessage(__('messages.errors.field_required', ['field' => rtrim($label, ':')], $lang));
                return;
            }
        }

        $data['telegram_user_id'] = $user->id;
        $data['status'] = 'moderation';

        // Преобразуем salary только если это число
        
        // experience — строка (например: "1–3 года", "нет опыта") → не трогаем

        try {
            $vacancy = Vacancy::create($data);
        } catch (\Throwable $e) {
            Log::error('Vacancy creation failed', [
                'user_id' => $user->id,
                'data' => $data,
                'error' => $e->getMessage(),
                'sql_state' => $e->getPrevious()?->getCode(),
                'sql_message' => $e->getPrevious()?->getMessage(),
            ]);

            $bot->sendMessage(__('messages.errors.save_failed', [], $lang));
            return;
        }

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

    public function sendForModeration(Nutgram $bot, string $mode, $model): void
    {
        $adminGroupId = config('nutgram.admin_controlls_group_id');
        if (!$adminGroupId) return;

        $lang = $this->tgLang($bot);
        $questions = $this->getQuestions($lang);

        $text = __('messages.moderation.new_item', [], $lang) . "\n\n";
        $text .= "<b>" . __('messages.moderation.type', [], $lang) . ":</b> " . __('messages.moderation.type_vacancy', [], $lang) . "\n";
        
        foreach ($model->toArray() as $key => $value) {
            if ($value && !in_array($key, ['id', 'telegram_user_id', 'created_at', 'updated_at', 'status'])) {
                $label = rtrim($questions[$key] ?? $key, ':');
                $cleanLabel = rtrim($label, ':');
                $text .= "<b>{$cleanLabel}:</b> {$value}\n";
            }
        }

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(__('messages.moderation.approve', [], $lang), callback_data: "mod_approve:vacancy:{$model->id}"),
                InlineKeyboardButton::make(__('messages.moderation.reject', [], $lang), callback_data: "mod_reject:vacancy:{$model->id}")
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
        $bot->setUserData('manual_input', null); // Добавлено
        $bot->setUserData('partial_address', null); // Добавлено
    }

    public function getEnumFields()
    {
        return $this->enumFields;
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
            'contact_phone',
            'contact_email',
            'address' // Теперь использует логику региона как в резюме
        ];
    }

    public function getQuestions(string $lang)
    {
        return [
            'company' => __('messages.vacancy.company', [], $lang),
            'position' => __('messages.vacancy.position', [], $lang),
            'salary' => __('messages.vacancy.salary', [], $lang),
            'experience' => __('messages.vacancy.experience', [], $lang),
            'employment' => __('messages.vacancy.employment', [], $lang),
            'schedule' => __('messages.vacancy.schedule', [], $lang),
            'work_hours' => __('messages.vacancy.hours', [], $lang),
            'format' => __('messages.vacancy.format', [], $lang),
            'responsibilities' => __('messages.vacancy.responsibilities', [], $lang),
            'requirements' => __('messages.vacancy.requirements', [], $lang),
            'conditions' => __('messages.vacancy.conditions', [], $lang),
            'benefits' => __('messages.vacancy.benefits', [], $lang),
            'contact_phone' => __('messages.vacancy.contact_phone', [], $lang),
            'contact_email' => __('messages.vacancy.contact_email', [], $lang),
            'address' => __('messages.vacancy.address', [], $lang),
        ];
    }
}