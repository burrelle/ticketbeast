<?php

use App\Ticket;
use App\Concert;
use Tests\TestCase;
use App\Reservation;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Billing\FakePaymentGateway;

class ReservationTest extends TestCase
{
    use DatabaseMigrations;

    public function testCalculatingTheTotalCost()
    {
        $tickets = collect([
            (object) ['price' => 1200],
            (object) ['price' => 1200],
            (object) ['price' => 1200]
        ]);
        $reservation = new Reservation($tickets, 'john@example.com');
        $this->assertEquals(3600, $reservation->totalCost());
    }

    public function testRetreieveTheReservationTickets()
    {
        $tickets = collect([
            (object) ['price' => 1200],
            (object) ['price' => 1200],
            (object) ['price' => 1200]
        ]);
        $reservation = new Reservation($tickets, 'john@example.com');
        $this->assertEquals($tickets, $reservation->tickets());
    }

    public function testRetreieveTheReservationEmail()
    {
        $reservation = new Reservation(collect(), 'john@example.com');
        $this->assertEquals('john@example.com', $reservation->email());
    }

    public function testReservedTicketsAreReleasedIsCancelled()
    {
        $tickets = collect([
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class)
        ]);

        $reservation = new Reservation($tickets, 'john@example.com');
        $reservation->cancel();

        foreach ($tickets as $ticket) {
            $ticket->shouldHaveReceived('release');
        }
    }

    public function testCompletingReservation()
    {
        $concert = factory(Concert::class)->create(['ticket_price' => 1200]);
        $tickets = factory(Ticket::class, 3)->create(['concert_id' => $concert->id]);
        $reservation = new Reservation($tickets, 'john@example.com');
        $paymentGateway = new FakePaymentGateway;
        $order = $reservation->complete($paymentGateway, $paymentGateway->getValidTestToken());
        $this->assertEquals('john@example.com', $order->email);
        $this->assertEquals(3, $order->ticketQuantity());
        $this->assertEquals(3600, $order->amount);
        $this->assertEquals(3600, $paymentGateway->totalCharges());
    }
}
