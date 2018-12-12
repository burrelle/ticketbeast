<?php

namespace Tests\Feature\Backstage;

use App\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PromoterLoginTest extends TestCase
{
    use DatabaseMigrations;

    public function testLoggingInWithValidCredentials()
    {
        $user = factory(User::class)->create([
            'email' => 'jane@example.com',
            'password' => bcrypt('super-secret-password'),
        ]);
        $response = $this->post('/login', [
            'email' => 'jane@example.com',
            'password' => 'super-secret-password'
        ]);
        $response->assertRedirect('/backstage/concerts/new');
        $this->assertTrue(Auth::check());
        $this->assertTrue(Auth::user()->is($user));
    }

    public function testLoggingInWithInvalidCredentials()
    {
        $user = factory(User::class)->create([
            'email' => 'jane@example.com',
            'password' => bcrypt('super-secret-password'),
        ]);
        $response = $this->post('/login', [
            'email' => 'jane@example.com',
            'password' => 'wrong-password'
        ]);
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertFalse(Auth::check());
    }

    public function testLoggingInWithInvalidAccount()
    {
        $response = $this->post('/login', [
            'email' => 'jane@example.com',
            'password' => 'wrong-password'
        ]);
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertFalse(Auth::check());
    }

    public function testLoggingOutTheCurrentUser()
    {
        Auth::login(factory(User::class)->create());
        $response = $this->post('/logout');
        $response->assertRedirect('/login');
        $this->assertFalse(Auth::check());
    }
}
