<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_visitante_e_redirecionado_para_o_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }
}
