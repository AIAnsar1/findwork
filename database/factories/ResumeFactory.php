<?php

namespace Database\Factories;

use App\Models\Resume;
use App\Models\TelegramUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Resume>
 */
class ResumeFactory extends Factory
{
    protected $model = Resume::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => $this->faker->name,
            'age' => $this->faker->numberBetween(20, 60),
            'address' => $this->faker->address,
            'position' => $this->faker->jobTitle,
            'salary' => $this->faker->numberBetween(500, 5000),
            'employment' => $this->faker->randomElement(['full', 'part', 'contract', 'temporary', 'intern']),
            'format' => $this->faker->randomElement(['office', 'remote', 'hybrid']),
            'experience_years' => $this->faker->numberBetween(1, 10),
            'skills' => $this->faker->sentence(5),
            'work_experience' => json_encode([
                ['company' => $this->faker->company, 'position' => $this->faker->jobTitle, 'years' => $this->faker->numberBetween(1, 5)],
            ]),
            'auto_posting' => true,
            'last_posted_at' => Carbon::now()->subHours(13),
            'phone' => $this->faker->phoneNumber,
            'telegram' => $this->faker->userName,
            'status' => $this->faker->randomElement(['active', 'hidden', 'moderation', 'rejected']),
            'about' => $this->faker->paragraph,
            'telegram_user_id' => TelegramUser::factory(),
        ];
    }
}
