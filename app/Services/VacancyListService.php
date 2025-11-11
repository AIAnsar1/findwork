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

    public function handle(Nutgram $bot, TelegramUser $user, ?int $messageId = null)
    {

    }

    public function getEnumFields()
    {

    }


    public function getSteps(string $mode)
    {

    }


    public function getQuestions(string $mode)
    {
        
    }
}