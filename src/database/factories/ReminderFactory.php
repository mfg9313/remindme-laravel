<?php

namespace Database\Factories;

use App\Models\Reminder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReminderFactory extends Factory
{
    protected $model = Reminder::class;

    public function definition()
    {
        $remindAt = $this->faker->dateTimeBetween('+1 hours', '+1 week')->getTimestamp();
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'remind_at' => $remindAt,
            'event_at' => $remindAt,
            'sent' => false,
        ];
    }
}
