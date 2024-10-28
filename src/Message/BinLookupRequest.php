<?php

namespace Omnipay\Parampos\Message;

use Omnipay\Common\Exception\InvalidCreditCardException;
use Omnipay\Parampos\Models\BinLookupRequestModel;
use Omnipay\Parampos\Models\GModel;
use Omnipay\Parampos\Traits\PurchaseGettersSetters;
use SoapClient;

class BinLookupRequest extends RemoteAbstractRequest
{
    use PurchaseGettersSetters;

    private string $endpoint = 'https://posws.param.com.tr/turkpos.ws/service_turkpos_prod.asmx?WSDL';

    /**
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getData()
    {
        $this->validate(
            'clientUsername',
            'clientPassword',
            'clientCode',
        );

        if (! is_null($this->getCard()->getNumber()) && ! preg_match('/^\d{8,19}$/', $this->getCard()->getNumber())) {
            throw new InvalidCreditCardException('Card number should have at least 6 to maximum of 19 digits');
        }

        if ($this->getTestMode()) {

            $this->endpoint = 'https://test-dmz.param.com.tr:4443/turkpos.ws/service_turkpos_test.asmx?WSDL';

        }

        return new BinLookupRequestModel([
            'G' => new GModel([
                'CLIENT_CODE' => $this->getClientCode(),
                'CLIENT_USERNAME' => $this->getClientUsername(),
                'CLIENT_PASSWORD' => $this->getClientPassword(),
            ]),
            'BIN' => substr($this->getCard()->getNumber(), 0, 6),
        ]);
    }

    protected function createResponse($data): BinLookupResponse
    {
        return $this->response = new BinLookupResponse($this, $data);
    }

    /**
     * @param  BinLookupResponse  $data
     *
     * @throws \SoapFault
     */
    public function sendData($data)
    {
        $client = new SoapClient($this->getEndpoint());

        $response = $client->BIN_SanalPos($data);

        return $this->createResponse($response);
    }
}
