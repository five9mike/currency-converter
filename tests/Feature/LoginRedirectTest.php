<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginRedirectTest extends TestCase
{
    /**
     * Browsing to the homepage without auth should redirect to login
     *
     * @return void
     */
    public function test_the_application_homepage_redirects_to_login()
    {
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
}
