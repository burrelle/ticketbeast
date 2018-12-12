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

        Collection::macro('assertEquals', function ($items) {
            Assert::assertEquals(count($this), count($items));
            $this->zip($items)->each(function ($pair) {
                list($a, $b) = $pair;
                Assert::assertTrue($a->is($b));
            });
        });
    }

    public function testGuestsCannotViewPromotersConcertList()
    {
        $response = $this->get('/backstage/concerts');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function testPromotersCanOnlyViewListOfTheirOwnConcerts()
    {
        $this->disableExceptionHandling();
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $publishedConcertA = CustomConcertFactory::createPublished(['user_id' => $user->id]);
        $publishedConcertB = CustomConcertFactory::createPublished(['user_id' => $otherUser->id]);
        $publishedConcertC = CustomConcertFactory::createPublished(['user_id' => $user->id]);
        $unpublishedConcertA = CustomConcertFactory::createUnpublished(['user_id' => $user->id]);
        $unpublishedConcertB = CustomConcertFactory::createUnpublished(['user_id' => $otherUser->id]);
        $unpublishedConcertC = CustomConcertFactory::createUnpublished(['user_id' => $user->id]);
        $response = $this->actingAs($user)->get('/backstage/concerts');
        $response->assertStatus(200);
        $response->data('publishedConcerts')->assertEquals([
            $publishedConcertA,
            $publishedConcertC,
        ]);
        $response->data('unpublishedConcerts')->assertEquals([
            $unpublishedConcertA,
            $unpublishedConcertC,
        ]);
    }
}
