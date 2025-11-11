<?php

namespace App\Http\Controllers;

use App\Helpers\{TelegramUserLangTrait, FormatForChannelTrait};
use App\Models\{TelegramUser, Resume, Vacancy};
use Illuminate\Http\Request;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Telegram\Types\Keyboard\{InlineKeyboardButton, InlineKeyboardMarkup};
use App\Services\{CreateResumeService, CreateVacancyService, ResumeListsService, VacancyListService};


class TelegramController extends Controller
{
    use TelegramUserLangTrait, FormatForChannelTrait;

    protected CreateResumeService $createResumeService;
    protected CreateVacancyService $createVacancyService;
    protected ResumeListsService $resumeListsService;
    protected VacancyListService $vacancyListService;

    public function __construct(CreateResumeService $createResumeService, CreateVacancyService $createVacancyService, ResumeListsService $resumeListsService, VacancyListService $vacancyListService)
    {
        $this->createResumeService = $createResumeService;
        $this->createVacancyService = $createVacancyService;
        $this->resumeListsService = $resumeListsService;
        $this->vacancyListService = $vacancyListService;
    }


    public function start(Nutgram $bot)
    {
        $user = TelegramUser::dontCache()->firstOrCreate(
            ['user_id' => $bot->userId()],
            [
                'username'   => $bot->user()->username,
                'first_name' => $bot->user()->first_name,
                'last_name'  => $bot->user()->last_name,
                'is_bot'     => $bot->user()->is_bot ?? false,
                'is_premium' => $bot->user()->is_premium ?? false,
            ]
        );
        // $user = $user->fresh();

        // Проверяем, был ли язык выбран
        if ($user->language_selected) {
            // Язык выбран → показываем главное меню
            $this->showMainMenu($bot, $user);
        } else {
            // Язык не выбран → показываем выбор языка
            $messageId = $bot->getUserData('main_message_id');

            if ($messageId) {
                return $this->showLangMenu($bot, $user);
            } else {
                $message = $this->showLangStart($bot, $user);
                $bot->setUserData('main_message_id', $message->message_id);
            }
        }
    }

    public function showLangStart(Nutgram $bot, TelegramUser $user)
    {
        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('🇺🇿 O‘zbek', callback_data: 'lang:uz'),
                InlineKeyboardButton::make('🇷🇺 Русский', callback_data: 'lang:ru'),
                InlineKeyboardButton::make('🇬🇧 English', callback_data: 'lang:en'),
            );

        $u = $user->username ?: ($user->first_name ?: 'друг');

        $text = match ($this->tgLang($bot)) {
            'uz' => "👋 Salom, {$u}! Tilni tanlang:",
            'en' => "👋 Hi, {$u}! Choose a language:",
            default => "👋 Привет, {$u}! Выбери язык:"
        };

