<?php 

namespace App\Services;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use App\Models\{TelegramUser, Vacancy};
use SergiX44\Nutgram\Telegram\Types\Keyboard\{InlineKeyboardButton, InlineKeyboardMarkup};
use App\Helpers\{FormatForChannelTrait, TelegramUserLangTrait, CreationServiceTrait, ChoseState};

class VacancyListService
{
    use FormatForChannelTrait, TelegramUserLangTrait, CreationServiceTrait, ChoseState;

    protected array $enumVacancyFields = [
        'employment' => ['full', 'part', 'contract', 'temporary', 'intern'],
        'format' => ['office', 'remote', 'hybrid'],
    ];

    public function handle(Nutgram $bot, TelegramUser $user, string $callbackData, ?int $messageId = null)
    {
        $messageId = $messageId ?? $bot->callbackQuery()?->message?->message_id;

        if ($callbackData === 'vacancy:edit' || $callbackData === 'view_vacancies') {
            $this->showVacancyList($bot, $user, $messageId);
            return;
        }

        if (str_starts_with($callbackData, 'show_vacancy:')) {
            [, $vacancyId] = explode(':', $callbackData);
            $this->showVacancyDetails($bot, (int)$vacancyId, $messageId);
            return;
        }

        if (str_starts_with($callbackData, 'edit_vacancy:')) {
            $this->handleEdit($bot, $user, $callbackData, $messageId);
            return;
        }

        if (str_starts_with($callbackData, 'save:vacancy')) {
            $this->saveEditedVacancy($bot, $user, $messageId);
            return;
        }

        if (str_starts_with($callbackData, 'delete_vacancy:')) {
            [, $vacancyId] = explode(':', $callbackData);
            $this->confirmDelete($bot, (int)$vacancyId, $messageId);
            return;
        }

        if (str_starts_with($callbackData, 'confirm_delete_vacancy:')) {
            [, $vacancyId] = explode(':', $callbackData);
            $this->deleteVacancy($bot, $user, (int)$vacancyId, $messageId);
            return;
        }

        if (str_starts_with($callbackData, 'toggle_vacancy_status:')) {
            [, $vacancyId] = explode(':', $callbackData);
            $this->toggleStatus($bot, (int)$vacancyId, $messageId);
            return;
        }

        if (str_starts_with($callbackData, 'toggle_auto_posting_vacancy:')) {
            [, $vacancyId] = explode(':', $callbackData);
            $this->toggleAutoPosting($bot, (int)$vacancyId, $messageId);
            return;
        }
    }

    public function toggleAutoPosting(Nutgram $bot, int $vacancyId, int $messageId)
    {
        $vacancy = Vacancy::findOrFail($vacancyId);
        $vacancy->update(['auto_posting' => !$vacancy->auto_posting]);
        $this->showVacancyDetails($bot, $vacancyId, $messageId);
    }

    public function showVacancyList(Nutgram $bot, TelegramUser $user, int $messageId)
    {
        $lang = $this->tgLang($bot);
        $vacancies = $user->vacancies()->get();

        if ($vacancies->isEmpty()) {
            $bot->answerCallbackQuery(__('messages.vacancy.no_vacancies', [], $lang));
            return;
        }

        $keyboard = InlineKeyboardMarkup::make();
        foreach ($vacancies as $vacancy) {
            $statusText = __('messages.statuses.' . $vacancy->status, [], $lang);
            $keyboard->addRow(InlineKeyboardButton::make(
                "{$vacancy->position} ({$statusText})",
                callback_data: "show_vacancy:{$vacancy->id}"
            ));
        }
        $keyboard->addRow(InlineKeyboardButton::make(__('messages.back', [], $lang), callback_data: 'back_to_start'));

        $bot->editMessageText(
            __('messages.vacancy.list', [], $lang),
            chat_id: $bot->chatId(),
            message_id: $messageId,
            reply_markup: $keyboard
        );
    }

