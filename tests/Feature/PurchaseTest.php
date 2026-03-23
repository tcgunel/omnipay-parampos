<?php

namespace Omnipay\Parampos\Tests\Feature;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Parampos\Constants\PaymentType;
use Omnipay\Parampos\Message\PurchaseRequest;
use Omnipay\Parampos\Message\PurchaseResponse;
use Omnipay\Parampos\Models\GModel;
use Omnipay\Parampos\Models\PurchaseRequestModel;
use Omnipay\Parampos\Tests\TestCase;

class PurchaseTest extends TestCase
{
    /**
     * Test that getData builds the correct PurchaseRequestModel.
     *
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     * @throws \Omnipay\Common\Exception\InvalidCreditCardException
     * @throws \JsonException
     */
    public function test_purchase_request()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/PurchaseRequest.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

        $request->initialize($options);

        $data = $request->getData();

        $this->assertInstanceOf(PurchaseRequestModel::class, $data);

        // Check GModel
        $this->assertInstanceOf(GModel::class, $data->G);
        $this->assertEquals(12345, $data->G->CLIENT_CODE);
        $this->assertEquals('test@parampos.com', $data->G->CLIENT_USERNAME);
        $this->assertEquals('testPass123', $data->G->CLIENT_PASSWORD);

        // Check GUID (truncated to 36 chars)
        $this->assertEquals('a1b2c3d4-e5f6-7890-abcd-ef1234567890', $data->GUID);

        // Check card details
        $this->assertEquals('Mehmet Demir', $data->KK_Sahibi);
        $this->assertEquals('5456165456165454', $data->KK_No);
        $this->assertEquals(3, $data->KK_SK_Ay); // last 2 chars of "03" cast to int
        $this->assertEquals('30', $data->KK_SK_Yil); // last 2 chars of "2030"
        $this->assertEquals('456', $data->KK_CVC);
        $this->assertEquals('5551234567', $data->KK_Sahibi_GSM); // last 10 digits

        // Check URLs
        $this->assertEquals('https://example.com/payment/fail', $data->Hata_URL);
        $this->assertEquals('https://example.com/payment/success', $data->Basarili_URL);

        // Check order details
        $this->assertEquals('PARAM-ORD-20240101001', $data->Siparis_ID);
        $this->assertEquals(1, $data->Taksit);

        // Check amount format (comma as decimal separator)
        $this->assertEquals('250,50', $data->Islem_Tutar);
        $this->assertEquals('250,50', $data->Toplam_Tutar);

        // Check security type (3D for secure)
        $this->assertEquals(PaymentType::SECURE, $data->Islem_Guvenlik_Tip);

