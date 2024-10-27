<?php

namespace App\Mail;

use App\Models\Reminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReminderDue extends Mailable
{
    use Queueable, SerializesModels;

    public Reminder $reminder;

    /**
     * Create a new message instance.
     *
     * @param Reminder $reminder
     * @return void
     */
    public function __construct(Reminder $reminder)
    {
        $this->reminder = $reminder;
    }

    public function build()
    {
        return $this->subject('Your Reminder is Due!')
            ->view('emails.reminder_due')
            ->with([
                'reminderTitle' => $this->reminder->title,
                'reminderDescription' => $this->reminder->description,
                'eventAt' => \Carbon\Carbon::createFromTimestamp($this->reminder->event_at)->toDayDateTimeString(),
                'remindAt' => \Carbon\Carbon::createFromTimestamp($this->reminder->remind_at)->toDayDateTimeString(),
                'userName' => $this->reminder->user->name,
            ]);
    }
}
