<?php

namespace App\Console\Commands;

use App\Mail\ReminderDue;
use App\Models\Reminder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendReminderNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send-reminder-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email notifications for due reminders';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = Carbon::now();

        // Fetch reminders that are due, batching in the case of many reminders to max memory usage
        Reminder::where('remind_at', '<=', $now->timestamp)
            ->where('sent', false)
            ->with('user')
            ->chunk(100, function ($reminders) {
                foreach ($reminders as $reminder) {
                    // Send email and mark as notified
                    Mail::to($reminder->user->email)->send(new ReminderDue($reminder));

                    // Mark the reminder as notified
                    $reminder->sent = true;
                    $reminder->save();

                    $this->info("Notification sent for Reminder ID: {$reminder->id}");
                }
            });

        return 0;
    }
}