        // Check IP
        $this->assertEquals('192.168.1.100', $data->IPAdr);
    }

    /**
     * Test that non-secure purchase sets the correct payment type.
     */
    public function test_purchase_request_non_secure()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/PurchaseRequest.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $options['secure'] = false;

        $request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

        $request->initialize($options);

        $data = $request->getData();

        $this->assertEquals(PaymentType::NON_SECURE, $data->Islem_Guvenlik_Tip);
    }

    /**
     * Test that getData throws InvalidRequestException when required fields are missing.
     */
    public function test_purchase_request_validation_error()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/PurchaseRequest-ValidationError.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

        $request->initialize($options);

        $this->expectException(InvalidRequestException::class);

        $request->getData();
    }

    /**
     * Test that test mode switches to the test endpoint.
     */
    public function test_purchase_request_test_mode_endpoint()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/PurchaseRequest.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

        $request->initialize($options);

        $request->getData();

        $this->assertEquals(
            'https://testposws.param.com.tr/turkpos.ws/service_turkpos_prod.asmx?wsdl',
            $request->getEndpoint()
        );
    }

    /**
     * Test PurchaseResponse with successful 3D redirect scenario.
     * Mocks the SOAP response object with TP_WMD_UCDResult.
     */
    public function test_purchase_response_redirect()
    {
        $soapResult = new \stdClass();
        $soapResult->TP_WMD_UCDResult = new \stdClass();
        $soapResult->TP_WMD_UCDResult->Islem_ID = '0';
        $soapResult->TP_WMD_UCDResult->Islem_GUID = 'islem-guid-abc123';
        $soapResult->TP_WMD_UCDResult->UCD_HTML = '<html><body><form>3D Redirect Form</form></body></html>';
        $soapResult->TP_WMD_UCDResult->UCD_MD = 'md-value-123';
        $soapResult->TP_WMD_UCDResult->Sonuc = '1';
        $soapResult->TP_WMD_UCDResult->Sonuc_Str = 'Islem basarili';
        $soapResult->TP_WMD_UCDResult->Banka_Sonuc_Kod = 0;
        $soapResult->TP_WMD_UCDResult->Siparis_ID = 'PARAM-ORD-20240101001';
        $soapResult->TP_WMD_UCDResult->Bank_Trans_ID = null;
        $soapResult->TP_WMD_UCDResult->Bank_AuthCode = null;
        $soapResult->TP_WMD_UCDResult->Bank_HostMsg = null;
        $soapResult->TP_WMD_UCDResult->Bank_Extra = null;
        $soapResult->TP_WMD_UCDResult->Bank_HostRefNum = null;

        $mockRequest = $this->getMockRequest();

        $response = new PurchaseResponse($mockRequest, $soapResult);

        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertEquals('Islem basarili', $response->getMessage());
    }

    /**
     * Test PurchaseResponse with successful NON-SECURE payment (no redirect).
     */
    public function test_purchase_response_non_secure_success()
    {
        $soapResult = new \stdClass();
        $soapResult->TP_WMD_UCDResult = new \stdClass();
        $soapResult->TP_WMD_UCDResult->Islem_ID = '123456';
        $soapResult->TP_WMD_UCDResult->Islem_GUID = 'islem-guid-abc123';
        $soapResult->TP_WMD_UCDResult->UCD_HTML = 'NONSECURE';
        $soapResult->TP_WMD_UCDResult->UCD_MD = null;
        $soapResult->TP_WMD_UCDResult->Sonuc = '1';
        $soapResult->TP_WMD_UCDResult->Sonuc_Str = 'Islem basarili';
        $soapResult->TP_WMD_UCDResult->Banka_Sonuc_Kod = 0;
        $soapResult->TP_WMD_UCDResult->Siparis_ID = 'PARAM-ORD-20240101001';
        $soapResult->TP_WMD_UCDResult->Bank_Trans_ID = 'trans-id-001';
        $soapResult->TP_WMD_UCDResult->Bank_AuthCode = 'AUTH001';
        $soapResult->TP_WMD_UCDResult->Bank_HostMsg = 'Onaylandi';
        $soapResult->TP_WMD_UCDResult->Bank_Extra = null;
        $soapResult->TP_WMD_UCDResult->Bank_HostRefNum = 'REF001';

        $mockRequest = $this->getMockRequest();

        $response = new PurchaseResponse($mockRequest, $soapResult);

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertEquals('Islem basarili', $response->getMessage());
    }

    /**
     * Test PurchaseResponse with API error.
     */
    public function test_purchase_response_error()
    {
        $soapResult = new \stdClass();
        $soapResult->TP_WMD_UCDResult = new \stdClass();
        $soapResult->TP_WMD_UCDResult->Islem_ID = '0';
        $soapResult->TP_WMD_UCDResult->Islem_GUID = null;
        $soapResult->TP_WMD_UCDResult->UCD_HTML = '';
        $soapResult->TP_WMD_UCDResult->UCD_MD = null;
        $soapResult->TP_WMD_UCDResult->Sonuc = '-1';
        $soapResult->TP_WMD_UCDResult->Sonuc_Str = 'Kart numarasi gecersiz';
        $soapResult->TP_WMD_UCDResult->Banka_Sonuc_Kod = 99;
        $soapResult->TP_WMD_UCDResult->Siparis_ID = 'PARAM-ORD-20240101001';
        $soapResult->TP_WMD_UCDResult->Bank_Trans_ID = null;
        $soapResult->TP_WMD_UCDResult->Bank_AuthCode = null;
        $soapResult->TP_WMD_UCDResult->Bank_HostMsg = null;
        $soapResult->TP_WMD_UCDResult->Bank_Extra = null;
        $soapResult->TP_WMD_UCDResult->Bank_HostRefNum = null;

        $mockRequest = $this->getMockRequest();

        $response = new PurchaseResponse($mockRequest, $soapResult);

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertEquals('Kart numarasi gecersiz', $response->getMessage());
    }

    /**
     * Test that hash_object builds the correct HashModel for SOAP SHA2B64 call.
     */
    public function test_purchase_request_hash_object()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/PurchaseRequest.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

        $request->initialize($options);

        $data = $request->getData();

        // Use reflection to call the protected hash_object method
        $reflection = new \ReflectionMethod(PurchaseRequest::class, 'hash_object');
        $reflection->setAccessible(true);

        $hashModel = $reflection->invoke($request, $data);

        $expectedData = $data->G->CLIENT_CODE .
            $data->GUID .
            $data->Taksit .
            $data->Islem_Tutar .
            $data->Toplam_Tutar .
            $data->Siparis_ID;

        $this->assertEquals($expectedData, $hashModel->Data);
        $this->assertSame($data->G, $hashModel->G);
    }

    /**
     * Test amount formatting uses comma as decimal separator (Turkish locale).
     */
    public function test_purchase_request_amount_formatting()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/PurchaseRequest.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $options['amount'] = 1000.00;

        $request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

        $request->initialize($options);

        $data = $request->getData();

        $this->assertEquals('1000,00', $data->Islem_Tutar);
        $this->assertEquals('1000,00', $data->Toplam_Tutar);
    }

    /**
     * Test that phone number is truncated to last 10 digits.
     */
    public function test_purchase_request_phone_truncation()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/PurchaseRequest.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        // Phone with country prefix
        $options['card']['phone'] = '+905551234567';

        $request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

        $request->initialize($options);

        $data = $request->getData();

        $this->assertEquals('5551234567', $data->KK_Sahibi_GSM);
    }
}
