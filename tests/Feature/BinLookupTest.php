<?php

namespace Omnipay\Parampos\Tests\Feature;

use Omnipay\Common\Exception\InvalidCreditCardException;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Parampos\Message\BinLookupRequest;
use Omnipay\Parampos\Message\BinLookupResponse;
use Omnipay\Parampos\Models\BinLookupRequestModel;
use Omnipay\Parampos\Models\BinLookupResponseModel;
use Omnipay\Parampos\Models\GModel;
use Omnipay\Parampos\Tests\TestCase;

class BinLookupTest extends TestCase
{
	/**
	 * Test that getData builds the correct BinLookupRequestModel.
	 *
	 * @throws \Omnipay\Common\Exception\InvalidRequestException
	 * @throws InvalidCreditCardException
	 * @throws \JsonException
	 */
	public function test_bin_lookup_request()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/BinLookupRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new BinLookupRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$data = $request->getData();

		$this->assertInstanceOf(BinLookupRequestModel::class, $data);

		// Check GModel
		$this->assertInstanceOf(GModel::class, $data->G);
		$this->assertEquals(12345, $data->G->CLIENT_CODE);
		$this->assertEquals('test@parampos.com', $data->G->CLIENT_USERNAME);
		$this->assertEquals('testPass123', $data->G->CLIENT_PASSWORD);

