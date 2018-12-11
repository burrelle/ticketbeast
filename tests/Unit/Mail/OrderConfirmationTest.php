<?php

namespace Tests\Mail;

use App\Order;
use Tests\TestCase;
use App\Mail\OrderConfirmationEmail;

class OrderConfirmationTest extends TestCase
{
    public function testEmailContainsLinkToOrderConfirmationPage()
    {
        $order = factory(Order::class)->make([
            'confirmation_number' => 'ORDERCONFIRMATION123'
        ]);

        $email = new OrderConfirmationEmail($order);
        $rendered = $email->render($email);
        $this->assertContains(url('/orders/ORDERCONFIRMATION123'), $rendered);
    }

    public function testEmailHasSubject()
    {
        $order = factory(Order::class)->make();
        $email = new OrderConfirmationEmail($order);
        $this->assertEquals("Your TicketBeast Order", $email->build()->subject);
    }
}
