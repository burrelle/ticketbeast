<?php

use Tests\TestCase;
use App\Billing\StripePaymentGateway;
use App\Billing\PaymentFailedException;

/**
 * @group integration
 */
class StripePaymentGatewayTest extends TestCase
{
    private function lastCharge()
    {
        return array_first(\Stripe\Charge::all(
            ['limit' => 1],
            ['api_key' => config('services.stripe.secret')]
        )['data']);
    }

    private function newCharges()
    {
        return \Stripe\Charge::all(
            [
                'ending_before' => $this->lastCharge ? $this->lastCharge->id : null,
            ],
            ['api_key' => config('services.stripe.secret')]
        )['data'];
    }

    private function validToken()
    {
        return \Stripe\Token::create([
            "card" => [
                "number" => "4242424242424242",
                "exp_month" => 1,
                "exp_year" => date('Y') + 1,
                "cvc" => "123"
            ]
        ], ['api_key' => config('services.stripe.secret')])->id;
    }

    protected function setUp()
    {
        parent::setUp();
        $this->lastCharge = $this->lastCharge();
    }

    public function testChargesWithValidPaymentTokenAreSuccessful()
    {
        $paymentGateway = new StripePaymentGateway(config('services.stripe.secret'));
        $paymentGateway->charge(2500, $this->validToken());
        $this->assertCount(1, $this->newCharges());
        $this->assertEquals(2500, $this->lastCharge()->amount);
    }

    public function testChargesWtihInvalidPaymentTokenFail()
    {
        try {
            $paymentGateway = new StripePaymentGateway(config('services.stripe.secret'));
            $paymentGateway->charge(2500, 'invalid-payment-token');
        } catch (PaymentFailedException $e) {
            $this->assertCount(0, $this->newCharges());
            return;
        }

        $this->fail();
    }
}
