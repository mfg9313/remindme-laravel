<?php

namespace Tests\Feature;

use App\Models\RefreshToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthControllerFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a user can log in with correct credentials.
     *
     * @return void
     */
    public function test_user_can_login_with_correct_credentials()
    {
        // Create a user with known credentials
        $user = User::factory()->create([
            'password' => Hash::make('123456'),
        ]);

        // Send POST request to /api/session for login
        $response = $this->postJson('/api/session', [
            'email' => $user->email,
            'password' => '123456',
        ]);

        // Assert that the response is successful
        $response->assertStatus(200)
            ->assertJson([
                'ok' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                        'name' => $user->name,
                    ],
                ],
            ])
            ->assertJsonStructure([
                'ok',
                'data' => [
                    'access_token',
                    'refresh_token',
                    'user' => [
                        'id',
                        'email',
                        'name',
                    ],
                ],
            ]);

        // Assert that the access token exists in the database
        $accessToken = $response->json('data.access_token');
        $this->assertNotNull($accessToken, 'Access token is missing.');

        // Extract the token's plain part and hash it for database verification
        $tokenParts = explode('|', $accessToken, 2);
        $this->assertCount(2, $tokenParts, 'Access token format is invalid.');
        $hashedAccessToken = hash('sha256', $tokenParts[1]);

        // Verify that the token exists in the database
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => get_class($user),
            'token' => $hashedAccessToken,
        ]);

        // Confirming that the refresh token exists
        $refreshToken = $response->json('data.refresh_token');
        $this->assertNotNull($refreshToken, 'Refresh token is missing.');

        // Assume RefreshToken model stores hashed tokens
        $hashedRefreshToken = hash('sha256', $refreshToken);

        $this->assertDatabaseHas('refresh_tokens', [
            'user_id' => $user->id,
            'token' => $hashedRefreshToken,
        ]);
    }

    /**
     * Test that login fails with incorrect credentials.
     *
     * @return void
     */
    public function test_login_fails_with_incorrect_credentials()
    {
        // Create a user with known credentials
        $user = User::factory()->create([
            'password' => Hash::make('123456'),
        ]);

        // Attempt to log in with incorrect password
        $response = $this->postJson('/api/session', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        // Assert that the response has a 401 status and appropriate error message
        $response->assertStatus(401)
            ->assertJson([
                'ok' => false,
                'err' => 'ERR_INVALID_CREDS',
                'msg' => 'incorrect username or password',
            ]);
    }

    /**
     * Test that a user can refresh their token.
     *
     * @return void
     */
    public function test_user_can_refresh_token_with_valid_refresh_token()
    {
        // Create a user
        $user = User::factory()->create();

        // Create a valid refresh token for the user
        $plainRefreshToken = Str::uuid()->toString();
        $hashedRefreshToken = hash('sha256', $plainRefreshToken);

        RefreshToken::create([
            'user_id' => $user->id,
            'token' => $hashedRefreshToken,
            'expires_at' => Carbon::now()->addDays(30),
        ]);

        // Send PUT request to /api/session with the refresh token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $plainRefreshToken,
        ])->putJson('/api/session');

        // Assert that the response is successful
        $response->assertStatus(200)
            ->assertJson([
                'ok' => true,
                'data' => []
            ])
            ->assertJsonStructure([
                'ok',
                'data' => [
                    'access_token',
                ],
            ]);

        // Assert that a new access token is created in the database
        $accessToken = $response->json('data.access_token');
        $this->assertNotNull($accessToken, 'Access token is missing.');

        $tokenParts = explode('|', $accessToken, 2);
        $this->assertCount(2, $tokenParts, 'Access token format is invalid.');
        $hashedAccessToken = hash('sha256', $tokenParts[1]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => get_class($user),
            'token' => $hashedAccessToken,
        ]);
    }

    /**
     * Test that refreshing the token fails with an invalid refresh token.
     *
     * @return void
     */
    public function test_refresh_token_fails_with_invalid_token()
    {
        // Attempt to refresh token with an invalid token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalidrefreshToken',
        ])->putJson('/api/session');

        // Assert that the response has a 401 status and appropriate error message
        $response->assertStatus(401)
            ->assertJson([
                'ok' => false,
                'err' => 'ERR_INVALID_REFRESH_TOKEN',
                'msg' => 'invalid refresh token',
            ]);
    }

    /**
     * Test that refreshing token fails when the refresh token is expired.
     *
     * @return void
     */
    public function test_refresh_token_fails_when_expired()
    {
        // Create a user
        $user = User::factory()->create();

        // Create an expired refresh token
        $plainRefreshToken = Str::uuid()->toString();
        $hashedRefreshToken = hash('sha256', $plainRefreshToken);

        RefreshToken::create([
            'user_id' => $user->id,
            'token' => $hashedRefreshToken,
            'expires_at' => Carbon::now()->subDays(1),
        ]);

        // Attempt to refresh token with the expired refresh token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $plainRefreshToken,
        ])->putJson('/api/session');

        // Assert that the response has a 401 status and appropriate error message
        $response->assertStatus(401)
            ->assertJson([
                'ok' => false,
                'err' => 'ERR_INVALID_REFRESH_TOKEN',
                'msg' => 'invalid refresh token',
            ]);
    }

    /**
     * Test that an authenticated user can log out successfully.
     *
     * @return void
     */
    public function test_authenticated_user_can_logout()
    {
        // Create a user
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        // Log in the user to get access and refresh tokens
        $loginResponse = $this->postJson('/api/session', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJson([
                'ok' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                        'name' => $user->name,
                    ],
                ],
            ]);

        $accessToken = $loginResponse->json('data.access_token');
        $refreshToken = $loginResponse->json('data.refresh_token');

        // Assert that access token exists in the database
        $tokenParts = explode('|', $accessToken, 2);
        $hashedAccessToken = hash('sha256', $tokenParts[1]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => get_class($user),
            'token' => $hashedAccessToken,
        ]);

        // Mock the POST request to /api/logout
        $logoutResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->postJson('/api/logout');

        // Assert that logout was successful
        $logoutResponse->assertStatus(200)
            ->assertJson([
                'ok' => true,
                'msg' => 'Logged out successfully.',
            ]);

        // Assert that the access token is removed
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => get_class($user),
            'token' => $hashedAccessToken,
        ]);
    }

    /**
     * Test that logout fails when not authenticated.
     *
     * @return void
     */
    public function test_logout_fails_when_not_authenticated()
    {
        // Attempt to log out without providing an access token
        $response = $this->postJson('/api/logout');

        // Assert that the response has a 401 status and appropriate error message
        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    /**
     * Test that logging out with an invalid token fails.
     *
     * @return void
     */
    public function test_logout_fails_with_invalid_token()
    {
        // Attempt to log out with an invalid access token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalidaccesstoken',
        ])->postJson('/api/logout');

        // Assert that the response has a 401 status and appropriate error message
        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }
}
