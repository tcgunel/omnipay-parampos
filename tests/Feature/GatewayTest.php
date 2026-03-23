<?php

namespace Omnipay\Parampos\Tests\Feature;

use Omnipay\Parampos\Message\BinLookupRequest;
use Omnipay\Parampos\Message\PaymentInquiryRequest;
use Omnipay\Parampos\Message\PurchaseRequest;
use Omnipay\Parampos\Message\VerifyEnrolmentRequest;
use Omnipay\Parampos\Tests\TestCase;

class GatewayTest extends TestCase
{
    public function test_gateway_name()
    {
        $this->assertEquals('Parampos', $this->gateway->getName());
    }

    public function test_gateway_default_parameters()
    {
        $defaults = $this->gateway->getDefaultParameters();

        $this->assertArrayHasKey('installment', $defaults);
        $this->assertArrayHasKey('secure', $defaults);

        $this->assertEquals('1', $defaults['installment']);
        $this->assertTrue($defaults['secure']);
    }

    public function test_gateway_purchase_returns_purchase_request()
    {
        $request = $this->gateway->purchase([]);

        $this->assertInstanceOf(PurchaseRequest::class, $request);
    }

    public function test_gateway_complete_purchase_returns_verify_enrolment_request()
    {
        $request = $this->gateway->completePurchase([]);

        $this->assertInstanceOf(VerifyEnrolmentRequest::class, $request);
    }

    public function test_gateway_payment_inquiry_returns_payment_inquiry_request()
    {
        $request = $this->gateway->paymentInquiry([]);

        $this->assertInstanceOf(PaymentInquiryRequest::class, $request);
    }

    public function test_gateway_bin_lookup_returns_bin_lookup_request()
    {
        $request = $this->gateway->binLookup([]);

        $this->assertInstanceOf(BinLookupRequest::class, $request);
    }

    public function test_gateway_getters_and_setters()
    {
        $this->gateway->setClientUsername('test-username');
        $this->assertEquals('test-username', $this->gateway->getClientUsername());

        $this->gateway->setClientPassword('test-password');
        $this->assertEquals('test-password', $this->gateway->getClientPassword());

        $this->gateway->setClientCode('99999');
        $this->assertEquals('99999', $this->gateway->getClientCode());

        $this->gateway->setGuid('test-guid-12345');
        $this->assertEquals('test-guid-12345', $this->gateway->getGuid());

        $this->gateway->setSecure(false);
        $this->assertFalse($this->gateway->getSecure());

        $this->gateway->setInstallment(6);
        $this->assertEquals(6, $this->gateway->getInstallment());
    }
}