		// Check BIN (first 6 digits of card number)
		$this->assertEquals('545616', $data->BIN);
	}

	/**
	 * Test that the BIN is correctly truncated to 6 digits via mb_substr.
	 */
	public function test_bin_lookup_request_bin_truncation()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/BinLookupRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new BinLookupRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$data = $request->getData();

		// Card number 5456165456165454 => BIN should be first 6 digits: 545616
		$this->assertEquals('545616', $data->BIN);
		$this->assertEquals(6, strlen($data->BIN));
	}

	/**
	 * Test that getData throws InvalidCreditCardException for short card number.
	 */
	public function test_bin_lookup_request_validation_error()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/BinLookupRequest-ValidationError.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new BinLookupRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$this->expectException(InvalidCreditCardException::class);

		$request->getData();
	}

	/**
	 * Test that test mode switches to the test endpoint.
	 */
	public function test_bin_lookup_test_mode_endpoint()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/BinLookupRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new BinLookupRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$request->getData();

		$this->assertEquals(
			'https://testposws.param.com.tr/turkpos.ws/service_turkpos_prod.asmx?wsdl',
			$request->getEndpoint()
		);
	}

	/**
	 * Test BinLookupResponse with successful result containing BIN data.
	 * Mocks the SOAP response object with BIN_SanalPosResult.
	 */
	public function test_bin_lookup_response_success()
	{
		$binData = '<NewDataSet><Temp><BIN>545616</BIN><BinBaslangic>5456160000000000</BinBaslangic>'
			. '<BinBitis>5456169999999999</BinBitis><BinUzunluk>6</BinUzunluk>'
			. '<SanalPOS_ID>1</SanalPOS_ID><Kart_Banka>QNB Finansbank</Kart_Banka>'
			. '<DKK>TRY</DKK><Kart_Tip>Credit Card</Kart_Tip><Kart_Org>Mastercard</Kart_Org>'
			. '<Banka_Kodu>111</Banka_Kodu><Kart_Ticari>0</Kart_Ticari>'
			. '<Kart_Marka>CardFinans</Kart_Marka></Temp></NewDataSet>';

		$dtBilgi = new \stdClass();
		$dtBilgi->any = $binData;

		$soapResult = new \stdClass();
		$soapResult->BIN_SanalPosResult = new \stdClass();
		$soapResult->BIN_SanalPosResult->Sonuc = '1';
		$soapResult->BIN_SanalPosResult->Sonuc_Str = 'Islem basarili';
		$soapResult->BIN_SanalPosResult->DT_Bilgi = $dtBilgi;

		// Mock the request so getData() returns a model with BIN
		$mockRequest = $this->getMockBuilder(BinLookupRequest::class)
			->disableOriginalConstructor()
			->onlyMethods(['getData'])
			->getMock();

		$binModel = new BinLookupRequestModel([
			'G' => new GModel([
				'CLIENT_CODE' => 12345,
				'CLIENT_USERNAME' => 'test@parampos.com',
				'CLIENT_PASSWORD' => 'testPass123',
			]),
			'BIN' => '545616',
		]);

		$mockRequest->method('getData')->willReturn($binModel);

		$response = new BinLookupResponse($mockRequest, $soapResult);

		$this->assertTrue($response->isSuccessful());
		$this->assertEquals('Islem basarili', $response->getMessage());

		$data = $response->getData();

		$this->assertInstanceOf(BinLookupResponseModel::class, $data);
		$this->assertEquals('545616', $data->BIN);
		$this->assertEquals('QNB Finansbank', $data->Kart_Banka);
		$this->assertEquals('Credit Card', $data->Kart_Tip);
		$this->assertEquals('Mastercard', $data->Kart_Org);
		$this->assertEquals('111', $data->Banka_Kodu);
		$this->assertEquals('CardFinans', $data->Kart_Marka);
	}

	/**
	 * Test BinLookupResponse with error (Sonuc <= 0).
	 */
	public function test_bin_lookup_response_error()
	{
		$soapResult = new \stdClass();
		$soapResult->BIN_SanalPosResult = new \stdClass();
		$soapResult->BIN_SanalPosResult->Sonuc = '-1';
		$soapResult->BIN_SanalPosResult->Sonuc_Str = 'BIN bulunamadi';
		$soapResult->BIN_SanalPosResult->DT_Bilgi = new \stdClass();

		$mockRequest = $this->getMockRequest();

		$response = new BinLookupResponse($mockRequest, $soapResult);

		$this->assertFalse($response->isSuccessful());
		$this->assertEquals('BIN bulunamadi', $response->getMessage());
	}

	/**
	 * Test BinLookupResponse getData returns null when DT_Bilgi is empty.
	 */
	public function test_bin_lookup_response_empty_data()
	{
		$dtBilgi = new \stdClass();

		$soapResult = new \stdClass();
		$soapResult->BIN_SanalPosResult = new \stdClass();
		$soapResult->BIN_SanalPosResult->Sonuc = '1';
		$soapResult->BIN_SanalPosResult->Sonuc_Str = 'Islem basarili';
		$soapResult->BIN_SanalPosResult->DT_Bilgi = $dtBilgi;

		$mockRequest = $this->getMockBuilder(BinLookupRequest::class)
			->disableOriginalConstructor()
			->onlyMethods(['getData'])
			->getMock();

		$binModel = new BinLookupRequestModel([
			'G' => new GModel([
				'CLIENT_CODE' => 12345,
				'CLIENT_USERNAME' => 'test@parampos.com',
				'CLIENT_PASSWORD' => 'testPass123',
			]),
			'BIN' => '545616',
		]);

		$mockRequest->method('getData')->willReturn($binModel);

		$response = new BinLookupResponse($mockRequest, $soapResult);

		$this->assertTrue($response->isSuccessful());

		$data = $response->getData();

		$this->assertNull($data);
	}

	/**
	 * Test BinLookupResponse getData returns null when BIN not found in XML data.
	 */
	public function test_bin_lookup_response_bin_not_in_xml()
	{
		$binData = '<NewDataSet><Temp><BIN>999999</BIN><Kart_Banka>Other Bank</Kart_Banka></Temp></NewDataSet>';

		$dtBilgi = new \stdClass();
		$dtBilgi->any = $binData;

		$soapResult = new \stdClass();
		$soapResult->BIN_SanalPosResult = new \stdClass();
		$soapResult->BIN_SanalPosResult->Sonuc = '1';
		$soapResult->BIN_SanalPosResult->Sonuc_Str = 'Islem basarili';
		$soapResult->BIN_SanalPosResult->DT_Bilgi = $dtBilgi;

		$mockRequest = $this->getMockBuilder(BinLookupRequest::class)
			->disableOriginalConstructor()
			->onlyMethods(['getData'])
			->getMock();

		$binModel = new BinLookupRequestModel([
			'G' => new GModel([
				'CLIENT_CODE' => 12345,
				'CLIENT_USERNAME' => 'test@parampos.com',
				'CLIENT_PASSWORD' => 'testPass123',
			]),
			'BIN' => '545616', // This BIN is NOT in the XML
		]);

		$mockRequest->method('getData')->willReturn($binModel);

		$response = new BinLookupResponse($mockRequest, $soapResult);

		$data = $response->getData();

		$this->assertNull($data);
	}
}
