<?php

namespace Tests\Features\Backstage;

use App\User;
use Tests\TestCase;
use App\CustomConcertFactory;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ViewPublishedConcertOrdersTest extends TestCase
{
    use DatabaseMigrations;

    public function testAPromoterCanViewTheOrdersOfTheirOwnPublishedConcert()
    {
        $this->disableExceptionHandling();
        $user = factory(User::class)->create();
        $concert = CustomConcertFactory::createPublished(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertStatus(200);
        $response->assertViewIs('backstage.published-concert-orders.index');
        $this->assertTrue($response->data('concert')->is($concert));
    }

    public function testAPromoterCannotViewTheOrdersOfUnpublishedConcerts()
    {
        $user = factory(User::class)->create();
        $concert = CustomConcertFactory::createUnpublished(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertStatus(404);
    }

    public function testAPromoterCannotViewTheOrdersOfAnotherPublishedConcert()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $concert = CustomConcertFactory::createPublished(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertStatus(404);
    }

    public function testAGuestCannotViewTheOrdersOfAnyPublishedConcert()
    {
        $concert = CustomConcertFactory::createPublished();

        $response = $this->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertRedirect('/login');
    }
}
