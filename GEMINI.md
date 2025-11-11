# GEMINI Project Analysis: FindWork Telegram Bot

## Project Overview

This project is a Telegram bot designed to help users find and post job opportunities. Users can create and manage their resumes, as well as create and manage job vacancies. The bot supports multiple languages (Uzbek, Russian, English).

The application is built on the **Laravel 12** framework using **PHP 8.2**. It utilizes the **Nutgram** library for Telegram Bot API interactions and **Filament** for a potential admin panel. The presence of **RoadRunner** suggests that the application is designed for high performance.

### Key Components:

*   **Telegram Bot Logic:** The core bot interaction flow is managed in `app/Http/Controllers/TelegramController.php`.
*   **Bot Routes:** Entry points for bot commands (like `/start`) and callback queries are defined in `routes/telegram.php`.
*   **Services:** Business logic for creating resumes and vacancies is encapsulated in services like `app/Services/CreateResumeService.php` and `app/Services/CreateVacancyService.php`.
*   **Database Models:** The application uses Eloquent ORM with models such as `Resume`, `Vacancy`, and `TelegramUser`.
*   **Localization:** The bot uses Laravel's localization features, with language files in the `lang/` directory.

## Building and Running

### 1. Initial Setup

To set up the project for the first time, run the following command. This will install PHP and JS dependencies, set up the `.env` file, generate an application key, and run database migrations.

```bash
composer run setup
```

### 2. Running the Development Environment

For local development, you can use the provided `dev` script, which concurrently runs the PHP server, queue worker, log viewer, and Vite asset bundler.

```bash
composer run dev
```

### 3. Running the Telegram Bot

The bot can be run in two modes:

**A) Polling (for development):**

Use the Nutgram artisan command to start polling for updates.

```bash
php artisan nutgram:run
```

**B) Webhook (for production):**

To use webhooks, you need to set a publicly accessible URL.

1.  Make sure your application is served and accessible via a public URL (e.g., using Expose or ngrok).
2.  Set the webhook URL using the following artisan command, replacing `YOUR_PUBLIC_URL` with your actual URL.

    ```bash
    php artisan nutgram:hook:set YOUR_PUBLIC_URL/telegram
    ```

To check the webhook status or remove it, you can use:

```bash
php artisan nutgram:hook:info
php artisan nutgram:hook:delete
```

### 4. Running Tests

The project uses PHPUnit for testing. To run the test suite, use the following command:

```bash
composer test
```

## Development Conventions

*   **Framework:** The project follows standard Laravel application architecture.
*   **Telegram Logic:** Bot commands and conversations should be defined in `routes/telegram.php` and handled by controllers in `app/Http/Controllers`.
*   **Business Logic:** Complex business logic is abstracted into Service classes (e.g., `CreateVacancyService`).
*   **Database:** Database schema changes are managed through migrations in the `database/migrations` directory.
*   **Localization:** All user-facing strings should be managed through Laravel's localization files in the `lang/` directory. Use the `__('messages.key')` helper function to retrieve them.
*   **Code Style:** While not explicitly defined, it's recommended to follow a standard PHP style guide like PSR-12 and use Laravel Pint for automatic formatting.
