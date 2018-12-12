<?php

namespace Tests\Feature\Backstage;

use App\User;
use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class EditConcertTest extends TestCase
{
    use DatabaseMigrations;

    private function validParams($overrides = [])
    {
        return array_merge([
            'title' => 'New title',
            'subtitle' => 'New subtitle',
            'additional_information' => 'New additional information',
            'date' => '2018-12-12',
            'time' => '8:00pm',
            'venue' => 'New venue',
            'venue_address' => 'New address',
            'city' => 'New city',
            'state' => 'New state',
            'zip' => '99999',
            'ticket_price' => '72.50',
        ], $overrides);
    }

    public function testPromotersCanViewTheEditFromForUnpublishedConcerts()
    {
        $this->disableExceptionHandling();

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $user->id]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(200);
        $this->assertTrue($response->data('concert')->is($concert));
    }

    public function testPromotoersCannotViewEditForPublishedConcerts()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('published')->create(['user_id' => $user->id]);
        $this->assertTrue($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(403);
    }

    public function testPromotersCannotEditOtherPeoplesConcerts()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(404);
    }

    public function testEditCannotWorkOnNotRealConcert()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get("/backstage/concerts/999/edit");

        $response->assertStatus(404);
    }

    public function testGuestsAreRedirectedToLoginForm()
    {
        $otherUser = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $otherUser->id]);

        $response = $this->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function testGuestsAreRedirectedToLoginFormForConcertThatDoesntExist()
    {
        $response = $this->get("/backstage/concerts/999/edit");

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function testPromotersCanEditTheirOwnUnpublishedConcerts()
    {
        $this->disableExceptionHandling();
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'title' => 'Old title',
            'subtitle' => 'Old subtitle',
            'additional_information' => 'Old additional information',
            'date' => Carbon::parse('2017-01-01 5:00pm'),
            'venue' => 'Old venue',
            'venue_address' => 'Old address',
            'city' => 'Old city',
            'state' => 'Old state',
            'zip' => '00000',
            'ticket_price' => 2000,
        ]);
        $this->assertFalse($concert->isPublished());
        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", [
            'title' => 'New title',
            'subtitle' => 'New subtitle',
            'additional_information' => 'New additional information',
            'date' => '2018-12-12',
            'time' => '8:00pm',
            'venue' => 'New venue',
            'venue_address' => 'New address',
            'city' => 'New city',
            'state' => 'New state',
            'zip' => '99999',
            'ticket_price' => '72.50',
        ]);
        $response->assertRedirect("/backstage/concerts");
        tap($concert->fresh(), function ($concert) {
            $this->assertEquals('New title', $concert->title);
            $this->assertEquals('New subtitle', $concert->subtitle);
            $this->assertEquals('New additional information', $concert->additional_information);
            $this->assertEquals(Carbon::parse('2018-12-12 8:00pm'), $concert->date);
            $this->assertEquals('New venue', $concert->venue);
            $this->assertEquals('New address', $concert->venue_address);
            $this->assertEquals('New city', $concert->city);
            $this->assertEquals('New state', $concert->state);
            $this->assertEquals('99999', $concert->zip);
            $this->assertEquals(7250, $concert->ticket_price);
        });
    }

    public function testPromotersCannotEditOtherUnpublishedConcerts()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => $otherUser->id,
            'title' => 'Old title',
            'subtitle' => 'Old subtitle',
            'additional_information' => 'Old additional information',
            'date' => Carbon::parse('2017-01-01 5:00pm'),
            'venue' => 'Old venue',
            'venue_address' => 'Old address',
            'city' => 'Old city',
            'state' => 'Old state',
            'zip' => '00000',
            'ticket_price' => 2000,
        ]);
        $this->assertFalse($concert->isPublished());
        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", [
            'title' => 'New title',
            'subtitle' => 'New subtitle',
            'additional_information' => 'New additional information',
            'date' => '2018-12-12',
            'time' => '8:00pm',
            'venue' => 'New venue',
            'venue_address' => 'New address',
            'city' => 'New city',
            'state' => 'New state',
            'zip' => '99999',
            'ticket_price' => '72.50',
        ]);
        $response->assertStatus(404);
        tap($concert->fresh(), function ($concert) {
            $this->assertEquals('Old title', $concert->title);
            $this->assertEquals('Old subtitle', $concert->subtitle);
            $this->assertEquals('Old additional information', $concert->additional_information);
            $this->assertEquals(Carbon::parse('2017-01-01 5:00pm'), $concert->date);
            $this->assertEquals('Old venue', $concert->venue);
            $this->assertEquals('Old address', $concert->venue_address);
            $this->assertEquals('Old city', $concert->city);
            $this->assertEquals('Old state', $concert->state);
            $this->assertEquals('00000', $concert->zip);
            $this->assertEquals(2000, $concert->ticket_price);
        });
    }

    public function testPromotersCannotEditPublishedConcerts()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('published')->create([
            'user_id' => $user->id,
            'title' => 'Old title',
            'subtitle' => 'Old subtitle',
            'additional_information' => 'Old additional information',
            'date' => Carbon::parse('2017-01-01 5:00pm'),
            'venue' => 'Old venue',
            'venue_address' => 'Old address',
            'city' => 'Old city',
            'state' => 'Old state',
            'zip' => '00000',
            'ticket_price' => 2000,
        ]);
        $this->assertTrue($concert->isPublished());
        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", [
            'title' => 'New title',
            'subtitle' => 'New subtitle',
            'additional_information' => 'New additional information',
            'date' => '2018-12-12',
            'time' => '8:00pm',
            'venue' => 'New venue',
            'venue_address' => 'New address',
            'city' => 'New city',
            'state' => 'New state',
            'zip' => '99999',
            'ticket_price' => '72.50',
        ]);
        $response->assertStatus(403);
        tap($concert->fresh(), function ($concert) {
            $this->assertEquals('Old title', $concert->title);
            $this->assertEquals('Old subtitle', $concert->subtitle);
            $this->assertEquals('Old additional information', $concert->additional_information);
            $this->assertEquals(Carbon::parse('2017-01-01 5:00pm'), $concert->date);
            $this->assertEquals('Old venue', $concert->venue);
            $this->assertEquals('Old address', $concert->venue_address);
            $this->assertEquals('Old city', $concert->city);
            $this->assertEquals('Old state', $concert->state);
            $this->assertEquals('00000', $concert->zip);
            $this->assertEquals(2000, $concert->ticket_price);
        });
    }
    /** @test */
    function guests_cannot_edit_concerts()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'title' => 'Old title',
            'subtitle' => 'Old subtitle',
            'additional_information' => 'Old additional information',
            'date' => Carbon::parse('2017-01-01 5:00pm'),
            'venue' => 'Old venue',
            'venue_address' => 'Old address',
            'city' => 'Old city',
            'state' => 'Old state',
            'zip' => '00000',
            'ticket_price' => 2000,
        ]);
        $this->assertFalse($concert->isPublished());
        $response = $this->patch("/backstage/concerts/{$concert->id}", [
            'title' => 'New title',
            'subtitle' => 'New subtitle',
            'additional_information' => 'New additional information',
            'date' => '2018-12-12',
            'time' => '8:00pm',
            'venue' => 'New venue',
            'venue_address' => 'New address',
            'city' => 'New city',
            'state' => 'New state',
            'zip' => '99999',
            'ticket_price' => '72.50',
        ]);
        $response->assertRedirect('/login');
        tap($concert->fresh(), function ($concert) {
            $this->assertEquals('Old title', $concert->title);
            $this->assertEquals('Old subtitle', $concert->subtitle);
            $this->assertEquals('Old additional information', $concert->additional_information);
            $this->assertEquals(Carbon::parse('2017-01-01 5:00pm'), $concert->date);
            $this->assertEquals('Old venue', $concert->venue);
            $this->assertEquals('Old address', $concert->venue_address);
            $this->assertEquals('Old city', $concert->city);
            $this->assertEquals('Old state', $concert->state);
            $this->assertEquals('00000', $concert->zip);
            $this->assertEquals(2000, $concert->ticket_price);
        });
    }
    /** @test */
    function title_is_required()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'title' => 'Old title',
            'subtitle' => 'Old subtitle',
            'additional_information' => 'Old additional information',
            'date' => Carbon::parse('2017-01-01 5:00pm'),
            'venue' => 'Old venue',
            'venue_address' => 'Old address',
            'city' => 'Old city',
            'state' => 'Old state',
            'zip' => '00000',
            'ticket_price' => 2000,
        ]);
        $this->assertFalse($concert->isPublished());
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'title' => '',
        ]));
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('title');
        tap($concert->fresh(), function ($concert) {
            $this->assertEquals('Old title', $concert->title);
            $this->assertEquals('Old subtitle', $concert->subtitle);
            $this->assertEquals('Old additional information', $concert->additional_information);
            $this->assertEquals(Carbon::parse('2017-01-01 5:00pm'), $concert->date);
            $this->assertEquals('Old venue', $concert->venue);
            $this->assertEquals('Old address', $concert->venue_address);
            $this->assertEquals('Old city', $concert->city);
            $this->assertEquals('Old state', $concert->state);
            $this->assertEquals('00000', $concert->zip);
            $this->assertEquals(2000, $concert->ticket_price);
        });
    }
}
