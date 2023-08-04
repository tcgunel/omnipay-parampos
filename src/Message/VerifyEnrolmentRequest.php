<?php

namespace Omnipay\Parampos\Message;

use Omnipay\Parampos\Constants\PaymentType;
use Omnipay\Parampos\Models\GModel;
use Omnipay\Parampos\Models\HashModel;
use Omnipay\Parampos\Models\PurchaseRequestModel;
use Omnipay\Parampos\Models\VerifyEnrolmentRequestModel;
use Omnipay\Parampos\Traits\PurchaseGettersSetters;
use SoapClient;

class VerifyEnrolmentRequest extends RemoteAbstractRequest
{
    use PurchaseGettersSetters;

    private string $endpoint = 'https://posws.param.com.tr/turkpos.ws/service_turkpos_prod.asmx?WSDL';

    protected function validateAll()
    {
        $this->validate(
            'clientUsername',
            'clientPassword',
            'clientCode',
            'guid',
            'md',
            'islemGUID',
            'transactionId',
        );
    }

    /**
     * @return VerifyEnrolmentRequestModel
     *
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     * @throws \Omnipay\Common\Exception\InvalidCreditCardException
     */
    public function getData()
    {
        $this->validateAll();

        if ($this->getTestMode()) {

            $this->endpoint = 'https://test-dmz.param.com.tr:4443/turkpos.ws/service_turkpos_test.asmx?WSDL';

        }

        return new VerifyEnrolmentRequestModel([
            'G'          => new GModel([
                'CLIENT_CODE'     => $this->getClientCode(),
                'CLIENT_USERNAME' => $this->getClientUsername(),
                'CLIENT_PASSWORD' => $this->getClientPassword(),
            ]),
            'GUID'       => $this->getGuid(),
            'UCD_MD'     => $this->getMd(),
            'Islem_GUID' => $this->getIslemGUID(),
            'Siparis_ID' => $this->getTransactionId(),
        ]);
    }

    protected function createResponse($data)
    {
        return $this->response = new VerifyEnrolmentResponse($this, $data);
    }

    /**
     * @param PurchaseRequestModel $data
     * @return PurchaseResponse
     * @throws \SoapFault
     */
    public function sendData($data)
    {
        $client = new SoapClient($this->getEndpoint());

        $response = $client->TP_WMD_Pay($data);

        return $this->createResponse($response);
    }
}
