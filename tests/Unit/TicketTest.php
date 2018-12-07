<?php

use App\Ticket;
use App\Concert;
use Carbon\Carbon;
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
        $ticket = factory(Ticket::class)->states('reserved')->create();
        $this->assertNotNull($ticket->reserved_at);
        $ticket->release();
        $this->assertNull($ticket->fresh()->reserved_at);
    }
}
