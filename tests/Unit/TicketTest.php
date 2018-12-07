<?php

use App\Ticket;
use App\Concert;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class TicketTest extends TestCase
{
    use DatabaseMigrations;

    public function testTicketCanBeReserved()
    {
        $ticket = factory(Ticket::class)->create();
        $this->assertNull($ticket->reserved_at);
        $ticket->reserve();
        $this->assertNotNull($ticket->fresh()->reserved_at);
    }

    public function testTicketCanBeReleased()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(1);
        $order = $concert->orderTickets('jane@example.com', 1);
        $ticket = $order->tickets()->first();
        $this->assertEquals($order->id, $ticket->order_id);
        $ticket->release();
        $this->assertNull($ticket->fresh()->order_id);
    }
}
