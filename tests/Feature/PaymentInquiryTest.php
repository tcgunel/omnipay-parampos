<?php

namespace Omnipay\Parampos\Tests\Feature;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Parampos\Message\PaymentInquiryRequest;
use Omnipay\Parampos\Message\PaymentInquiryResponse;
use Omnipay\Parampos\Models\GModel;
use Omnipay\Parampos\Models\VerifyEnrolmentRequestModel;
use Omnipay\Parampos\Tests\TestCase;

class PaymentInquiryTest extends TestCase
{
	/**
	 * Test that getData builds the correct VerifyEnrolmentRequestModel for inquiry.
	 * Note: PaymentInquiryRequest reuses VerifyEnrolmentRequestModel.
	 *
	 * @throws \Omnipay\Common\Exception\InvalidRequestException
	 * @throws \JsonException
	 */
	public function test_payment_inquiry_request()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PaymentInquiryRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new PaymentInquiryRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$data = $request->getData();

		$this->assertInstanceOf(VerifyEnrolmentRequestModel::class, $data);

		// Check GModel
		$this->assertInstanceOf(GModel::class, $data->G);
		$this->assertEquals(12345, $data->G->CLIENT_CODE);
		$this->assertEquals('test@parampos.com', $data->G->CLIENT_USERNAME);
		$this->assertEquals('testPass123', $data->G->CLIENT_PASSWORD);

		$this->assertEquals('a1b2c3d4-e5f6-7890-abcd-ef1234567890', $data->GUID);
		$this->assertEquals('PARAM-ORD-20240101001', $data->Siparis_ID);
	}

	/**
	 * Test that getData throws InvalidRequestException when transactionId is missing.
	 */
	public function test_payment_inquiry_request_validation_error()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PaymentInquiryRequest-ValidationError.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new PaymentInquiryRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$this->expectException(InvalidRequestException::class);

		$request->getData();
	}

	/**
	 * Test that test mode switches to the test endpoint.
	 */
	public function test_payment_inquiry_test_mode_endpoint()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PaymentInquiryRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new PaymentInquiryRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$request->getData();

		$this->assertEquals(
			'https://testposws.param.com.tr/turkpos.ws/service_turkpos_prod.asmx?wsdl',
			$request->getEndpoint()
		);
	}

	/**
	 * Test PaymentInquiryResponse with successful payment.
	 * Mocks the SOAP response object with TP_Islem_Sorgulama4Result.
	 */
	public function test_payment_inquiry_response_success()
	{
		$dtBilgi = new \stdClass();
		$dtBilgi->Odeme_Sonuc = 1;
		$dtBilgi->Odeme_Tutar = '250,50';
		$dtBilgi->Banka_Sonuc_Kod = 0;

		$soapResult = new \stdClass();
		$soapResult->TP_Islem_Sorgulama4Result = new \stdClass();
		$soapResult->TP_Islem_Sorgulama4Result->Sonuc = '1';
		$soapResult->TP_Islem_Sorgulama4Result->Sonuc_Str = 'Islem basarili';
		$soapResult->TP_Islem_Sorgulama4Result->DT_Bilgi = $dtBilgi;

		$mockRequest = $this->getMockRequest();

		$response = new PaymentInquiryResponse($mockRequest, $soapResult);

		$this->assertTrue($response->isSuccessful());
		$this->assertEquals('Islem basarili', $response->getMessage());
	}

	/**
	 * Test PaymentInquiryResponse with error (Sonuc <= 0).
	 */
	public function test_payment_inquiry_response_error_sonuc()
	{
		$dtBilgi = new \stdClass();
		$dtBilgi->Odeme_Sonuc = 0;

		$soapResult = new \stdClass();
		$soapResult->TP_Islem_Sorgulama4Result = new \stdClass();
		$soapResult->TP_Islem_Sorgulama4Result->Sonuc = '-1';
		$soapResult->TP_Islem_Sorgulama4Result->Sonuc_Str = 'Siparis bulunamadi';
		$soapResult->TP_Islem_Sorgulama4Result->DT_Bilgi = $dtBilgi;

		$mockRequest = $this->getMockRequest();

		$response = new PaymentInquiryResponse($mockRequest, $soapResult);

		$this->assertFalse($response->isSuccessful());
		$this->assertEquals('Siparis bulunamadi', $response->getMessage());
	}

	/**
	 * Test PaymentInquiryResponse with Sonuc > 0 but Odeme_Sonuc != 1 (payment not completed).
	 */
	public function test_payment_inquiry_response_payment_not_completed()
	{
		$dtBilgi = new \stdClass();
		$dtBilgi->Odeme_Sonuc = 0;

		$soapResult = new \stdClass();
		$soapResult->TP_Islem_Sorgulama4Result = new \stdClass();
		$soapResult->TP_Islem_Sorgulama4Result->Sonuc = '1';
		$soapResult->TP_Islem_Sorgulama4Result->Sonuc_Str = 'Islem beklemede';
		$soapResult->TP_Islem_Sorgulama4Result->DT_Bilgi = $dtBilgi;

		$mockRequest = $this->getMockRequest();

		$response = new PaymentInquiryResponse($mockRequest, $soapResult);

		$this->assertFalse($response->isSuccessful());
	}
}
