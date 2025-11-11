<?php

namespace App\Helpers;

use SergiX44\Nutgram\Nutgram;
use App\Models\TelegramUser;

trait TelegramUserLangTrait
{
    public function tgUser(Nutgram $bot): ?TelegramUser
    {
        return TelegramUser::where('user_id', $bot->userId())->first();
    }

    public function tgLang(Nutgram $bot): string
    {
        return $this->tgUser($bot)?->lang() ?? 'ru';
    }
}