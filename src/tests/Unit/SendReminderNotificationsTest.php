<?php

namespace Tests\Unit;

use App\Console\Commands\SendReminderNotifications;
use App\Mail\ReminderDue;
use App\Models\Reminder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class SendReminderNotificationsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that due reminders are sent and marked as sent.
     */
    public function test_due_reminders_are_sent_and_marked_as_sent()
    {
        // Fake mail to intercept outgoing emails
        Mail::fake();

        // Create a user with a name to ensure no null issues
        $user = User::factory()->create([
            'email' => 'matthew@example.com',
            'password' => Hash::make('123456'),
        ]);

        // Create reminders: one due and one not
        $dueReminder = Reminder::factory()->create([
            'user_id' => $user->id,
            'sent' => false,
        ]);

        $notDueReminder = Reminder::factory()->create([
            'user_id' => $user->id,
            'sent' => false,
        ]);

        // For the due reminder, set remind_at and event_at in the past
        $past = Carbon::now()->subMinutes(10)->timestamp;
        $dueReminder->update([
            'remind_at' => $past,
            'event_at' => $past,
        ]);

        // For the not due reminder, set remind_at and event_at in the future
        $future = Carbon::now()->addMinutes(10)->timestamp;
        $notDueReminder->update([
            'remind_at' => $future,
            'event_at' => $future,
        ]);

        // Assert that the due reminder's remind_at is in the past
        $this->assertLessThanOrEqual(
            Carbon::now()->timestamp,
            $dueReminder->fresh()->remind_at,
        );

        // Assert that the not due reminder's remind_at is in the future
        $this->assertGreaterThan(
            Carbon::now()->timestamp,
            $notDueReminder->fresh()->remind_at,
        );

        // Confirm database entries
        $this->assertDatabaseHas('reminders', [
            'id' => $dueReminder->id,
            'remind_at' => $past,
            'sent' => false,
        ]);

        $this->assertDatabaseHas('reminders', [
            'id' => $notDueReminder->id,
            'remind_at' => $future,
            'sent' => false,
        ]);

        // Replicate the command's query to ensure it fetches the due reminder
        $now = Carbon::now()->timestamp;
        $dueReminders = Reminder::where('remind_at', '<=', $now)
            ->where('sent', false)
            ->with('user')
            ->get();
        $this->assertCount(1, $dueReminders);
        $this->assertEquals($dueReminder->id,$dueReminders->first()->id);

        // Run the console command via Artisan
        $this->artisan('reminders:send-reminder-notifications')->assertExitCode(0);

        // Assert that the mailable was sent
        Mail::assertSent(ReminderDue::class, function ($mail) use ($user, $dueReminder) {
            return $mail->hasTo($user->email) && $mail->reminder->id === $dueReminder->id;
        });

        // Assert that the due reminder is marked as sent
        $this->assertEquals(1, $dueReminder->fresh()->sent);

        // Assert that the mailable was not sent
        Mail::assertNotSent(ReminderDue::class, function ($mail) use ($notDueReminder) {
            return $mail->reminder->id === $notDueReminder->id;
        });

        // Assert that the not due reminder is not sent
        $this->assertEquals(0, $notDueReminder->fresh()->sent);
    }
}
