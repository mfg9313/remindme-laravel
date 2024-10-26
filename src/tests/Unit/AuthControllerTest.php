<?php

namespace Tests\Unit;

use App\Http\Controllers\AuthController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can log in with correct credentials.
     */
    public function test_user_can_login_with_correct_credentials()
    {
        // Create user
        $user = User::factory()->create([
            'password' => Hash::make('123456'),
        ]);

        // Mocking POST to /api/session
        $request = Request::create('/api/session', 'POST', [
            'email' => $user->email,
            'password' => '123456',
        ]);

        // Instantiate the controller
        $controller = new AuthController();

        // Call the login method
        $response = $controller->login($request);

        // Assert response
        $responseData = $response->getData(true);
        $this->assertTrue($responseData['ok']);
        $this->assertArrayHasKey('access_token', $responseData['data']);
        $this->assertArrayHasKey('refresh_token', $responseData['data']);
        $this->assertEquals($user->id, $responseData['data']['user']['id']);
        $this->assertEquals($user->email, $responseData['data']['user']['email']);
        $this->assertEquals($user->name, $responseData['data']['user']['name']);
    }

    /**
     * Test login fails with incorrect credentials.
     */
    public function test_login_fails_with_incorrect_credentials()
    {
        // Create a user
        $user = User::factory()->create([
            'password' => Hash::make('123456'),
        ]);

        // Mocking POST to /api/session
        $request = Request::create('/api/session', 'POST', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        // Instantiate the controller
        $controller = new AuthController();

        // Call the login method
        $response = $controller->login($request);

        // Assert response
        $responseData = $response->getData(true);
        $this->assertFalse($responseData['ok']);
        $this->assertEquals('ERR_INVALID_CREDS', $responseData['err']);
        $this->assertEquals('incorrect username or password', $responseData['msg']);
        $this->assertEquals(401, $response->status());
    }

    /**
     * Test login fails with a user that doesn't exist.
     */
    public function test_login_fails_with_non_user()
    {
        // Create fake request
        $request = Request::create('/api/session', 'POST', [
            'email' => 'notauser@example.com',
            'password' => 'notauser_password',
        ]);

        // Instantiate the controller
        $controller = new AuthController();

        // Call the login method
        $response = $controller->login($request);

        // Assert response
        $responseData = $response->getData(true);
        $this->assertFalse($responseData['ok']);
        $this->assertEquals('ERR_INVALID_CREDS', $responseData['err']);
        $this->assertEquals('incorrect username or password', $responseData['msg']);
        $this->assertEquals(401, $response->status());
    }

    /**
     * Test refresh token with valid token.
     */
    public function test_refresh_token_with_valid_token()
    {
        // Create a user
        $user = User::factory()->create();

        // Create a valid refresh token
        $refreshToken = Str::uuid()->toString();
        $hashedToken = hash('sha256', $refreshToken);

        $user->refreshTokens()->create([
            'token' => $hashedToken,
            'expires_at' => now()->addDays(30),
        ]);

        // Mock PUT to /api/session with Bearer token
        $request = Request::create('/api/session', 'PUT', [], [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $refreshToken,
        ]);

        // Instantiate the controller
        $controller = new AuthController();

        // Call the login method
        $response = $controller->refresh($request);

        // Assert response
        $responseData = $response->getData(true);
        $this->assertTrue($responseData['ok']);
        $this->assertArrayHasKey('access_token', $responseData['data']);
    }

    /**
     * Test refresh token with invalid token.
     */
    public function test_refresh_token_with_invalid_token()
    {
        // Mock PUT to /api/session with invalid Bearer token
        $request = Request::create('/api/session', 'PUT', [], [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer invalidtoken',
        ]);

        // Instantiate the controller
        $controller = new AuthController();

        // Call the login method
        $response = $controller->refresh($request);

        // Assert response
        $responseData = $response->getData(true);
        $this->assertFalse($responseData['ok']);
        $this->assertEquals('ERR_INVALID_REFRESH_TOKEN', $responseData['err']);
        $this->assertEquals('invalid refresh token', $responseData['msg']);
        $this->assertEquals(401, $response->status());
    }
}
