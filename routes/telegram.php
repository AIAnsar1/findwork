<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use SergiX44\Nutgram\Nutgram;
use Nutgram\Laravel\Facades\Telegram;
use App\Http\Controllers\{TelegramController, FrontWebHookController};

/*
|--------------------------------------------------------------------------
| Nutgram Handlers
|--------------------------------------------------------------------------
|
| Here is where you can register telegram handlers for Nutgram. These
| handlers are loaded by the NutgramServiceProvider. Enjoy!
|
*/


// Стартовая команда
Telegram::onCommand('start', [TelegramController::class, 'start']);

// Выбор языка
// Telegram::onCallbackQueryData('lang:{lang}', [TelegramController::class, 'chooseLanguage']);
Telegram::onCallbackQueryData('lang:change', [TelegramController::class, 'handleMessage']);
Telegram::onCallbackQueryData('lang:{lang}', [TelegramController::class, 'handleMessage']);

// Главное меню действия
Telegram::onCallbackQueryData('resume:create', [TelegramController::class, 'handleMessage']);
Telegram::onCallbackQueryData('vacancy:create', [TelegramController::class, 'handleMessage']);
Telegram::onCallbackQueryData('resume:edit', [TelegramController::class, 'handleMessage']);
Telegram::onCallbackQueryData('vacancy:edit', [TelegramController::class, 'handleMessage']);
Telegram::onCallbackQueryData('lang:change', [TelegramController::class, 'handleMessage']);

// Создание резюме
Telegram::onCallbackQueryData('show_creation_menu:resume', [TelegramController::class, 'handleCallbacks']);
Telegram::onCallbackQueryData('edit_field:resume:{field}', [TelegramController::class, 'handleCallbacks']);
Telegram::onCallbackQueryData('set_enum:resume:{field}:{value}', [TelegramController::class, 'handleCallbacks']);
Telegram::onCallbackQueryData('select_region:resume:{field}:{region}', [TelegramController::class, 'handleCallbacks']);
Telegram::onCallbackQueryData('manual_input:resume:{field}', [TelegramController::class, 'handleCallbacks']);
Telegram::onCallbackQueryData('save:resume', [TelegramController::class, 'handleCallbacks']);

// Создание вакансии  
Telegram::onCallbackQueryData('show_creation_menu:vacancy', [TelegramController::class, 'handleCallbacks']);
Telegram::onCallbackQueryData('edit_field:vacancy:{field}', [TelegramController::class, 'handleCallbacks']);
Telegram::onCallbackQueryData('set_enum:vacancy:{field}:{value}', [TelegramController::class, 'handleCallbacks']);
Telegram::onCallbackQueryData('select_region:vacancy:{field}:{region}', [TelegramController::class, 'handleCallbacks']);
Telegram::onCallbackQueryData('manual_input:vacancy:{field}', [TelegramController::class, 'handleCallbacks']);
Telegram::onCallbackQueryData('save:vacancy', [TelegramController::class, 'handleCallbacks']);

// Просмотр записей (резюме)
Telegram::onCallbackQueryData('view_resumes', [TelegramController::class, 'handleCallbacks']);
Telegram::onCallbackQueryData('show_resume:{id}', [TelegramController::class, 'handleCallbacks']);
Telegram::onCallbackQueryData('edit_resume:{id}', [TelegramController::class, 'handleCallbacks']);
Telegram::onCallbackQueryData('delete_resume:{id}', [TelegramController::class, 'handleCallbacks']);
Telegram::onCallbackQueryData('confirm_delete_resume:{id}', [TelegramController::class, 'handleCallbacks']);
Telegram::onCallbackQueryData('toggle_resume_status:{id}', [TelegramController::class, 'handleCallbacks']);

// Просмотр записей (вакансии)
Telegram::onCallbackQueryData('view_vacancies', [TelegramController::class, 'handleCallbacks']);
Telegram::onCallbackQueryData('show_vacancy:{id}', [TelegramController::class, 'handleCallbacks']);
Telegram::onCallbackQueryData('edit_vacancy:{id}', [TelegramController::class, 'handleCallbacks']);
Telegram::onCallbackQueryData('delete_vacancy:{id}', [TelegramController::class, 'handleCallbacks']);
Telegram::onCallbackQueryData('confirm_delete_vacancy:{id}', [TelegramController::class, 'handleCallbacks']);
Telegram::onCallbackQueryData('toggle_vacancy_status:{id}', [TelegramController::class, 'handleCallbacks']);

// Навигация
Telegram::onCallbackQueryData('back_to_start', [TelegramController::class, 'start']);

// Модерация
Telegram::onCallbackQueryData('mod_approve:{mode}:{id}', [TelegramController::class, 'handleCallbacks']);
Telegram::onCallbackQueryData('mod_reject:{mode}:{id}', [TelegramController::class, 'handleCallbacks']);

// Обработка текстовых сообщений
Telegram::onMessage([TelegramController::class, 'handleTextMessage']);

// Fallback для непредвиденных callback'ов
Telegram::onCallbackQuery([TelegramController::class, 'handleCallbacks']);
