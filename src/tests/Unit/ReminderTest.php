<?php

namespace Tests\Unit;

use App\Models\Reminder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReminderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a reminder belongs to a user.
     */
    public function test_reminder_belongs_to_user()
    {
        // Create a user
        $user = User::factory()->create();

        // Create a reminder
        $reminder = Reminder::factory()->create([
            'user_id' => $user->id,
        ]);

        // Assert relationship
        $this->assertInstanceOf(User::class, $reminder->user);
        $this->assertEquals($user->id, $reminder->user->id);
    }

    /**
     * Test that reminders are scoped correctly.
     */
    public function test_reminders_scope_for_upcoming_reminders()
    {
        // Create a user
        $user = User::factory()->create();

        // Create past and future reminders
        Reminder::factory()->count(3)->create([
            'user_id' => $user->id,
            'remind_at' => now()->addHours(2)->timestamp,
        ]);

        Reminder::factory()->count(2)->create([
            'user_id' => $user->id,
            'remind_at' => now()->subHours(2)->timestamp,
        ]);

        // Fetch upcoming reminders
        $upcomingReminders = $user->reminders()
            ->where('remind_at', '>=', now()->timestamp)
            ->orderBy('remind_at', 'asc')
            ->limit(10)
            ->get();

        $this->assertCount(3, $upcomingReminders);
        foreach ($upcomingReminders as $reminder) {
            $this->assertTrue($reminder->remind_at >= now()->timestamp);
        }
    }
}
