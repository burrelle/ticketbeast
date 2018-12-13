<?php

namespace Tests\Unit\Mail;

use App\Invitation;
use Tests\TestCase;
use App\Mail\InvitationEmail;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvitationEmailTest extends TestCase
{
    public function testEmailContainsALinkToAcceptTheInvitation()
    {
        $invitation = factory(Invitation::class)->make([
            'email' => 'john@example.com',
            'code' => 'TESTCODE1234',
        ]);
        $email = new InvitationEmail($invitation);
        $this->assertContains(url('/invitations/TESTCODE1234'), $email->render());
    }
    public function testEmailHasTheCorrectSubject()
    {
        $invitation = factory(Invitation::class)->make();
        $email = new InvitationEmail($invitation);
        $this->assertEquals("You're invited to join TicketBeast!", $email->build()->subject);
    }
}
