<?php

namespace Tests\Unit\Billing;

use App\Billing\PaymentFailedException;

trait PaymentGatewayContractTests
{
    abstract protected function getPaymentGateway();

    public function testChargesWithValidPaymentTokenAreSuccessful()
    {
        $paymentGateway = $this->getPaymentGateway();
        $newCharges = $paymentGateway->newChargesDuring(function($paymentGateway) {
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken(), 'test_acct_1234');
        });
        $this->assertCount(1, $newCharges);
        $this->assertEquals(2500, $newCharges->map->amount()->sum());
    }

    public function testCanFetchChargesDuringCallback()
    {
        $paymentGateway = $this->getPaymentGateway();
        $paymentGateway->charge(2000, $paymentGateway->getValidTestToken(), 'test_acct_1234');
        $paymentGateway->charge(3000, $paymentGateway->getValidTestToken(), 'test_acct_1234');
        $newCharges = $paymentGateway->newChargesDuring(function($paymentGateway){
            $paymentGateway->charge(4000, $paymentGateway->getValidTestToken(), 'test_acct_1234');
            $paymentGateway->charge(5000, $paymentGateway->getValidTestToken(), 'test_acct_1234');
        });
        $this->assertCount(2, $newCharges);
        $this->assertEquals([5000, 4000], $newCharges->map->amount()->all());
    }


    public function testChargesWithInvalidTokenFail()
    {
        $paymentGateway = $this->getPaymentGateway();
        $newCharges = $paymentGateway->newChargesDuring(function ($paymentGateway) {
            try {
                $paymentGateway->charge(2500, 'invalid-payment-token', 'test_acct_1234');
            } catch (PaymentFailedException $e) {
                return;
            }
            $this->fail("Charging with an invalid payment token did not throw a PaymentFailedException.");
        });
        $this->assertCount(0, $newCharges);
    }

    public function testCanGetDetailsAboutSuccessfulCharge()
    {
        $paymentGateway = $this->getPaymentGateway();
        $charge = $paymentGateway->charge(2500, $paymentGateway->getValidTestToken($paymentGateway::TEST_CARD_NUMBER), 'test_acct_1234');
        $this->assertEquals(substr($paymentGateway::TEST_CARD_NUMBER, -4), $charge->cardLastFour());
        $this->assertEquals(2500, $charge->amount());
        $this->assertEquals('test_acct_1234', $charge->destination());
    }
}
