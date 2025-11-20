<?php


namespace App\Helpers;

use SergiX44\Nutgram\Nutgram;
use App\Models\{Resume, Vacancy, TelegramUser};
use SergiX44\Nutgram\Telegram\Types\Keyboard\{InlineKeyboardMarkup,InlineKeyboardButton};

trait FormatForChannelTrait
{
    use TelegramUserLangTrait;

    public function formatResumeForChannel(Resume $resume, Nutgram $bot)
    {
        $lang = $this->tgLang($bot);

        $text  = "<b>".__('messages.resume.title', [], $lang).": {$resume->position}</b>\n\n";
        $text .= "👤 <b>".__('messages.resume.full_name', [], $lang).":</b>\n {$resume->full_name}\n";
        $text .= "🎂 <b>".__('messages.resume.age', [], $lang).":</b>\n {$resume->age}\n";
        $text .= "📍 <b>".__('messages.resume.address', [], $lang).":</b>\n {$resume->address}\n";
        $text .= "💰 <b>".__('messages.resume.salary', [], $lang).":</b>\n {$resume->salary}$\n";
        $text .= "🗓️ <b>".__('messages.resume.employment', [], $lang).":</b>\n {$resume->employment}\n";
        $text .= "🖥️ <b>".__('messages.resume.format', [], $lang).":</b>\n {$resume->format}\n";
        $text .= "📈 <b>".__('messages.resume.experience', [], $lang).":</b> {$resume->experience_years} ". __('messages.resume.years', [], $lang)."\n";
        $text .= "🛠️ <b>".__('messages.resume.skills', [], $lang).":</b>\n {$resume->skills}\n";
        $text .= "📞 <b>".__('messages.resume.phone', [], $lang).":</b>\n {$resume->phone}\n";
        $text .= "📞 <b>".__('messages.resume.telegtram', [], $lang).":</b>\n @{$resume->telegramUser->username}\n\n";
        $text .= "📝 <b>".__('messages.resume.about', [], locale: $lang).":</b>\n {$resume->about}\n";
        $text .= "\n\n💼 <a href=\"https://t.me/HeadHuntuz\">HeadHunt Uz</a>";


        return $text;
    }

    public function formatVacancyForChannel(Vacancy $vacancy, Nutgram $bot)
    {
        $lang = $this->tgLang($bot);

        $text  = "<b>".__('messages.vacancy.title', [], $lang)." {$vacancy->position} " .__('messages.vacancy.in', [], $lang)." {$vacancy->company}</b>\n\n";
        $text .= "💰 <b>".__('messages.vacancy.salary', [], $lang).":</b> {$vacancy->salary}$\n";
        $text .= "📈 <b>".__('messages.vacancy.experience', [], $lang).":</b> {$vacancy->experience}\n";
        $text .= "🗓️ <b>".__('messages.vacancy.employment', [], $lang).":</b> {$vacancy->employment}\n";
        $text .= "⏰ <b>".__('messages.vacancy.schedule', [], $lang).":</b>\n {$vacancy->schedule} ({$vacancy->work_hours} ". __('messages.vacancy.hours', [], $lang).")\n";
        $text .= "🖥️ <b>".__('messages.vacancy.format', [], $lang).":</b>\n {$vacancy->format}\n";
        $text .= "📍 <b>".__('messages.vacancy.address', [], $lang).":</b>\n {$vacancy->address}\n";
        $text .= "📍 <b>".__('messages.vacancy.telegram', [], $lang).":</b>\n @{$vacancy->telegramUser->username}\n\n";
        $text .= "📋 <b>".__('messages.vacancy.responsibilities', [], $lang).":</b>\n{$vacancy->responsibilities}\n\n";
        $text .= "✅ <b>".__('messages.vacancy.requirements', [], $lang).":</b>\n{$vacancy->requirements}\n\n";
        $text .= "🎁 <b>".__('messages.vacancy.conditions', [], $lang).":</b>\n{$vacancy->conditions}\n{$vacancy->benefits}\n";
        $text .= "\n\n💼 <a href=\"https://t.me/HeadHuntuz\">HeadHunt Uz</a>";

        return $text;
    }

    public function postToChannel(Nutgram $bot, string $mode, $model)
    {
        $channelId = config('nutgram.telegram_channel_id');

        if (!$channelId)
        {
            return;
        }

        if ($mode === 'resume')
        {
            $payload = $this->formatResumeForChannel($model, $bot);
        }
        else
        {
            $payload = $this->formatVacancyForChannel($model, $bot);
        }

        $bot->sendMessage(
            text: $payload,
            chat_id: $channelId,
            parse_mode: 'HTML',
        );
    }

    public function handleModeration(Nutgram $bot, string $mode, int $id, string $action)
    {
        $lang = $this->tgLang($bot); // Используем переданный $bot, а не $this->bot

        $moderatorId = $bot->userId();
        $adminGroupId = config('nutgram.admin_controlls_group_id'); // Убрал лишний параметр
        $messageId = $bot->callbackQuery()->message->message_id;
        $model = $mode === 'resume' ? Resume::find($id) : Vacancy::find($id);

        if (!$model) {
            $bot->answerCallbackQuery(text: __('messages.errors.not_found', [], $lang));
            return;
        }

        if ($action === 'approve') {
            // Уведомляем пользователя
            $bot->sendMessage(
                __('messages.moderation.approved_user_notification', [], $lang),
                chat_id: $model->telegramUser->user_id
            );
            // Публикуем в канал
            $this->postToChannel($bot, $mode, $model, $lang);
            // Обновляем сообщение модерации
            $bot->editMessageText(
                __('messages.moderation.approved_admin_notification', ['moderator' => $bot->user()->first_name], $lang),
                chat_id: $adminGroupId,
                message_id: $messageId,
            );
        } elseif ($action === 'reject') {
            // Сохраняем контекст для отклонения
            $bot->setUserData('rejecting_item', [
                'mode' => $mode,
                'id' => $id,
                'message_id' => $messageId,
                'admin_group_id' => $adminGroupId
            ], $bot->userId());

            $bot->editMessageText(
                __('messages.moderation.rejection_reason_prompt', ['id' => $id], $lang),
                chat_id: $adminGroupId,
                message_id: $messageId,
            );
            $bot->answerCallbackQuery(
                __('messages.moderation.rejection_reason_prompt_short', [], $lang)
            );
        }
    }
}
