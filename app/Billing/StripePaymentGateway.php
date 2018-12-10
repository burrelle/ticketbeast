<?php

namespace App\Billing;

use Stripe\Charge;

class StripePaymentGateway implements PaymentGateway
{
    protected $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function charge($amount, $token)
    {
        \Stripe\Charge::create([
            "amount" => $amount,
            "currency" => "usd",
            "source" => $token,
        ], ['api_key' => $this->apiKey ])->id;
    }
}
