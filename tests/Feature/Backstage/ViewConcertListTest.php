<?php

namespace Tests\Feature\Backstage;

use App\User;
use App\Concert;
use Tests\TestCase;
use App\CustomConcertFactory;
use PHPUnit\Framework\Assert;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ViewConcertListTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp()
    {
        parent::setUp();

        Collection::macro('assertContains', function ($value) {
            Assert::assertTrue($this->contains($value), "Failed asserting that the collection contains the specified value.");
        });
        Collection::macro('assertNotContains', function ($value) {
            Assert::assertFalse($this->contains($value), "Failed asserting that the collection does not contain the specified value.");
        });
    }

    public function testGuestsCannotViewPromotersConcertList()
    {
        $response = $this->get('/backstage/concerts');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function testPromotersCanOnlyViewListOfTheirOkwnConcerts()
    {
        $this->disableExceptionHandling();
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $publishedConcertA = CustomConcertFactory::createPublished(['user_id' => $user->id]);
        $publishedConcertB = CustomConcertFactory::createPublished(['user_id' => $otherUser->id]);
        $publishedConcertC = CustomConcertFactory::createPublished(['user_id' => $user->id]);
        $unpublishedConcertA = factory(Concert::class)->states('unpublished')->create(['user_id' => $user->id]);
        $unpublishedConcertB = factory(Concert::class)->states('unpublished')->create(['user_id' => $otherUser->id]);
        $unpublishedConcertC = factory(Concert::class)->states('unpublished')->create(['user_id' => $user->id]);
        $response = $this->actingAs($user)->get('/backstage/concerts');
        $response->assertStatus(200);
        $response->data('publishedConcerts')->assertContains($publishedConcertA);
        $response->data('publishedConcerts')->assertNotContains($publishedConcertB);
        $response->data('publishedConcerts')->assertContains($publishedConcertC);
        $response->data('publishedConcerts')->assertNotContains($unpublishedConcertA);
        $response->data('publishedConcerts')->assertNotContains($unpublishedConcertB);
        $response->data('publishedConcerts')->assertNotContains($unpublishedConcertC);
        $response->data('unpublishedConcerts')->assertNotContains($publishedConcertA);
        $response->data('unpublishedConcerts')->assertNotContains($publishedConcertB);
        $response->data('unpublishedConcerts')->assertNotContains($publishedConcertC);
        $response->data('unpublishedConcerts')->assertContains($unpublishedConcertA);
        $response->data('unpublishedConcerts')->assertNotContains($unpublishedConcertB);
        $response->data('unpublishedConcerts')->assertContains($unpublishedConcertC);
    }
}