        return $bot->sendMessage(
            text: $text,
            reply_to_message_id: $bot->message()->message_id,
            reply_markup: $keyboard,
            parse_mode: ParseMode::HTML
        );
    }

    public function showLangMenu(Nutgram $bot, TelegramUser $user)
    {
        $keyboard = InlineKeyboardMarkup::make()->addRow(
            InlineKeyboardButton::make(text: '🇺🇿 O‘zbek', callback_data: "lang:uz"),
            InlineKeyboardButton::make(text: '🇷🇺 Русский', callback_data: "lang:ru"),
            InlineKeyboardButton::make(text: '🇬🇧 English', callback_data: "lang:en"),
        );

        $u = $user->username ?? '';
        $text = "👋 Привет, {$u}! Выбери язык\n".
                "Salom, {$u}! Tilni tanlang\n".
                "Hi, {$u}! Choose language";

        // Используем ID сообщения из callback_query, если main_message_id пустой
        $messageId = $bot->getUserData('main_message_id');
        
        if (!$messageId) {
            $messageId = $bot->callbackQuery()->message->message_id;
        }

        try {
            return $bot->editMessageText(
                chat_id: $bot->chatId(),
                message_id: $messageId,
                text: $text,
                reply_markup: $keyboard
            );
        } catch (\SergiX44\Nutgram\Telegram\Exceptions\TelegramException $e) {
            if (str_contains($e->getMessage(), 'message is not modified')) {
                // Игнорируем ошибку, если сообщение не изменилось
                $bot->answerCallbackQuery();
                return;
            }
            throw $e;
        }
    }

    public function handleMessage(Nutgram $bot)
    {
        $userId = $bot->userId();
        $user = TelegramUser::dontCache()->where('user_id', $userId)->first();

        if (!$user) {
            $bot->sendMessage('Пользователь не найден. Начните с /start');
            return;
        }

        $callbackData = $bot->callbackQuery()?->data;

        // ✅ Обработка изменения языка
        if ($callbackData === "lang:change") {
            return $this->showLangMenu($bot, $user);
        }

        if (str_starts_with($callbackData, "lang:")) {
            $lang = str_replace("lang:", "", $callbackData);

            if (in_array($lang, ['ru', 'uz', 'en'])) {
                $user->update(['language' => $lang, 'language_selected' => true]);
                $user = TelegramUser::dontCache()->where('user_id', $bot->userId())->first();

                return $this->showMainMenu($bot, $user);
            }
        }

        // ✅ Обработка действий создания/редактирования
        $actions = ['resume:create', 'vacancy:create', 'resume:edit', 'vacancy:edit'];
        
        if (in_array($callbackData, $actions)) {
            // Обрабатываем действие без показа главного меню
            $messageId = $bot->getUserData('main_message_id');
            
            if ($callbackData === "resume:create") {
                return $this->createResumeService->handle($bot, $user, $messageId);
            } elseif ($callbackData === "vacancy:create") {
                return $this->createVacancyService->handle($bot, $user, $messageId);
            } elseif ($callbackData === "resume:edit") {
                return $this->resumeListsService->handle($bot, $user, $messageId);
            } elseif ($callbackData === "vacancy:edit") {
                return $this->vacancyListService->handle($bot, $user, $messageId);
            }
        }

        // Обработка остальных callback'ов
        $lang = $this->tgLang($bot);
        return $this->showMainMenu($bot, $user);
    }

    
    
    public function showMainMenu(Nutgram $bot, TelegramUser $user, ?int $replyToMessageId = null): void
    {
        $messageId = $bot->getUserData('main_message_id');
        $callbackData = $bot->callbackQuery()?->data;

        // Показываем главное меню только если это не действие
        $lang = $this->tgLang($bot);

        $keyboard = InlineKeyboardMarkup::make();

        $createResumeBtn = InlineKeyboardButton::make(__('messages.resume.create', [], $lang), callback_data: 'resume:create');
        $editResumeBtn   = InlineKeyboardButton::make(__('messages.resume.edit', [], $lang),   callback_data: 'resume:edit');
        $createVacancyBtn = InlineKeyboardButton::make(__('messages.vacancy.create', [], $lang), callback_data: 'vacancy:create');
        $editVacancyBtn   = InlineKeyboardButton::make(__('messages.vacancy.edit', [], $lang),   callback_data: 'vacancy:edit');
        $changeLangBtn    = InlineKeyboardButton::make(__('messages.language.change', [], $lang), callback_data: 'lang:change');

        $keyboard->addRow($createVacancyBtn, $createResumeBtn);

        if ($user->resumes()->exists()) {
            $keyboard->addRow($editResumeBtn);
        }
        if ($user->vacancies()->exists()) {
            $keyboard->addRow($editVacancyBtn);
        }

        $keyboard->addRow($changeLangBtn);

        $text = __('messages.start_message', [], $lang);

        if ($replyToMessageId) {
            $result = $bot->sendMessage(
                $text,
                reply_to_message_id: $replyToMessageId,
                reply_markup: $keyboard,
                parse_mode: ParseMode::HTML
            );
            $bot->setUserData('main_message_id', $result->message_id);
            return;
        }

        try {
            $bot->editMessageText(
                chat_id: $bot->chatId(),
                message_id: $messageId,
                text: $text,
                parse_mode: ParseMode::HTML,
                reply_markup: $keyboard
            );
            
            // ✅ Устанавливаем main_message_id, если он еще не установлен
            if (!$bot->getUserData('main_message_id') && $messageId) {
                $bot->setUserData('main_message_id', $messageId);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to edit message to main menu', ['error' => $e->getMessage()]);
            // fallback
            $result = $bot->sendMessage(
                $text, 
                reply_markup: $keyboard, 
                parse_mode: ParseMode::HTML
            );
            $bot->setUserData('main_message_id', $result->message_id);
        }
    }

    public function handleCallbacks(Nutgram $bot)
    {
        $callbackData = $bot->callbackQuery()?->data;
        $user = $this->tgUser($bot);
        $messageId = $bot->getUserData('main_message_id');

        if (!$user) {
            $bot->answerCallbackQuery(text: 'Пользователь не найден');
            return;
        }

        // Обработка создания резюме
        if (str_starts_with($callbackData, 'show_creation_menu:resume') ||
            str_starts_with($callbackData, 'edit_field:resume') ||
            str_starts_with($callbackData, 'set_enum:resume') ||
            str_starts_with($callbackData, 'select_region:resume') ||
            str_starts_with($callbackData, 'manual_input:resume') ||
            $callbackData === 'save:resume') {
            
            $this->createResumeService->handle($bot, $user, $callbackData);
            return;
        }

        // Обработка создания вакансии
        if (str_starts_with($callbackData, 'show_creation_menu:vacancy') ||
            str_starts_with($callbackData, 'edit_field:vacancy') ||
            str_starts_with($callbackData, 'set_enum:vacancy') ||
            str_starts_with($callbackData, 'select_region:vacancy') ||
            str_starts_with($callbackData, 'manual_input:vacancy') ||
            $callbackData === 'save:vacancy') {
            
            $this->createVacancyService->handle($bot, $user, $callbackData);
            return;
        }

        // Обработка просмотра резюме
        if (str_starts_with($callbackData, 'show_resume') || 
            str_starts_with($callbackData, 'edit_resume') ||
            str_starts_with($callbackData, 'delete_resume') ||
            str_starts_with($callbackData, 'confirm_delete_resume') ||
            str_starts_with($callbackData, 'toggle_resume_status') ||
            $callbackData === 'view_resumes') {
            
            $this->resumeListsService->handle($bot, $user, $messageId);
            return;
        }

        // Обработка просмотра вакансий
        if (str_starts_with($callbackData, 'show_vacancy') || 
            str_starts_with($callbackData, 'edit_vacancy') ||
            str_starts_with($callbackData, 'delete_vacancy') ||
            str_starts_with($callbackData, 'confirm_delete_vacancy') ||
            str_starts_with($callbackData, 'toggle_vacancy_status') ||
            $callbackData === 'view_vacancies') {
            
            $this->vacancyListService->handle($bot, $user, $messageId);
            return;
        }

        // Модерация
        if (str_starts_with($callbackData, 'mod_approve:') || str_starts_with($callbackData, 'mod_reject:')) {
            $this->handleModerationCallbacks($bot, $callbackData);
            return;
        }

        $bot->answerCallbackQuery(text: 'Команда не распознана');
    }

    public function handleTextMessage(Nutgram $bot)
    {
        $user = $this->tgUser($bot);
        
        if (!$user) {
            return;
        }

        // Обработка модерации (отклонение с причиной)
        $moderatorId = $bot->userId();
        $adminGroupId = config('nutgram.admin_controlls_group_id');
        
        if ($bot->chatId() == $adminGroupId && $rejectionContext = $bot->getUserData('rejecting_item', $moderatorId)) {
            $this->handleModerationRejection($bot, $rejectionContext);
            return;
        }

        // Передаем сообщение в сервисы для обработки ввода текста
        $mode = $bot->getUserData('mode');
        
        if ($mode === 'resume') {
            $this->createResumeService->handleMessage($bot);
        } elseif ($mode === 'vacancy') {
            $this->createVacancyService->handleMessage($bot);
        } else {
            // Если это обычное сообщение, показываем главное меню
            $this->showMainMenu($bot, $user, $bot->message()->message_id);
        }
    }

    public function chooseLanguage(Nutgram $bot)
    {
        $lang = str_replace('lang:', '', $bot->callbackQuery()?->data);
        $user = $this->tgUser($bot);
        
        if ($user) {
            $user->update(['language' => $lang]);
            $this->handleMessage($bot);
        }
    }

    protected function handleModerationCallbacks(Nutgram $bot, string $callbackData): void
    {
        // Разбираем callbackData: mod_approve:resume:123 или mod_reject:vacancy:456
        $parts = explode(':', $callbackData);
        
        if (count($parts) !== 3) {
            $bot->answerCallbackQuery(text: 'Ошибка: Неверный формат callback данных');
            return;
        }

        $action = str_replace('mod_', '', $parts[0]); // 'approve' или 'reject'
        $mode = $parts[1]; // 'resume' или 'vacancy'
        $id = (int) $parts[2]; // ID записи

        $this->handleModeration($bot, $mode, $id, $action);
    }

    protected function handleModerationRejection(Nutgram $bot, array $rejectionContext): void
    {
        $reason = $bot->message()->text;
        $mode = $rejectionContext['mode'];
        $id = $rejectionContext['id'];
        $messageId = $rejectionContext['message_id'];
        $adminGroupId = $rejectionContext['admin_group_id'];

        $model = $mode === 'resume' ? Resume::find($id) : Vacancy::find($id);
        
        if ($model) {
            $model->update(['status' => 'rejected']);
            
            // Уведомляем пользователя
            $bot->sendMessage(
                "Ваше объявление было отклонено по причине: {$reason}", 
                chat_id: $model->telegramUser->user_id
            );
            
            // Обновляем сообщение модерации
            $bot->editMessageText(
                "ОТКЛОНЕНО по причине: {$reason}", 
                chat_id: $adminGroupId,
                message_id: $messageId,
            );
            
            // Очищаем данные модерации
            $moderatorId = $bot->userId();
            $bot->setUserData('rejecting_item', null, $moderatorId);
        }
    }
}
