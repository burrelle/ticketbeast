<?php

namespace Tests\Feature;

use App\User;
use App\Invitation;
use Tests\TestCase;
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
}
