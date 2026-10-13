<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_root_redirects_to_home(): void
    {
        $this->get('/')->assertRedirect('/home');
    }

    public function test_the_login_page_is_displayed(): void
    {
        $this->get('/login')->assertOk()->assertSee('Iniciar sesión');
    }
}
