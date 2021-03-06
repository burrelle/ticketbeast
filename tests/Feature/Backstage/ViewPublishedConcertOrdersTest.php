<?php

namespace Tests\Features\Backstage;

use App\User;
use Carbon\Carbon;
use Tests\TestCase;
use App\CustomOrderFactory;
use App\CustomConcertFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;

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

    public function testPromoterCanViewThe10MostRecentOrdersForTheirConcert()
    {
        $this->disableExceptionHandling();
        $user = factory(User::class)->create();
        $concert = CustomConcertFactory::createPublished(['user_id' => $user->id]);
        $oldOrder = CustomOrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('11 days ago')]);
        $recentOrder1 = CustomOrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('10 days ago')]);
        $recentOrder2 = CustomOrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('9 days ago')]);
        $recentOrder3 = CustomOrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('8 days ago')]);
        $recentOrder4 = CustomOrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('7 days ago')]);
        $recentOrder5 = CustomOrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('6 days ago')]);
        $recentOrder6 = CustomOrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('5 days ago')]);
        $recentOrder7 = CustomOrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('4 days ago')]);
        $recentOrder8 = CustomOrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('3 days ago')]);
        $recentOrder9 = CustomOrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('2 days ago')]);
        $recentOrder10 = CustomOrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('1 days ago')]);
        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");
        $response->data('orders')->assertNotContains($oldOrder);
        $response->data('orders')->assertEquals([
            $recentOrder10,
            $recentOrder9,
            $recentOrder8,
            $recentOrder7,
            $recentOrder6,
            $recentOrder5,
            $recentOrder4,
            $recentOrder3,
            $recentOrder2,
            $recentOrder1,
        ]);
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
