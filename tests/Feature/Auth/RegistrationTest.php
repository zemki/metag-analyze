<?php

namespace Tests\Feature\Auth;

use App\User;
use Tests\TestCase;

class RegistrationTest extends TestCase
{

    /**
     * Test the registration page loads correctly.
     *
     * @return void
     */
    public function test_registration_page_loads()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);

        // Just check that the page loads and contains the captcha element
        $response->assertSee('altcha-widget', false);
    }

    /**
     * Test registration with missing altcha token.
     *
     * @return void
     */
    public function test_registration_with_missing_altcha_token()
    {
        $userData = [
            'email' => 'missing_token_test@example.com',
            'password' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertSessionHasErrors('altcha');

        $this->assertDatabaseMissing('users', [
            'email' => 'missing_token_test@example.com',
        ]);
    }

    /**
     * Test submission of form with empty (null) altcha token.
     *
     * @return void
     */
    public function test_null_altcha_token_handling()
    {
        $userData = [
            'email' => 'null_token_test@example.com',
            'password' => 'password123',
            'altcha' => null,
        ];

        $response = $this->post('/register', $userData);

        $response->assertSessionHasErrors(['altcha' => 'Please complete the captcha verification.']);

        $this->assertDatabaseMissing('users', [
            'email' => 'null_token_test@example.com',
        ]);
    }
}
