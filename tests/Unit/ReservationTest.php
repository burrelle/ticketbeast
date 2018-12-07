<?php

use Tests\TestCase;
use App\Reservation;

class ReservationTest extends TestCase
{
    public function testCalculatingTheTotalCost()
    {
        $tickets = collect([
            (object) ['price' => 1200],
            (object) ['price' => 1200],
            (object) ['price' => 1200]
        ]);
        $reservation = new Reservation($tickets);
        $this->assertEquals(3600, $reservation->totalCost());
    }

    public function testReservedTicketsAreReleasedIsCancelled()
    {
        $tickets = collect([
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class)
        ]);

        $reservation = new Reservation($tickets);
        $reservation->cancel();

        foreach ($tickets as $ticket) {
            $ticket->shouldHaveReceived('release');
        }
    }
}
