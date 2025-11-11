<?php


namespace App\Helpers;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\{InlineKeyboardButton, InlineKeyboardMarkup};

trait ChoseState
{
    protected array $regions = [
        'tashkent_city' => ['ru' => '🗺️ Город Ташкент', 'uz' => '🗺️ Toshkent shahri', 'en' => '🗺️ Tashkent City'],
        'tashkent_region' => ['ru' => '🗺️ Ташкентская область', 'uz' => '🗺️ Toshkent viloyati', 'en' => '🗺️ Tashkent Region'],
        'samarkand' => ['ru' => '🗺️ Самарканд', 'uz' => '🗺️ Samarqand', 'en' => '🗺️ Samarkand'],
        'bukhara' => ['ru' => '🗺️ Бухара', 'uz' => '🗺️ Buxoro', 'en' => '🗺️ Bukhara'],
        'khorezm' => ['ru' => '🗺️ Хорезм', 'uz' => '🗺️ Xorazm', 'en' => '🗺️ Khorezm'],
        'navoi' => ['ru' => '🗺️ Навои', 'uz' => '🗺️ Navoiy', 'en' => '🗺️ Navoi'],
        'jizzakh' => ['ru' => '🗺️ Джизак', 'uz' => '🗺️ Jizzax', 'en' => '🗺️ Jizzakh'],
        'sirdaryo' => ['ru' => '🗺️ Сырдарья', 'uz' => '🗺️ Sirdaryo', 'en' => '🗺️ Sirdaryo'],
        'andijan' => ['ru' => '🗺️ Андижан', 'uz' => '🗺️ Andijon', 'en' => '🗺️ Andijan'],
        'fergana' => ['ru' => '🗺️ Фергана', 'uz' => '🗺️ Fargʻona', 'en' => '🗺️ Fergana'],
        'namangan' => ['ru' => '🗺️ Наманган', 'uz' => '🗺️ Namangan', 'en' => '🗺️ Namangan'],
        'kashkadarya' => ['ru' => '🗺️ Кашкадарья', 'uz' => '🗺️ Qashqadaryo', 'en' => '🗺️ Kashkadarya'],
        'surkhandarya' => ['ru' => '🗺️ Сурхандарья', 'uz' => '🗺️ Surxondaryo', 'en' => '🗺️ Surkhandarya'],
        'karakalpakstan' => ['ru' => '🗺️ Каракалпакстан', 'uz' => '🗺️ Qoraqalpogʻiston', 'en' => '🗺️ Karakalpakstan'],
    ];

    public function showRegionSelection(Nutgram $bot, string $mode, string $field, string $lang, ?int $messageId = null): void
    {
        $text = $this->getRegionSelectionText($lang);
        $keyboard = $this->buildRegionsKeyboard($mode, $field, $lang);

        if ($messageId) {
            $bot->editMessageText(
                text: $text,
                chat_id: $bot->chatId(),
                message_id: $messageId,
                reply_markup: $keyboard,
                parse_mode: 'Markdown'
            );
        } else {
            $bot->sendMessage(
                text: $text,
                reply_markup: $keyboard,
                parse_mode: 'Markdown'
            );
        }
    }

    public function buildRegionsKeyboard(string $mode, string $field, string $lang): InlineKeyboardMarkup
    {
        $keyboard = InlineKeyboardMarkup::make();
        $regions = array_chunk($this->regions, 2, true);

        foreach ($regions as $row) {
            $buttons = [];

            foreach ($row as $regionKey => $regionNames) {
                $buttons[] = InlineKeyboardButton::make(
                    $regionNames[$lang],
                    callback_data: "select_region:{$mode}:{$field}:{$regionKey}"
                );
            }
            $keyboard->addRow(...$buttons);
        }

        $keyboard->addRow(
            InlineKeyboardButton::make(
                $this->getBackText($lang),
                callback_data: "show_creation_menu:{$mode}"
            )
        );

        return $keyboard;
    }

    public function getRegionName(string $region, string $lang): string
    {
        return $this->regions[$region][$lang] ?? $region;
    }

    public function getRegionSelectionText(string $lang): string
    {
        $texts = [
            'ru' => "🗺️ *Выберите регион*\n\nПожалуйста, выберите ваш регион из списка ниже:",
            'uz' => "🗺️ *Viloyatingizni tanlang*\n\nIltimos, quyidagi roʻyxatdan viloyatingizni tanlang:",
            'en' => "🗺️ *Select Region*\n\nPlease select your region from the list below:"
        ];
        return $texts[$lang] ?? $texts['ru'];
    }


    public function getBackText(string $lang): string
    {
        return match($lang) {
            'ru' => "⬅️ Назад",
            'uz' => "⬅️ Orqaga",
            'en' => "⬅️ Back",
            default => "⬅️ Back"
        };
    }

    public function formatFullAddress(string $region, ?string $customText = null): string
    {
        $address = $this->getRegionName($region, 'ru');
        
        if ($customText) {
            $address .= ', ' . $customText;
        }
        
        return $address;
    }
}

