    public function showVacancyDetails(Nutgram $bot, int $vacancyId, int $messageId)
    {
        $lang = $this->tgLang($bot);
        $vacancy = Vacancy::findOrFail($vacancyId);

        $text = "<b>{$vacancy->position}</b>\n\n";
        $fields = $this->getSteps('vacancy');
        $questions = $this->getQuestions($lang);

        foreach ($fields as $field) {
            if (!empty($vacancy->$field)) {
                $label = rtrim($questions[$field] ?? $field, ':');
                $text .= "<b>{$label}:</b> {$vacancy->$field}\n";
            }
        }
        $statusText = __('messages.statuses.' . $vacancy->status, [], $lang);
        $text .= "\n<b>" . __('messages.status', [], $lang) . ":</b> {$statusText}";

        $autoPostingText = $vacancy->auto_posting ? __('messages.auto_posting_on', [], $lang) : __('messages.auto_posting_off', [], $lang);

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(__('messages.edit', [], $lang), callback_data: "edit_vacancy:{$vacancy->id}"),
                InlineKeyboardButton::make(__('messages.delete', [], $lang), callback_data: "delete_vacancy:{$vacancy->id}")
            )
            ->addRow(InlineKeyboardButton::make(
                $vacancy->status === 'open' ? __('messages.hide', [], $lang) : __('messages.show', [], $lang),
                callback_data: "toggle_vacancy_status:{$vacancy->id}"
            ))
            ->addRow(InlineKeyboardButton::make(
                $autoPostingText,
                callback_data: "toggle_auto_posting_vacancy:{$vacancy->id}"
            ))
            ->addRow(InlineKeyboardButton::make(__('messages.back', [], $lang), callback_data: 'vacancy:edit'));

        $bot->editMessageText(
            $text,
            chat_id: $bot->chatId(),
            message_id: $messageId,
            parse_mode: ParseMode::HTML,
            reply_markup: $keyboard
        );
    }

    public function handleEdit(Nutgram $bot, TelegramUser $user, string $callbackData, int $messageId)
    {
        [, $vacancyId] = explode(':', $callbackData);
        $vacancy = Vacancy::findOrFail($vacancyId);

        $bot->setUserData('mode', 'vacancy'); // Use the same mode as creation
        $bot->setUserData('editing_vacancy_id', $vacancyId);
        $bot->setUserData('data', $vacancy->toArray());
        $bot->setUserData('menu_message_id', $messageId);

        $this->showCreationMenu($bot, 'vacancy', $messageId);
    }

    public function saveEditedVacancy(Nutgram $bot, TelegramUser $user, int $messageId)
    {
        $lang = $this->tgLang($bot);
        $vacancyId = $bot->getUserData('editing_vacancy_id');
        $data = $bot->getUserData('data');

        $vacancy = Vacancy::findOrFail($vacancyId);
        $vacancy->update($data);

        // Clear user data
        $bot->setUserData('mode', null);
        $bot->setUserData('editing_vacancy_id', null);
        $bot->setUserData('data', null);
        $bot->setUserData('menu_message_id', null);

        $bot->answerCallbackQuery(__('messages.vacancy.updated', [], $lang));
        $this->showVacancyDetails($bot, $vacancyId, $messageId);
    }

    public function confirmDelete(Nutgram $bot, int $vacancyId, int $messageId)
    {
        $lang = $this->tgLang($bot);
        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(__('messages.confirm_delete', [], $lang), callback_data: "confirm_delete_vacancy:{$vacancyId}"),
                InlineKeyboardButton::make(__('messages.cancel', [], $lang), callback_data: "show_vacancy:{$vacancyId}")
            );

        $bot->editMessageText(
            __('messages.vacancy.confirm_delete_text', [], $lang),
            chat_id: $bot->chatId(),
            message_id: $messageId,
            reply_markup: $keyboard
        );
    }

    public function deleteVacancy(Nutgram $bot, TelegramUser $user, int $vacancyId, int $messageId)
    {
        $lang = $this->tgLang($bot);
        $bot->answerCallbackQuery(__('messages.vacancy.deleted', [], $lang));
        Vacancy::where('id', $vacancyId)->where('telegram_user_id', $user->id)->delete();
        $this->showVacancyList($bot, $user, $messageId);
    }

    public function toggleStatus(Nutgram $bot, int $vacancyId, int $messageId)
    {
        $lang = $this->tgLang($bot);
        $vacancy = Vacancy::findOrFail($vacancyId);
        $newStatus = $vacancy->status === 'open' ? 'closed' : 'open';
        $vacancy->update(['status' => $newStatus]);
        $bot->answerCallbackQuery(__('messages.vacancy.status_changed', [], $lang));
        $this->showVacancyDetails($bot, $vacancyId, $messageId);
    }

    public function getEnumFields()
    {
        return $this->enumVacancyFields;
    }

    public function getSteps(string $mode)
    {
        return [
            'company', 'position', 'salary', 'experience', 'employment', 'schedule', 'work_hours',
            'format', 'responsibilities', 'requirements', 'conditions', 'benefits', 'contact_name',
            'contact_phone', 'contact_email', 'contact_telegram', 'address'
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
