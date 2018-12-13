<?php

namespace Tests\Feature;

use App\User;
use App\Invitation;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AcceptInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function testViewingAnUnusedInvitation()
    {
        $this->withoutExceptionHandling();
        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE1234',
        ]);
        $response = $this->get('/invitations/TESTCODE1234');
        $response->assertStatus(200);
        $response->assertViewIs('invitations.show');
        $this->assertTrue($response->data('invitation')->is($invitation));
    }

    public function testViewingAUsedInvitation()
    {
        $invitation = factory(Invitation::class)->create([
            'user_id' => factory(User::class)->create(),
            'code' => 'TESTCODE1234',
        ]);
        $response = $this->get('/invitations/TESTCODE1234');
        $response->assertStatus(404);
    }

    public function testViewingAnInvitationThatDoesNotExist()
    {
        $response = $this->get('/invitations/TESTCODE1234');
        $response->assertStatus(404);
    }

    public function testRegisteringWithAValidInvitationCode()
    {
        $this->withoutExceptionHandling();
        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE1234',
        ]);
        $response = $this->post('/register', [
            'email' => 'john@example.com',
            'password' => 'secret',
            'invitation_code' => 'TESTCODE1234',
        ]);
        $response->assertRedirect('/backstage/concerts');
        $this->assertEquals(1, User::count());
        $user = User::first();
        $this->assertAuthenticatedAs($user);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertTrue(Hash::check('secret', $user->password));
        $this->assertTrue($invitation->fresh()->user->is($user));
    }


    public function testRegisteringWithAUsedInvitationCode()
    {
        $invitation = factory(Invitation::class)->create([
            'user_id' => factory(User::class)->create(),
            'code' => 'TESTCODE1234',
        ]);
        $this->assertEquals(1, User::count());
        $response = $this->post('/register', [
            'email' => 'john@example.com',
            'password' => 'secret',
            'invitation_code' => 'TESTCODE1234',
        ]);
        $response->assertStatus(404);
        $this->assertEquals(1, User::count());
    }

    public function testRegisteringWithAnInvitationCodeThatDoesNotExist()
    {
        $response = $this->post('/register', [
            'email' => 'john@example.com',
            'password' => 'secret',
            'invitation_code' => 'TESTCODE1234',
        ]);
        $response->assertStatus(404);
        $this->assertEquals(0, User::count());
    }


    public function testEmailIsRequired()
    {
        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE1234',
        ]);
        $response = $this->from('/invitations/TESTCODE1234')->post('/register', [
            'email' => '',
            'password' => 'secret',
            'invitation_code' => 'TESTCODE1234',
        ]);
        $response->assertRedirect('/invitations/TESTCODE1234');
        $response->assertSessionHasErrors('email');
        $this->assertEquals(0, User::count());
    }

    public function testEmailMustBeAnEmail()
    {
        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE1234',
        ]);
        $response = $this->from('/invitations/TESTCODE1234')->post('/register', [
            'email' => 'not-an-email',
            'password' => 'secret',
            'invitation_code' => 'TESTCODE1234',
        ]);
        $response->assertRedirect('/invitations/TESTCODE1234');
        $response->assertSessionHasErrors('email');
        $this->assertEquals(0, User::count());
    }

    public function testEmailMustBeUnique()
    {
        $existingUser = factory(User::class)->create(['email' => 'john@example.com']);
        $this->assertEquals(1, User::count());
        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE1234',
        ]);
        $response = $this->from('/invitations/TESTCODE1234')->post('/register', [
            'email' => 'john@example.com',
            'password' => 'secret',
            'invitation_code' => 'TESTCODE1234',
        ]);
        $response->assertRedirect('/invitations/TESTCODE1234');
        $response->assertSessionHasErrors('email');
        $this->assertEquals(1, User::count());
    }

    public function testPasswordIsRequired()
    {
        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE1234',
        ]);
        $response = $this->from('/invitations/TESTCODE1234')->post('/register', [
            'email' => 'john@example.com',
            'password' => '',
            'invitation_code' => 'TESTCODE1234',
        ]);
        $response->assertRedirect('/invitations/TESTCODE1234');
        $response->assertSessionHasErrors('password');
        $this->assertEquals(0, User::count());
    }
}
