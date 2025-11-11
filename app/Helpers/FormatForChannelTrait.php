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
        $text .= "📞 <b>".__('messages.resume.phone', [], $lang).":</b>\n {$resume->phone}\n\n";
        $text .= "📝 <b>".__('messages.resume.about', [], $lang).":</b>\n {$resume->about}\n";
        $text .= "\n\n💼 <a href=\"https://t.me/HeadHuntuz\">HeadHunt Uz</a>";

        $keyboard = null;
        
        if ($resume->telegramUser->username) 
        {
            $keyboard = InlineKeyboardMarkup::make()->addRow(
                InlineKeyboardButton::make(
                    text: __('messages.resume.contact', [], $lang), 
                    url: "https://t.me/{$resume->telegramUser->username}"
                )
            );
        }
        return ['text' => $text, 'keyboard' => $keyboard];
    }

    public function formatVacancyForChannel(Vacancy $vacancy, Nutgram $bot)
    {
        $lang = $this->tgLang($bot);

        $text  = "<b>".__('messages.vacancy.title', [], $lang)." {$vacancy->position} " .__('messages.vacancy.in', [], $lang)." {$vacancy->company}</b>\n\n";
        $text .= "💰 <b>".__('messages.vacancy.salary', [], $lang).":</b>\n {$vacancy->salary}$\n";
        $text .= "📈 <b>".__('messages.vacancy.experience', [], $lang).":</b>\n {$vacancy->experience}\n";
        $text .= "🗓️ <b>".__('messages.vacancy.employment', [], $lang).":</b>\n {$vacancy->employment}\n";
        $text .= "⏰ <b>".__('messages.vacancy.schedule', [], $lang).":</b>\n {$vacancy->schedule} ({$vacancy->work_hours} ". __('messages.vacancy.hours', [], $lang).")\n";
        $text .= "🖥️ <b>".__('messages.vacancy.format', [], $lang).":</b>\n {$vacancy->format}\n";
        $text .= "📍 <b>".__('messages.vacancy.address', [], $lang).":</b>\n {$vacancy->address}\n\n";
        $text .= "📋 <b>".__('messages.vacancy.responsibilities', [], $lang).":</b>\n{$vacancy->responsibilities}\n\n";
        $text .= "✅ <b>".__('messages.vacancy.requirements', [], $lang).":</b>\n{$vacancy->requirements}\n\n";
        $text .= "🎁 <b>".__('messages.vacancy.conditions', [], $lang).":</b>\n{$vacancy->conditions}\n{$vacancy->benefits}\n";
        $text .= "\n\n💼 <a href=\"https://t.me/HeadHuntuz\">HeadHunt Uz</a>";

        $keyboard = null;

        if ($vacancy->contact_telegram) 
        {
             $keyboard = InlineKeyboardMarkup::make()->addRow(
                InlineKeyboardButton::make(
                    text: __('messages.vacancy.apply', [], $lang), 
                    url: "https://t.me/{$vacancy->contact_telegram}"
                )
            );
        }
        return ['text' => $text, 'keyboard' => $keyboard];
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
            text: $payload['text'], 
            chat_id: $channelId,
            parse_mode: 'HTML',
            reply_markup: $payload['keyboard'],
        );
    }

    public function handleModeration(Nutgram $bot, string $mode, int $id, string $action)
    {
        $lang = $this->tgLang($bot); // Используем переданный $bot, а не $this->bot
        
        $moderatorId = $bot->userId();
        $adminGroupId = config('nutgram.admin_controlls_group_id'); // Убрал лишний параметр
        $messageId = $bot->callbackQuery()->message->message_id;
        $model = $mode === 'resume' ? Resume::find($id) : Vacancy::find($id);

        if (!$model) 
        {
            $bot->answerCallbackQuery(text: 'Ошибка: Запись не найдена!');
            return;
        }

        if ($action === 'approve') 
        {
            $newStatus = $mode === 'resume' ? 'active' : 'open';
            $model->update(['status' => $newStatus]);
            
            // Уведомляем пользователя
            $bot->sendMessage(
                '🎉 Ваше объявление было одобрено!', 
                chat_id: $model->telegramUser->user_id
            );
            
            // Публикуем в канал
            $this->postToChannel($bot, $mode, $model);
            
            // Обновляем сообщение модерации
            $bot->editMessageText(
                "ОДОБРЕНО И ОПУБЛИКОВАНО (модератор: {$bot->user()->first_name})", 
                chat_id: $adminGroupId,
                message_id: $messageId,
            );
        }

        if ($action === 'reject') 
        {
            $bot->setUserData('rejecting_item', [
                'mode' => $mode, 
                'id' => $id, 
                'message_id' => $messageId,
                'admin_group_id' => $adminGroupId // Добавляем ID админ группы для rejection
            ], $moderatorId);
            
            $bot->sendMessage(
                "Укажите причину отказа для записи #{$id}", 
                chat_id: $adminGroupId
            );
            
            $bot->editMessageText(
                "ОЖИДАЕТ ПРИЧИНУ ОТКАЗА (модератор: {$bot->user()->first_name})", 
                chat_id: $adminGroupId,
                message_id: $messageId,
            );
        }
    }
}