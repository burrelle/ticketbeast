<?php

use App\Order;
use App\Ticket;
use App\Concert;
use Tests\TestCase;
use App\Reservation;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OrderTest extends TestCase
{
    use DatabaseMigrations;

    public function testCreatingAnOrderFromEmailAndTicketsAndAmount()
    {
        $concert = factory(Concert::class)->create()->addTickets(5);
        $order = Order::forTickets($concert->findTickets(3), 'john@example.com', 3600);
        $this->assertEquals('john@example.com', $order->email);
        $this->assertEquals(3, $order->ticketQuantity());
        $this->assertEquals(3600, $order->amount);
        $this->assertEquals(2, $concert->ticketsRemaining());
    }

    public function testConvertToAnArray()
    {
        $concert = factory(Concert::class)->create(['ticket_price' => 1200])->addTickets(5);
        $order = $concert->orderTickets('jane@example.com', 5);
        $result = $order->toArray();
        $this->assertEquals([
            'email' => 'jane@example.com',
            'ticket_quantity' => 5,
            'amount' => 6000
        ], $result);
    }

    public function testRetrievingOrderByConfirmationNumber()
    {
        $order = factory(Order::class)->create([
            'confirmation_number' => 'ORDERCONFIRMATION1234'
        ]);
        $foundOrder = Order::findByConfirmationNumber('ORDERCONFIRMATION1234');
        $this->assertEquals($order->id, $foundOrder->id);
    }

    public function testRetrievingNonexistentOrderByConfirmationThrowsException()
    {
        try {
            Order::findByConfirmationNumber('NOTFOUNDNUMBER');
        } catch (ModelNotFoundException $e) {
            $this->assertNotNull($e);
            return;
        }

        $this->fail('No matching order was found. No exception thrown.');
    }
}
