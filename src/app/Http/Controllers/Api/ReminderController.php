<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReminderController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->query('limit', 10);

        $reminders = $request->user()->reminders()
            ->where('remind_at', '>=', now()->timestamp)
            ->orderBy('remind_at', 'asc')
            ->limit($limit)
            ->get(['id', 'title', 'description', 'remind_at', 'event_at']);

        return response()->json([
            'ok' => true,
            'data' => [
                'reminders' => $reminders->map(function ($reminder) {
                    return [
                        'id' => $reminder->id,
                        'title' => $reminder->title,
                        'description' => Str::limit($reminder->description, 250, '...'),
                        'remind_at' => $reminder->remind_at,
                        'event_at' => $reminder->event_at,
                    ];
                }),
                'limit' => $limit,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'required|nullable|string',
            'remind_at' => 'required|integer',
            'event_at' => 'required|integer',
        ]);

        $data['user_id'] = $request->user()->id;

        $reminder = Reminder::create($data);

        return response()->json([
            'ok' => true,
            'data' => [
                'id' => $reminder->id,
                'title' => $reminder->title,
                'description' => $reminder->description,
                'remind_at' => (string) $reminder->remind_at,
                'event_at' => (string) $reminder->event_at,
            ],
        ]);
    }

    public function show($id)
    {
        $reminder = Reminder::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if (!$reminder) {
            return response()->json([
                'ok' => false,
                'msg' => 'Reminder not found.',
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'data' => [
                'id' => $reminder->id,
                'title' => $reminder->title,
                'description' => $reminder->description,
                'remind_at' => (string) $reminder->remind_at,
                'event_at' => (string) $reminder->event_at,
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $reminder = Reminder::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $data = $request->validate([
            'title' => 'sometimes|required|string',
            'description' => 'sometimes|nullable|string',
            'remind_at' => 'sometimes|required|integer',
            'event_at' => 'sometimes|required|integer',
        ]);

        $reminder->update($data);

        return response()->json([
            'ok' => true,
            'data' => [
                'id' => $reminder->id,
                'title' => $reminder->title,
                'description' => $reminder->description,
                'remind_at' => (string) $reminder->remind_at,
                'event_at' => (string) $reminder->event_at,
            ],
        ]);
    }

    public function destroy($id)
    {
        $reminder = Reminder::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $reminder->delete();

        return response()->json([
            'ok' => true,
        ]);
    }
}
