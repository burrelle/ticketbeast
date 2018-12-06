<?php

use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use App\Exceptions\NotEnoughTicketsException;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ConcertTest extends TestCase
{
    use DatabaseMigrations;

    public function testCanGetFormattedDate()
    {
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01 8:00 pm')
        ]);

        $this->assertEquals('December 1, 2016', $concert->formatted_date);
    }

    public function testCanGetFormattedStartTime()
    {
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01 17:00:00')
        ]);

        $this->assertEquals('5:00 pm', $concert->formatted_start_time);
    }

    public function testCanGetTicketPriceInDollars()
    {
        $concert = factory(Concert::class)->make([
            'ticket_price' => '6750'
        ]);

        $this->assertEquals('67.50', $concert->ticket_price_in_dollars);
    }

    public function testConcertsWithAPublishedtADateArePublished()
    {
        $publishedConcertA = factory(Concert::class)->create(['published_at' => Carbon::parse('-1 week')]);
        $publishedConcertB = factory(Concert::class)->create(['published_at' => Carbon::parse('-1 week')]);
        $unpublishedConcert = factory(Concert::class)->create(['published_at' => null ]);
        $publishedConcerts = Concert::published()->get();
        $this->assertTrue($publishedConcerts->contains($publishedConcertA));
        $this->assertTrue($publishedConcerts->contains($publishedConcertB));
        $this->assertFalse($publishedConcerts->contains($unpublishedConcert));
    }

    public function testCanOrderConcertTickets()
    {
        $concert = factory(Concert::class)->create()->addTickets(3);
        $order = $concert->orderTickets('jane@example.com', 3);
        $this->assertEquals('jane@example.com', $order->email);
        $this->assertEquals(3, $order->ticketQuantity());
    }

    public function testCanAddTickets()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(50);
        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    public function testTicketsRemainingDoesNotIncludeTicketsAssoicatedWithAnOrder()
    {
        $concert = factory(Concert::class)->create()->addTickets(50);
        $concert->orderTickets('jane@example.com', 30);
        $this->assertEquals(20, $concert->ticketsRemaining());
    }

    public function testTryingToPurchaseMoreTicketsThanRemainThrowsAnException()
    {
        $concert = factory(Concert::class)->create()->addTickets(10);
        try{
            $concert->orderTickets('jane@example.com', 11);
        } catch (NotEnoughTicketsException $e) {
            $this->assertFalse($concert->hasOrderFor('jane@example.com'));
            $this->assertEquals(10, $concert->ticketsRemaining());
            return;
        }

        $this->fail('Order succeeded even though there were not enough tickets remaining');
    }

    public function testCannotOrderTicketsThatHaveAlreadyBeenPurchased()
    {
        $concert = factory(Concert::class)->create()->addTickets(10);
        $concert->orderTickets('jane@example.com', 8);
        try{
            $concert->orderTickets('john@example.com', 3);
        } catch (NotEnoughTicketsException $e) {
            $this->assertFalse($concert->hasOrderFor('john@example.com'));
            $this->assertEquals(2, $concert->ticketsRemaining());
            return;
        }

        $this->fail('Order succeeded even though there were not enough tickets remaining');
    }
}
