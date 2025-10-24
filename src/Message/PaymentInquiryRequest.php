<?php

namespace Omnipay\Parampos\Message;

use Omnipay\Common\Exception\InvalidCreditCardException;
use Omnipay\Parampos\Helpers\Helper;
use Omnipay\Parampos\Models\GModel;
use Omnipay\Parampos\Models\PaymentInquiryRequestModel;
use Omnipay\Parampos\Models\VerifyEnrolmentRequestModel;
use Omnipay\Parampos\Traits\PurchaseGettersSetters;
use SoapClient;

class PaymentInquiryRequest extends RemoteAbstractRequest
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
            'guid',
            'transactionId',
        );

        if ($this->getTestMode()) {

            $this->endpoint = 'https://testposws.param.com.tr/turkpos.ws/service_turkpos_prod.asmx?wsdl';

        }

        return new VerifyEnrolmentRequestModel([
            'G'          => new GModel([
                'CLIENT_CODE'     => $this->getClientCode(),
                'CLIENT_USERNAME' => $this->getClientUsername(),
                'CLIENT_PASSWORD' => $this->getClientPassword(),
            ]),
            'GUID'       => $this->getGuid(),
            'Siparis_ID' => $this->getTransactionId(),
        ]);
    }

    protected function createResponse($data)
    {
        return $this->response = new PaymentInquiryResponse($this, $data);
    }

    /**
     * @param PaymentInquiryRequestModel $data
     * @return PaymentInquiryResponse
     * @throws \SoapFault
     */
    public function sendData($data)
    {
        $client = new SoapClient($this->getEndpoint());

        $response = $client->TP_Islem_Sorgulama4($data);

        return $this->createResponse($response);
    }
}
