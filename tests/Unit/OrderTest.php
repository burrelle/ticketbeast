<?php

use App\Order;
use App\Concert;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class OrderTest extends TestCase
{
    use DatabaseMigrations;

    public function testTicketsAreReleasedWhenAnOrderIsCancelled()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(10);
        $order = $concert->orderTickets('jane@example.com', 5);
        $this->assertEquals(5, $concert->ticketsRemaining());
        $order->cancel();
        $this->assertEquals(10, $concert->ticketsRemaining());
        $this->assertNull(Order::find($order->id));
    }
}
