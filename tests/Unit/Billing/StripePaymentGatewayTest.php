<?php

use Tests\TestCase;

class StripePaymentGatewayTest extends TestCase
{
    public function testChargesWithValidPaymentTokenAreSuccessful()
    {
        $paymentGateway = new StripePaymentGateway;

        $token = \Stripe\Charge::create([
            "amount" => 2000,
            "currency" => "usd",
            "source" => "tok_visa",
            "description" => "Charge for example@example.com"
        ], ['api_key' => config('services.stripe.secret')])->id;

        $paymentGateway->charge(2500, $token);
    }
}
