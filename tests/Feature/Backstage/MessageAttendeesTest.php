<?php

namespace Tests\Feature\Backstage;

use App\User;
use Tests\TestCase;
use App\AttendeeMessage;
use App\CustomConcertFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class MessageAttendeesTest extends TestCase
{
    use DatabaseMigrations;

    public function testAPromoterCanViewTheMessageFormForTheirOwnConcert()
    {
        $this->disableExceptionHandling();

        $user = factory(User::class)->create();
        $concert = CustomConcertFactory::createPublished([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertStatus(200);
        $response->assertViewIs('backstage.concert-messages.new');
        $this->assertTrue($response->data('concert')->is($concert));
    }

    public function testAPromoterCannotViewTheMessageFormForAnotherConcert()
    {
        $user = factory(User::class)->create();
        $concert = CustomConcertFactory::createPublished([
            'user_id' => factory(User::class)->create(),
        ]);

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertStatus(404);
    }

    public function testAGuestCannotViewTheMessageFormForAnyConcert()
    {
        $concert = CustomConcertFactory::createPublished();

        $response = $this->get("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertRedirect('/login');
    }

    public function testPromoterCanSendANewMessage()
    {
        $this->disableExceptionHandling();
        $user = factory(User::class)->create();
        $concert = CustomConcertFactory::createPublished([
            'user_id' => $user->id,
        ]);
        $response = $this->actingAs($user)->post("/backstage/concerts/{$concert->id}/messages", [
            'subject' => 'My subject',
            'message' => 'My message',
        ]);
        $response->assertRedirect("/backstage/concerts/{$concert->id}/messages/new");
        $response->assertSessionHas('flash');
        $message = AttendeeMessage::first();
        $this->assertEquals($concert->id, $message->concert_id);
        $this->assertEquals('My subject', $message->subject);
        $this->assertEquals('My message', $message->message);
    }


    public function testAPromoterCannotSendANewMessageForOtherConcerts()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $concert = CustomConcertFactory::createPublished([
            'user_id' => $otherUser->id,
        ]);
        $response = $this->actingAs($user)->post("/backstage/concerts/{$concert->id}/messages", [
            'subject' => 'My subject',
            'message' => 'My message',
        ]);
        $response->assertStatus(404);
        $this->assertEquals(0, AttendeeMessage::count());
    }

    public function testAGuestCannotSendANewMessageForAnyConcerts()
    {
        $concert = CustomConcertFactory::createPublished();
        $response = $this->post("/backstage/concerts/{$concert->id}/messages", [
            'subject' => 'My subject',
            'message' => 'My message',
        ]);
        $response->assertRedirect('/login');
        $this->assertEquals(0, AttendeeMessage::count());
    }

    public function testSubjectIsRequired()
    {
        $user = factory(User::class)->create();
        $concert = CustomConcertFactory::createPublished([
            'user_id' => $user->id,
        ]);
        $response = $this->from("/backstage/concerts/{$concert->id}/messages/new")
            ->actingAs($user)
            ->post("/backstage/concerts/{$concert->id}/messages", [
                'subject' => '',
                'message' => 'My message',
            ]);
        $response->assertRedirect("/backstage/concerts/{$concert->id}/messages/new");
        $response->assertSessionHasErrors('subject');
        $this->assertEquals(0, AttendeeMessage::count());
    }

    public function testMessageIsRequired()
    {
        $user = factory(User::class)->create();
        $concert = CustomConcertFactory::createPublished([
            'user_id' => $user->id,
        ]);
        $response = $this->from("/backstage/concerts/{$concert->id}/messages/new")
            ->actingAs($user)
            ->post("/backstage/concerts/{$concert->id}/messages", [
                'subject' => 'My subject',
                'message' => '',
            ]);
        $response->assertRedirect("/backstage/concerts/{$concert->id}/messages/new");
        $response->assertSessionHasErrors('message');
        $this->assertEquals(0, AttendeeMessage::count());
    }
}
