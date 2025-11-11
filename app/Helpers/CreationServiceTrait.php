<?php


namespace App\Helpers;

use App\Models\TelegramUser;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\{InlineKeyboardMarkup, InlineKeyboardButton};


trait CreationServiceTrait
{
    public function startCreation(Nutgram $bot, TelegramUser $user, string $mode)
    {
        $bot->setUserData('mode', $mode);
        $bot->setUserData('data', []);
        $this->showCreationMenu($bot, $mode);
    }

    public function showCreationMenu(Nutgram $bot, string $mode, ?int $messageId = null)
    {
        $formData = $bot->getUserData('data', default: []);
        $steps = $this->getSteps($mode);
        $labels = $this->getQuestions($mode);
        $text = "📋 **" . ($mode === 'resume' ? 'Создание резюме' : 'Создание вакансии') . "**\n\n";
        $text .= "Заполните поля, нажимая на кнопки. Когда закончите, нажмите 'Сохранить'.\n\n";
        $keyboard = InlineKeyboardMarkup::make();

        foreach ($steps as $field) {
            $label = rtrim($labels[$field] ?? $field, ':');
            $value = $formData[$field] ?? null;
            
            $text .= "<b>{$label}:</b> " . ($value ?? '...') . "\n";
            
            $buttonText = $value ? mb_strimwidth($value, 0, 20, "...") : $label;
            $keyboard->addRow(InlineKeyboardButton::make("✏️ {$buttonText}", callback_data: "edit_field:{$mode}:{$field}"));
        }

        $keyboard->addRow(
            InlineKeyboardButton::make("✅ Сохранить", callback_data: "save:{$mode}"),
            InlineKeyboardButton::make("⬅️ Назад", callback_data: "back_to_start")
        );

        if ($messageId) {
            try {
                $bot->editMessageText($text, 
                    chat_id: $bot->chatId(),
                    message_id: $messageId,
                    reply_markup: $keyboard,
                    parse_mode: 'HTML'
                );
            } catch (\Exception $e) { /* Ignore if message not modified */ }
        } else {
            $sentMessage = $bot->sendMessage($text, 
                reply_markup: $keyboard,
                parse_mode: 'HTML'
            );
            $bot->setUserData('menu_message_id', $sentMessage->message_id);
        }
    }

    public function askEnumOptions(Nutgram $bot, string $mode, string $field)
    {
        $callbackQuery = $bot->callbackQuery();
        $options = $this->getEnumFields()[$mode][$field];
        $labels = $this->getQuestions($mode);
        $text = "Выберите один из вариантов для поля `{$labels[$field]}`:";
        $keyboard = InlineKeyboardMarkup::make();

        foreach ($options as $option) 
        {
            $keyboard->addRow(InlineKeyboardButton::make($option, callback_data: "set_enum:{$mode}:{$field}:{$option}"));
        }
        $keyboard->addRow(InlineKeyboardButton::make('⬅️ Назад', callback_data: "show_creation_menu:{$mode}"));

        $bot->editMessageText(
            text: $text,
            chat_id: $callbackQuery->message->chat->id,
            message_id: $callbackQuery->message->message_id,
            reply_markup: $keyboard,
        );
    }


    abstract public function getEnumFields();
    abstract public function getSteps(string $mode);
    abstract public function getQuestions(string $mode);
}


































