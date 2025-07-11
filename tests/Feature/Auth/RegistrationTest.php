<?php

namespace Tests\Feature\Auth;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

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
    'email' => 'missing_token_test@example.com', // Unique email
    'password' => 'password123',
    // No altcha token
    ];

    $response = $this->post('/register', $userData);
    
    // Should redirect back with errors
    $response->assertSessionHasErrors('altoken');
    
    // Ensure user wasn't created
    $this->assertDatabaseMissing('users', [
    'email' => 'missing_token_test@example.com',
    ]);
    }

    /**
    * Test submission of form with empty (null) altoken.
    *
    * @return void
    */
    public function test_null_altcha_token_handling()
    {
    $userData = [
    'email' => 'null_token_test@example.com', // Unique email
    'password' => 'password123',
    'altoken' => null, // Explicitly null
    ];

    $response = $this->post('/register', $userData);
    
    // Check for required field error
    $response->assertSessionHasErrors(['altoken' => 'Please complete the captcha verification.']);
    
    // Ensure user wasn't created
    $this->assertDatabaseMissing('users', [
    'email' => 'null_token_test@example.com',
    ]);
    }
}
