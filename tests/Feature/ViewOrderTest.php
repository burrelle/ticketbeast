<?php

use App\Order;
use App\Ticket;
use App\Concert;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ViewOrderTest extends TestCase
{
    use DatabaseMigrations;

    public function testUserCanViewTheirOrderConfirmation()
    {
        $concert = factory(Concert::class)->create();
        $order = factory(Order::class)->create([
            'confirmation_number' => 'ORDERCONFIRMATION1234'
        ]);
        $ticket = factory(Ticket::class)->create([
            'concert_id' => $concert->id,
            'order_id' => $order->id
        ]);
        $response = $this->get("/orders/ORDERCONFIRMATION1234");
        $response->assertStatus(200);
        $response->assertViewHas('order', function($viewOrder) use ($order){
            return $order->id === $viewOrder->id;
        });
    }
}
