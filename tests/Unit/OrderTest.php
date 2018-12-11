<?php

use App\Order;
use App\Ticket;
use App\Concert;
use Tests\TestCase;
use App\Reservation;
use App\Billing\Charge;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OrderTest extends TestCase
{
    use DatabaseMigrations;

    public function testCreatingAnOrderFromEmailAndTicketsAndAmount()
    {
        $tickets = factory(Ticket::class, 3)->create();
        $charge = new Charge(['amount' => 3600, 'card_last_four' => '1234']);

        $order = Order::forTickets($tickets, 'john@example.com', $charge);

        $this->assertEquals('john@example.com', $order->email);
        $this->assertEquals(3, $order->ticketQuantity());
        $this->assertEquals(3600, $order->amount);
        $this->assertEquals('1234', $order->card_last_four);
    }

    public function testConvertToAnArray()
    {
        $order = factory(Order::class)->create([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'email' => 'jane@example.com',
            'amount' => 6000
        ]);
        $order->tickets()->saveMany(factory(Ticket::class)->times(5)->create());
        $result = $order->toArray();
        $this->assertEquals([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
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
