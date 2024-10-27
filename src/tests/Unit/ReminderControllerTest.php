<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\ReminderController;
use App\Models\Reminder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ReminderControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test returns reminders for authenticated user.
     */
    public function test_index_returns_reminders_for_authenticated_user()
    {
        // Create user
        $user = User::factory()->create();

        // Create reminders
        $reminders = Reminder::factory()->count(5)->create([
            'user_id' => $user->id,
            'remind_at' => now()->addHours(1)->timestamp,
        ]);

        // Mock the request with authenticated user
        $request = Request::create('/api/reminders', 'GET', [
            'limit' => 10,
        ]);

        // Set the authenticated user
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $controller = new ReminderController();
        $response = $controller->index($request);

        // Assert response
        $responseData = $response->getData(true);
        $this->assertTrue($responseData['ok']);
        $this->assertCount(5, $responseData['data']['reminders']);
        $this->assertEquals(10, $responseData['data']['limit']);
    }

    /**
     * Test store new reminder for authenticated user.
     */
    public function test_store_creates_new_reminder_for_authenticated_user()
    {
        // Create a user
        $user = User::factory()->create();

        // Prepare request data
        $data = [
            'title' => 'Test Reminder',
            'description' => 'This is a test.',
            'remind_at' => now()->addHours(2)->timestamp,
            'event_at' => now()->addHours(2)->timestamp,
        ];

        // Mock the request with authenticated user
        $request = Request::create('/api/reminders', 'POST', $data);

        // Set the authenticated user
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $controller = new ReminderController();
        $response = $controller->store($request);

        // Assert response
        $responseData = $response->getData(true);
        $this->assertTrue($responseData['ok']);
        $this->assertEquals('Test Reminder', $responseData['data']['title']);
        $this->assertEquals('This is a test.', $responseData['data']['description']);
        $this->assertEquals((string) $data['remind_at'], $responseData['data']['remind_at']);
        $this->assertEquals((string) $data['event_at'], $responseData['data']['event_at']);

        // Assert database
        $this->assertDatabaseHas('reminders', [
            'title' => 'Test Reminder',
            'description' => 'This is a test.',
            'remind_at' => $data['remind_at'],
            'event_at' => $data['event_at'],
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test show specific amount of reminder for authenticated user.
     */
    public function test_show_returns_specific_reminder_for_authenticated_user()
    {
        // Create a user
        $user = User::factory()->create();

        // Create a reminder
        $reminder = Reminder::factory()->create([
            'user_id' => $user->id,
            'remind_at' => now()->addHours(1)->timestamp,
            'event_at' => now()->addHours(1)->timestamp,
        ]);

        // Mock the request with authenticated user
        Request::create("/api/reminders/{$reminder->id}", 'GET');

        // Mock the Auth facade to return the user id
        Auth::shouldReceive('id')->once()->andReturn($user->id);

        $controller = new ReminderController();
        $response = $controller->show($reminder->id);

        // Assert response
        $responseData = $response->getData(true);
        $this->assertTrue($responseData['ok']);
        $this->assertEquals($reminder->id, $responseData['data']['id']);
        $this->assertEquals($reminder->title, $responseData['data']['title']);
    }

    /**
     * Test fails if the reminder does not belong to the user.
     */
    public function test_show_fails_if_reminder_does_not_belong_to_authenticated_user()
    {
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create a reminder for user2
        $reminder = Reminder::factory()->create([
            'user_id' => $user2->id,
            'remind_at' => now()->addHours(1)->timestamp,
            'event_at' => now()->addHours(1)->timestamp,
        ]);

        // Mock the request with user1
        $request = Request::create("/api/reminders/{$reminder->id}", 'GET');

        // Set the authenticated user to user1
        $request->setUserResolver(function () use ($user1) {
            return $user1;
        });

        $controller = new ReminderController();

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $controller->show($reminder->id);
    }

    /**
     * Test update modifies a reminder.
     */
    public function test_update_modifies_reminder_for_authenticated_user()
    {
        // Create a user
        $user = User::factory()->create();

        // Create a reminder
        $reminder = Reminder::factory()->create([
            'user_id' => $user->id,
            'title' => 'Old Title',
            'description' => 'Old Description',
            'remind_at' => now()->addHours(1)->timestamp,
            'event_at' => now()->addHours(1)->timestamp,
        ]);

        // Prepare update data
        $updateData = [
            'title' => 'New Title',
            'description' => 'New Description',
            'remind_at' => now()->addHours(2)->timestamp,
            'event_at' => now()->addHours(2)->timestamp,
        ];

        // Mock the request
        $request = Request::create("/api/reminders/{$reminder->id}", 'PUT', $updateData);

        // Mock the Auth facade to return the user id
        Auth::shouldReceive('id')->once()->andReturn($user->id);

        $controller = new ReminderController();
        $response = $controller->update($request, $reminder->id);

        // Assert response
        $responseData = $response->getData(true);
        $this->assertTrue($responseData['ok']);
        $this->assertEquals('New Title', $responseData['data']['title']);
        $this->assertEquals('New Description', $responseData['data']['description']);
        $this->assertEquals((string) $updateData['remind_at'], $responseData['data']['remind_at']);
        $this->assertEquals((string) $updateData['event_at'], $responseData['data']['event_at']);

        // Assert database
        $this->assertDatabaseHas('reminders', [
            'id' => $reminder->id,
            'title' => 'New Title',
            'description' => 'New Description',
            'remind_at' => $updateData['remind_at'],
            'event_at' => $updateData['event_at'],
        ]);
    }

    /**
     * Test update fails if reminder does not belong to the user.
     */
    public function test_update_fails_if_reminder_does_not_belong_to_authenticated_user()
    {
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create a reminder for user2
        $reminder = Reminder::factory()->create([
            'user_id' => $user2->id,
            'title' => 'Old Title',
            'description' => 'Old Description',
            'remind_at' => now()->addHours(1)->timestamp,
            'event_at' => now()->addHours(1)->timestamp,
        ]);

        // Prepare update data
        $updateData = [
            'title' => 'New Title',
        ];

        // Mock the request with user1
        $request = Request::create("/api/reminders/{$reminder->id}", 'PUT', $updateData);

        // Mock the Auth facade to return the user id
        Auth::shouldReceive('id')->once()->andReturn($user1->id);

        // Call the update method and expect exception
        $controller = new ReminderController();

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $controller->update($request, $reminder->id);
    }

    /**
     * Test destroy removes the reminder.
     */
    public function test_destroy_deletes_reminder_for_authenticated_user()
    {
        // Create a user
        $user = User::factory()->create();

        // Create a reminder
        $reminder = Reminder::factory()->create([
            'user_id' => $user->id,
        ]);

        // Mock the request with authenticated user
        Request::create("/api/reminders/{$reminder->id}", 'DELETE');

        // Mock the Auth facade to return the user id
        Auth::shouldReceive('id')->once()->andReturn($user->id);

        $controller = new ReminderController();
        $response = $controller->destroy($reminder->id);

        // Assert response
        $responseData = $response->getData(true);
        $this->assertTrue($responseData['ok']);

        // Assert that the reminder is soft deleted
        $this->assertSoftDeleted('reminders', [
            'id' => $reminder->id,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test destroy fails if reminder does not belong to a user.
     */
    public function test_destroy_fails_if_reminder_does_not_belong_to_authenticated_user()
    {
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create a reminder for user2
        $reminder = Reminder::factory()->create([
            'user_id' => $user2->id,
        ]);

        // Fake the request with user1
        Request::create("/api/reminders/{$reminder->id}", 'DELETE');

        // Mock the Auth facade to return the user id
        Auth::shouldReceive('id')->once()->andReturn($user1->id);

        // Call the destroy method and expect exception
        $controller = new ReminderController();

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $controller->destroy($reminder->id);
    }
}
