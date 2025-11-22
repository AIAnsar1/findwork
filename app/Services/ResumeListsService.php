<?php

namespace App\Services;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use App\Models\{TelegramUser, Resume};
use SergiX44\Nutgram\Telegram\Types\Keyboard\{InlineKeyboardButton, InlineKeyboardMarkup};
use App\Helpers\{FormatForChannelTrait, TelegramUserLangTrait, CreationServiceTrait, ChoseState};

class ResumeListsService
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
        $messageId = $messageId ?? $bot->callbackQuery()?->message?->message_id;

        if ($callbackData === 'resume:edit' || $callbackData === 'view_resumes') {
            $this->showResumeList($bot, $user, $messageId);
            return;
        }

        if (str_starts_with($callbackData, 'show_resume:')) {
            [, $resumeId] = explode(':', $callbackData);
            $this->showResumeDetails($bot, (int)$resumeId, $messageId);
            return;
        }

        if (str_starts_with($callbackData, 'edit_resume:')) {
            $this->handleEdit($bot, $user, $callbackData, $messageId);
            return;
        }

        if (str_starts_with($callbackData, 'save:resume')) {
            $this->saveEditedResume($bot, $user, $messageId);
            return;
        }

        if (str_starts_with($callbackData, 'delete_resume:')) {
            [, $resumeId] = explode(':', $callbackData);
            $this->confirmDelete($bot, (int)$resumeId, $messageId);
            return;
        }

        if (str_starts_with($callbackData, 'confirm_delete_resume:')) {
            [, $resumeId] = explode(':', $callbackData);
            $this->deleteResume($bot, $user, (int)$resumeId, $messageId);
            return;
        }

        if (str_starts_with($callbackData, 'toggle_resume_status:')) {
            [, $resumeId] = explode(':', $callbackData);
            $this->toggleStatus($bot, (int)$resumeId, $messageId);
            return;
        }

        if (str_starts_with($callbackData, 'toggle_auto_posting_resume:')) {
            [, $resumeId] = explode(':', $callbackData);
            $this->toggleAutoPosting($bot, (int)$resumeId, $messageId);
            return;
        }
    }

    public function toggleAutoPosting(Nutgram $bot, int $resumeId, int $messageId)
    {
        $resume = Resume::findOrFail($resumeId);
        $resume->update(['auto_posting' => !$resume->auto_posting]);
        $this->showResumeDetails($bot, $resumeId, $messageId);
    }

    public function showResumeList(Nutgram $bot, TelegramUser $user, int $messageId)
    {
        $lang = $this->tgLang($bot);
        $resumes = $user->resumes()->get();

        if ($resumes->isEmpty()) {
            $bot->answerCallbackQuery(__('messages.resume.no_resumes', [], $lang));
            $bot->safeAnswerCallbackQuery();
            return;
        }

        $keyboard = InlineKeyboardMarkup::make();
        foreach ($resumes as $resume) {
            $statusText = __('messages.statuses.' . $resume->status, [], $lang);
            $keyboard->addRow(InlineKeyboardButton::make(
                "{$resume->position} ({$statusText})",
                callback_data: "show_resume:{$resume->id}"
            ));
        }
        $keyboard->addRow(InlineKeyboardButton::make(__('messages.back', [], $lang), callback_data: 'back_to_start'));

        $bot->editMessageText(
            __('messages.resume.list', [], $lang),
            chat_id: $bot->chatId(),
            message_id: $messageId,
            reply_markup: $keyboard
        );
    }

    public function showResumeDetails(Nutgram $bot, int $resumeId, int $messageId)
    {
        $lang = $this->tgLang($bot);
        $resume = Resume::findOrFail($resumeId);

        $text = "<b>{$resume->position}</b>\n\n";
        $fields = $this->getSteps('resume');
        $questions = $this->getQuestions($lang);

        foreach ($fields as $field) {
            if (!empty($resume->$field)) {
                $label = rtrim($questions[$field] ?? $field, ':');
                $text .= "<b>{$label}:</b> {$resume->$field}\n";
            }
        }

        $statusText = __('messages.statuses.' . $resume->status, [], $lang);
        $text .= "\n<b>" . __('messages.status', [], $lang) . ":</b> {$statusText}";

        $autoPostingText = $resume->auto_posting ? __('messages.auto_posting_on', [], $lang) : __('messages.auto_posting_off', [], $lang);

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(__('messages.edit', [], $lang), callback_data: "edit_resume:{$resume->id}"),
                InlineKeyboardButton::make(__('messages.delete', [], $lang), callback_data: "delete_resume:{$resume->id}")
            )
            ->addRow(InlineKeyboardButton::make(
                $resume->status === 'active' ? __('messages.hide', [], $lang) : __('messages.show', [], $lang),
                callback_data: "toggle_resume_status:{$resume->id}"
            ))
            ->addRow(InlineKeyboardButton::make(
                $autoPostingText,
                callback_data: "toggle_auto_posting_resume:{$resume->id}"
            ))
            ->addRow(InlineKeyboardButton::make(__('messages.back', [], $lang), callback_data: 'resume:edit'));

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
        [, $resumeId] = explode(':', $callbackData);
        $resume = Resume::findOrFail($resumeId);

        $bot->setUserData('mode', 'resume'); // Use the same mode as creation
        $bot->setUserData('editing_resume_id', $resumeId);
        $bot->setUserData('data', $resume->toArray());
        $bot->setUserData('menu_message_id', $messageId);

        $this->showCreationMenu($bot, 'resume', $messageId);
    }

    public function saveEditedResume(Nutgram $bot, TelegramUser $user, int $messageId)
    {
        $lang = $this->tgLang($bot);
        $resumeId = $bot->getUserData('editing_resume_id');
        $data = $bot->getUserData('data');

        $resume = Resume::findOrFail($resumeId);
        $resume->update($data);

        // Clear user data
        $bot->setUserData('mode', null);
        $bot->setUserData('editing_resume_id', null);
        $bot->setUserData('data', null);
        $bot->setUserData('menu_message_id', null);

        $this->showResumeDetails($bot, $resumeId, $messageId);
        $bot->answerCallbackQuery(__('messages.resume.updated', [], $lang));
    }

    public function confirmDelete(Nutgram $bot, int $resumeId, int $messageId)
    {
        $lang = $this->tgLang($bot);
        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(__('messages.confirm_delete', [], $lang), callback_data: "confirm_delete_resume:{$resumeId}"),
                InlineKeyboardButton::make(__('messages.cancel', [], $lang), callback_data: "show_resume:{$resumeId}")
            );

        $bot->editMessageText(
            __('messages.resume.confirm_delete_text', [], $lang),
            chat_id: $bot->chatId(),
            message_id: $messageId,
            reply_markup: $keyboard
        );
    }

    public function deleteResume(Nutgram $bot, TelegramUser $user, int $resumeId, int $messageId)
    {
        $lang = $this->tgLang($bot);
        $bot->answerCallbackQuery(__('messages.resume.deleted', [], $lang));
        Resume::where('id', $resumeId)->where('telegram_user_id', $user->id)->delete();
        $this->showResumeList($bot, $user, $messageId);
    }

    public function toggleStatus(Nutgram $bot, int $resumeId, int $messageId)
    {
        $resume = Resume::findOrFail($resumeId);
        $newStatus = $resume->status === 'active' ? 'hidden' : 'active';
        $resume->update(['status' => $newStatus]);
        $this->showResumeDetails($bot, $resumeId, $messageId);
    }

    public function getEnumFields()
    {
        return $this->enumFields;
    }

    public function getSteps(string $mode)
    {
        return [
            'full_name', 'age', 'address', 'position', 'salary', 'employment',
            'format', 'experience_years', 'skills', 'about', 'phone'
        ];
    }

    public function getQuestions(string $lang)
    {
        return [
            'full_name' => __('messages.fields.full_name', [], $lang),
            'age' => __('messages.fields.age', [], $lang),
            'address' => __('messages.fields.address', [], $lang),
            'position' => __('messages.fields.position', [], $lang),
            'salary' => __('messages.fields.salary', [], $lang),
            'employment' => __('messages.fields.employment', [], $lang),
            'format' => __('messages.fields.format', [], $lang),
            'experience_years' => __('messages.fields.experience_years', [], $lang),
            'skills' => __('messages.fields.skills', [], $lang),
            'about' => __('messages.fields.about', [], $lang),
            'phone' => __('messages.fields.phone', [], $lang),
        ];
    }
}
