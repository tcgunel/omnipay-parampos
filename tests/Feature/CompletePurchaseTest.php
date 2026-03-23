<?php

namespace Omnipay\Parampos\Tests\Feature;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Parampos\Message\VerifyEnrolmentRequest;
use Omnipay\Parampos\Message\VerifyEnrolmentResponse;
use Omnipay\Parampos\Models\GModel;
use Omnipay\Parampos\Models\VerifyEnrolmentRequestModel;
use Omnipay\Parampos\Tests\TestCase;

class CompletePurchaseTest extends TestCase
{
    /**
     * Test that getData builds the correct VerifyEnrolmentRequestModel.
     *
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     * @throws \Omnipay\Common\Exception\InvalidCreditCardException
     * @throws \JsonException
     */
    public function test_complete_purchase_request()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/CompletePurchaseRequest.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $request = new VerifyEnrolmentRequest($this->getHttpClient(), $this->getHttpRequest());

        $request->initialize($options);

        $data = $request->getData();

        $expected = new VerifyEnrolmentRequestModel([
            'G' => new GModel([
                'CLIENT_CODE' => 12345,
                'CLIENT_USERNAME' => 'test@parampos.com',
                'CLIENT_PASSWORD' => 'testPass123',
            ]),
            'GUID' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
            'UCD_MD' => 'mdValue123456789',
            'Islem_GUID' => 'islem-guid-a1b2c3d4',
            'Siparis_ID' => 'PARAM-ORD-20240101001',
        ]);

        self::assertEquals($expected, $data);
    }

    /**
     * Test that getData throws InvalidRequestException when required fields are missing.
     */
    public function test_complete_purchase_request_validation_error()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/CompletePurchaseRequest-ValidationError.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $request = new VerifyEnrolmentRequest($this->getHttpClient(), $this->getHttpRequest());

        $request->initialize($options);

        $this->expectException(InvalidRequestException::class);

        $request->getData();
    }

    /**
     * Test that test mode switches to the test endpoint.
     */
    public function test_complete_purchase_test_mode_endpoint()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/CompletePurchaseRequest.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $request = new VerifyEnrolmentRequest($this->getHttpClient(), $this->getHttpRequest());

        $request->initialize($options);

        $request->getData();

        $this->assertEquals(
            'https://testposws.param.com.tr/turkpos.ws/service_turkpos_prod.asmx?wsdl',
            $request->getEndpoint()
        );
    }

    /**
     * Test VerifyEnrolmentResponse with successful payment.
     * Mocks the SOAP response object with TP_WMD_PayResult.
     */
    public function test_complete_purchase_response_success()
    {
        $soapResult = new \stdClass();
        $soapResult->TP_WMD_PayResult = new \stdClass();
        $soapResult->TP_WMD_PayResult->Sonuc = '1';
        $soapResult->TP_WMD_PayResult->Sonuc_Ack = 'Odeme basarili';
        $soapResult->TP_WMD_PayResult->Dekont_ID = '987654';
        $soapResult->TP_WMD_PayResult->Siparis_ID = 'PARAM-ORD-20240101001';
        $soapResult->TP_WMD_PayResult->UCD_MD = 'mdValue123456789';
        $soapResult->TP_WMD_PayResult->Bank_Trans_ID = 'trans-id-001';
        $soapResult->TP_WMD_PayResult->Bank_AuthCode = 'AUTH001';
        $soapResult->TP_WMD_PayResult->Bank_HostMsg = 'Onaylandi';
        $soapResult->TP_WMD_PayResult->Bank_Extra = '';
        $soapResult->TP_WMD_PayResult->Bank_Sonuc_Kod = 0;
        $soapResult->TP_WMD_PayResult->Bank_HostRefNum = 'REF001';

        $mockRequest = $this->getMockRequest();

        $response = new VerifyEnrolmentResponse($mockRequest, $soapResult);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('Odeme basarili', $response->getMessage());
    }

    /**
     * Test VerifyEnrolmentResponse with failed payment (Sonuc <= 0).
     */
    public function test_complete_purchase_response_error_sonuc()
    {
        $soapResult = new \stdClass();
        $soapResult->TP_WMD_PayResult = new \stdClass();
        $soapResult->TP_WMD_PayResult->Sonuc = '-1';
        $soapResult->TP_WMD_PayResult->Sonuc_Ack = 'MD dogrulama hatasi';
        $soapResult->TP_WMD_PayResult->Dekont_ID = '0';
        $soapResult->TP_WMD_PayResult->Siparis_ID = 'PARAM-ORD-20240101001';
        $soapResult->TP_WMD_PayResult->UCD_MD = '';
        $soapResult->TP_WMD_PayResult->Bank_Trans_ID = '';
        $soapResult->TP_WMD_PayResult->Bank_AuthCode = '';
        $soapResult->TP_WMD_PayResult->Bank_HostMsg = '';
        $soapResult->TP_WMD_PayResult->Bank_Extra = '';
        $soapResult->TP_WMD_PayResult->Bank_Sonuc_Kod = 99;
        $soapResult->TP_WMD_PayResult->Bank_HostRefNum = '';

        $mockRequest = $this->getMockRequest();

        $response = new VerifyEnrolmentResponse($mockRequest, $soapResult);

        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('MD dogrulama hatasi', $response->getMessage());
    }

    /**
     * Test VerifyEnrolmentResponse with Dekont_ID = 0 (payment incomplete).
     */
    public function test_complete_purchase_response_no_dekont()
    {
        $soapResult = new \stdClass();
        $soapResult->TP_WMD_PayResult = new \stdClass();
        $soapResult->TP_WMD_PayResult->Sonuc = '1';
        $soapResult->TP_WMD_PayResult->Sonuc_Ack = 'Islem basarisiz';
        $soapResult->TP_WMD_PayResult->Dekont_ID = '0';
        $soapResult->TP_WMD_PayResult->Siparis_ID = 'PARAM-ORD-20240101001';
        $soapResult->TP_WMD_PayResult->UCD_MD = '';
        $soapResult->TP_WMD_PayResult->Bank_Trans_ID = '';
        $soapResult->TP_WMD_PayResult->Bank_AuthCode = '';
        $soapResult->TP_WMD_PayResult->Bank_HostMsg = '';
        $soapResult->TP_WMD_PayResult->Bank_Extra = '';
        $soapResult->TP_WMD_PayResult->Bank_Sonuc_Kod = 0;
        $soapResult->TP_WMD_PayResult->Bank_HostRefNum = '';

        $mockRequest = $this->getMockRequest();

        $response = new VerifyEnrolmentResponse($mockRequest, $soapResult);

        // Sonuc > 0 but Dekont_ID is not > 0, so should be false
        $this->assertFalse($response->isSuccessful());
    }

    /**
     * Test that the gateway completePurchase method returns a VerifyEnrolmentRequest.
     */
    public function test_gateway_complete_purchase_creates_request()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/CompletePurchaseRequest.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $request = $this->gateway->completePurchase($options);

        $this->assertInstanceOf(VerifyEnrolmentRequest::class, $request);
    }
}
