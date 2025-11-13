<?php

namespace Database\Factories;

use App\Models\Vacancy;
use App\Models\TelegramUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vacancy>
 */
class VacancyFactory extends Factory
{
    protected $model = Vacancy::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company' => $this->faker->company,
            'position' => $this->faker->jobTitle,
            'salary' => $this->faker->numberBetween(1000, 10000),
            'experience' => $this->faker->randomElement(['1-3 years', '3-5 years', '5+ years']),
            'employment' => $this->faker->randomElement(['full', 'part', 'contract', 'temporary', 'intern']),
            'schedule' => $this->faker->randomElement(['full-time', 'part-time', 'flexible']),
            'work_hours' => $this->faker->numberBetween(4, 8),
            'format' => $this->faker->randomElement(['office', 'remote', 'hybrid']),
            'responsibilities' => $this->faker->paragraph,
            'requirements' => $this->faker->paragraph,
            'conditions' => $this->faker->paragraph,
            'benefits' => $this->faker->paragraph,
            'auto_posting' => true,
            'last_posted_at' => Carbon::now()->subHours(13),
            'contact_name' => $this->faker->name,
            'contact_phone' => $this->faker->phoneNumber,
            'contact_email' => $this->faker->email,
            'contact_telegram' => $this->faker->userName,
            'status' => $this->faker->randomElement(['open', 'closed', 'moderation', 'rejected']),
            'address' => $this->faker->address,
            'telegram_user_id' => TelegramUser::factory(),
        ];
    }
}
