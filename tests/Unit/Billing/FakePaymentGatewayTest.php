<?php

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;
use Tests\TestCase;

class FakePaymentGatewayTest extends TestCase
{
    public function testChargesWithValidPaymentTokenAreSuccessful()
    {
        $paymentGateway = new FakePaymentGateway;
        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
        $this->assertEquals(2500, $paymentGateway->totalCharges());
    }

    public function testChargesWtihInvalidPaymentTokenFail()
    {
        try {
            $paymentGateway = new FakePaymentGateway;
            $paymentGateway->charge(2500, 'invalid-payment-token');
        } catch (PaymentFailedException $e) {
            $this->assertNotNull($e);
            return;
        }

        $this->fail();
    }

    public function testRunningAHookBeforeTheFirstCharge()
    {
        $paymentGateway = new FakePaymentGateway;
        $timesCallbackRan = 0;
        $paymentGateway->beforeFirstCharge(function($paymentGateway) use (&$timesCallbackRan) {
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
            $timesCallbackRan++;
            $this->assertEquals(2500, $paymentGateway->totalCharges());
        });

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
        $this->assertEquals(1, $timesCallbackRan);
        $this->assertEquals(5000, $paymentGateway->totalCharges());
    }
}
