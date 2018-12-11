<?php

namespace App\Billing;

use App\Billing\Charge;
use Stripe\Error\InvalidRequest;
use App\Billing\PaymentFailedException;

class StripePaymentGateway implements PaymentGateway
{
    const TEST_CARD_NUMBER = "4242424242424242";

    protected $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function charge($amount, $token)
    {
        try {
            $stripeCharge = \Stripe\Charge::create([
                "amount" => $amount,
                "currency" => "usd",
                "source" => $token,
            ], ['api_key' => $this->apiKey ]);
            return new Charge([
                'card_last_four' => $stripeCharge['source']['last4'],
                'amount' => $stripeCharge['amount']
            ]);
        } catch (InvalidRequest $e) {
            throw new PaymentFailedException;
        }
    }

    public function getValidTestToken($cardNumber = self::TEST_CARD_NUMBER)
    {
        return \Stripe\Token::create([
            "card" => [
                "number" => $cardNumber,
                "exp_month" => 1,
                "exp_year" => date('Y') + 1,
                "cvc" => "123"
            ]
        ], ['api_key' => $this->apiKey])->id;
    }

    private function lastCharge()
    {
        return array_first(\Stripe\Charge::all(
            ['limit' => 1],
            ['api_key' => $this->apiKey]
        )['data']);
    }

    private function newChargesSince($charge = null)
    {
        $newCharges = \Stripe\Charge::all(
            [
                'ending_before' => $charge ? $charge->id : null,
            ],
            ['api_key' => $this->apiKey]
        )['data'];
        return collect($newCharges);
    }

    public function newChargesDuring($callback)
    {
        $latestCharge = $this->lastCharge();
        $callback($this);

        return $this->newChargesSince($latestCharge)->map(function ($stripeCharge){
            return new Charge([
                'card_last_four' => $stripeCharge['source']['last4'],
                'amount' => $stripeCharge['amount']
            ]);
        });
    }
}
