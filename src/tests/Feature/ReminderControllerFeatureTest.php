<?php

namespace Tests\Feature;

use App\Models\Reminder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReminderControllerFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user
        $this->user = User::factory()->create([
            'password' => Hash::make('123456'),
        ]);

        // Log in the user to get access token
        $loginResponse = $this->postJson('/api/session', [
            'email' => $this->user->email,
            'password' => '123456',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJson([
                'ok' => true,
                'data' => [
                    'user' => [
                        'id' => $this->user->id,
                        'email' => $this->user->email,
                        'name' => $this->user->name,
                    ],
                ],
            ]);

        $this->accessToken = $loginResponse->json('data.access_token');
    }

    /**
     * Helper method to set Authorization header.
     *
     * @return array
     */
    protected function headers()
    {
        return [
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Accept' => 'application/json',
        ];
    }

    /**
     * Test that a user can retrieve their reminders.
     *
     * @return void
     */
    public function test_authenticated_user_can_retrieve_their_reminders()
    {
        // Create reminders for the user
        Reminder::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'title' => 'Test Reminder',
            'description' => 'This is a test reminder.',
            'remind_at' => now()->addDays(1)->timestamp,
            'event_at' => now()->addDays(1)->timestamp,
        ]);

        // Create reminders for a different user
        $user2 = User::factory()->create();
        Reminder::factory()->count(2)->create([
            'user_id' => $user2->id,
            'title' => 'Other User Reminder',
            'description' => 'This reminder belongs to another user.',
            'remind_at' => now()->addDays(2)->timestamp,
            'event_at' => now()->addDays(2)->timestamp,
        ]);

        // Send GET request to /api/reminders
        $response = $this->withHeaders($this->headers())->getJson('/api/reminders?limit=10');

        // Assert that the response is successful
        $response->assertStatus(200)
            ->assertJson([
                'ok' => true,
                'data' => [
                    'reminders' => [
                        ['title' => 'Test Reminder'],
                        ['title' => 'Test Reminder'],
                        ['title' => 'Test Reminder'],
                    ],
                    'limit' => 10,
                ],
            ])
            ->assertJsonStructure([
                'ok',
                'data' => [
                    'reminders' => [
                        '*' => [
                            'id',
                            'title',
                            'description',
                            'remind_at',
                            'event_at',
                        ],
                    ],
                    'limit',
                ],
            ]);

        // Ensure only the user reminders are returned
        $responseData = $response->json('data.reminders');
        $this->assertCount(3, $responseData);

        foreach ($responseData as $reminderJson) {
            $reminder = Reminder::find($reminderJson['id']);
            $this->assertEquals($this->user->id, $reminder->user_id);
        }
    }

    /**
     * Test that an authenticated user can create a new reminder.
     *
     * @return void
     */
    public function test_authenticated_user_can_create_a_new_reminder()
    {
        // Prepare reminder data
        $reminderData = [
            'title' => 'New Reminder',
            'description' => 'This is a new reminder.',
            'remind_at' => now()->addDays(3)->timestamp,
            'event_at' => now()->addDays(3)->timestamp,
        ];

        // Send POST request to /api/reminders
        $response = $this->withHeaders($this->headers())
            ->postJson('/api/reminders', $reminderData);

        // Assert that the response is successful
        $response->assertStatus(200)
            ->assertJson([
                'ok' => true,
                'data' => [
                    'title' => 'New Reminder',
                    'description' => 'This is a new reminder.',
                    'remind_at' => (string)$reminderData['remind_at'],
                    'event_at' => (string)$reminderData['event_at'],
                ],
            ])
            ->assertJsonStructure([
                'ok',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'remind_at',
                    'event_at',
                ],
            ]);

        // Assert that the reminder exists in the database
        $this->assertDatabaseHas('reminders', [
            'title' => 'New Reminder',
            'description' => 'This is a new reminder.',
            'remind_at' => $reminderData['remind_at'],
            'event_at' => $reminderData['event_at'],
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * Test that creating a reminder fails with invalid data.
     *
     * @return void
     */
    public function test_creating_a_reminder_fails_with_invalid_data()
    {
        // Prepare invalid reminder, missing title and remind_at
        $invalidData = [
            'description' => 'Missing title and remind_at.',
            'event_at' => now()->addDays(3)->timestamp,
        ];

        // Send POST request to /api/reminders
        $response = $this->withHeaders($this->headers())->postJson('/api/reminders', $invalidData);

        // Assert that the response has validation errors
        $response->assertStatus(422)->assertJsonValidationErrors(['title', 'remind_at']);
    }

    /**
     * Test that a user can retrieve a specific reminder.
     *
     * @return void
     */
    public function test_authenticated_user_can_retrieve_specific_reminder()
    {
        // Create a reminder for the user
        $reminder = Reminder::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Specific Reminder',
            'description' => 'Details of specific reminder.',
            'remind_at' => now()->addDays(4)->timestamp,
            'event_at' => now()->addDays(4)->timestamp,
        ]);

        // Send GET request to /api/reminders/{id}
        $response = $this->withHeaders($this->headers())
            ->getJson("/api/reminders/{$reminder->id}");

        // Assert that the response is successful
        $response->assertStatus(200)
            ->assertJson([
                'ok' => true,
                'data' => [
                    'id' => $reminder->id,
                    'title' => 'Specific Reminder',
                    'description' => 'Details of specific reminder.',
                    'remind_at' => (string)$reminder->remind_at,
                    'event_at' => (string)$reminder->event_at,
                ],
            ])
            ->assertJsonStructure([
                'ok',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'remind_at',
                    'event_at',
                ],
            ]);
    }

    /**
     * Test that retrieving a reminder not belonging to the user fails.
     *
     * @return void
     */
    public function test_authenticated_user_cannot_retrieve_other_users_reminder()
    {
        // Create another user and their reminder
        $user2 = User::factory()->create();
        $otherReminder = Reminder::factory()->create([
            'user_id' => $user2->id,
            'title' => 'Other User Reminder',
            'description' => 'This reminder belongs to another user.',
            'remind_at' => now()->addDays(5)->timestamp,
            'event_at' => now()->addDays(5)->timestamp,
        ]);

        // Send GET request to /api/reminders/{id}
        $response = $this->withHeaders($this->headers())->getJson("/api/reminders/{$otherReminder->id}");

        // Assert that the response has a 404 status (not found)
        $response->assertStatus(404);
    }

    /**
     * Test that a user can update their reminder.
     *
     * @return void
     */
    public function test_authenticated_user_can_update_their_reminder()
    {
        // Create a reminder for the user
        $reminder = Reminder::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Old Title',
            'description' => 'Old Description.',
            'remind_at' => now()->addDays(6)->timestamp,
            'event_at' => now()->addDays(6)->timestamp,
        ]);

        // Prepare update data
        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated Description.',
            'remind_at' => now()->addDays(7)->timestamp,
            'event_at' => now()->addDays(7)->timestamp,
        ];

        // Send PUT request to /api/reminders/{id}
        $response = $this->withHeaders($this->headers())->putJson("/api/reminders/{$reminder->id}", $updateData);

        // Assert that the response is successful
        $response->assertStatus(200)
            ->assertJson([
                'ok' => true,
                'data' => [
                    'id' => $reminder->id,
                    'title' => 'Updated Title',
                    'description' => 'Updated Description.',
                    'remind_at' => (string)$updateData['remind_at'],
                    'event_at' => (string)$updateData['event_at'],
                ],
            ])
            ->assertJsonStructure([
                'ok',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'remind_at',
                    'event_at',
                ],
            ]);

        // Assert that the reminder is updated in the database
        $this->assertDatabaseHas('reminders', [
            'id' => $reminder->id,
            'title' => 'Updated Title',
            'description' => 'Updated Description.',
            'remind_at' => $updateData['remind_at'],
            'event_at' => $updateData['event_at'],
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * Test that updating a reminder not belonging to the user fails.
     *
     * @return void
     */
    public function test_authenticated_user_cannot_update_other_users_reminder()
    {
        // Create another user and their reminder
        $user2 = User::factory()->create();
        $otherReminder = Reminder::factory()->create([
            'user_id' => $user2->id,
            'title' => 'Other User Reminder',
            'description' => 'This reminder belongs to another user.',
            'remind_at' => now()->addDays(5)->timestamp,
            'event_at' => now()->addDays(5)->timestamp,
        ]);

        // Prepare update data
        $updateData = [
            'title' => 'Not My Title',
            'description' => 'Not My Description.',
        ];

        // Send PUT request to /api/reminders/{id}
        $response = $this->withHeaders($this->headers())->putJson("/api/reminders/{$otherReminder->id}", $updateData);

        // Assert that the response has a 404 status (not found)
        $response->assertStatus(404);

        // Ensure the reminder was not updated
        $this->assertDatabaseHas('reminders', [
            'id' => $otherReminder->id,
            'title' => 'Other User Reminder',
            'description' => 'This reminder belongs to another user.',
        ]);
    }

    /**
     * Test that a user can delete their reminder.
     *
     * @return void
     */
    public function test_authenticated_user_can_delete_their_reminder()
    {
        // Create a reminder for the user
        $reminder = Reminder::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Reminder to Delete',
            'description' => 'This reminder will be deleted.',
            'remind_at' => now()->addDays(8)->timestamp,
            'event_at' => now()->addDays(8)->timestamp,
        ]);

        // Send DELETE request to /api/reminders/{id}
        $response = $this->withHeaders($this->headers())->deleteJson("/api/reminders/{$reminder->id}");

        // Assert that the response is successful
        $response->assertStatus(200)
            ->assertJson([
                'ok' => true,
            ]);

        // Assert that the reminder is deleted from the database
        $this->assertSoftDeleted('reminders', [
            'id' => $reminder->id,
        ]);
    }

    /**
     * Test that deleting a reminder of another user fails.
     *
     * @return void
     */
    public function test_authenticated_user_cannot_delete_other_users_reminder()
    {
        // Create another user and their reminder
        $user2 = User::factory()->create();
        $otherReminder = Reminder::factory()->create([
            'user_id' => $user2->id,
            'title' => 'Other User Reminder',
            'description' => 'This reminder belongs to another user.',
            'remind_at' => now()->addDays(5)->timestamp,
            'event_at' => now()->addDays(5)->timestamp,
        ]);

        // Send DELETE request to /api/reminders/{id}
        $response = $this->withHeaders($this->headers())->deleteJson("/api/reminders/{$otherReminder->id}");

        // Assert that the response has a 404 status (not found)
        $response->assertStatus(404);

        // Ensure the reminder still exists in the database
        $this->assertDatabaseHas('reminders', [
            'id' => $otherReminder->id,
            'title' => 'Other User Reminder',
        ]);
    }

    /**
     * Test that unauthorized access to reminders is denied.
     *
     * @return void
     */
    public function test_unauthorized_access_to_reminders_is_denied()
    {
        // Create another user and their reminders
        $user2 = User::factory()->create();
        Reminder::factory()->count(3)->create([
            'user_id' => $user2->id,
            'title' => 'Other User Reminder',
            'description' => 'Reminders belonging to another user.',
            'remind_at' => now()->addDays(2)->timestamp,
            'event_at' => now()->addDays(2)->timestamp,
        ]);

        // Attempt to access reminders without authentication
        $response = $this->getJson('/api/reminders');

        // Assert that the response has a 401 status
        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }
}
