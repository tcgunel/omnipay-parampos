<?php

namespace Omnipay\Parampos\Message;

use Omnipay\Parampos\Constants\PaymentType;
use Omnipay\Parampos\Models\GModel;
use Omnipay\Parampos\Models\HashModel;
use Omnipay\Parampos\Models\PurchaseRequestModel;
use Omnipay\Parampos\Traits\PurchaseGettersSetters;
use SoapClient;

class PurchaseRequest extends RemoteAbstractRequest
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
            'card',
            'cancelUrl',
            'returnUrl',
            'transactionId',
            'amount',
            'secure',
            'clientIp',
        );
    }

    /**
     * @return PurchaseRequestModel
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

        return new PurchaseRequestModel([
            'G'                  => new GModel([
                'CLIENT_CODE'     => $this->getClientCode(),
                'CLIENT_USERNAME' => $this->getClientUsername(),
                'CLIENT_PASSWORD' => $this->getClientPassword(),
            ]),
            'GUID'               => $this->getGuid(),
            'KK_Sahibi'          => $this->getCard()->getName(),
            'KK_No'              => $this->getCard()->getNumber(),
            'KK_SK_Ay'           => $this->getCard()->getExpiryMonth(),
            'KK_SK_Yil'          => $this->getCard()->getExpiryYear(),
            'KK_CVC'             => $this->getCard()->getCvv(),
            'KK_Sahibi_GSM'      => $this->getCard()->getPhone(),
            'Hata_URL'           => $this->getCancelUrl(),
            'Basarili_URL'       => $this->getReturnUrl(),
            'Siparis_ID'         => $this->getTransactionId(),
            'Siparis_Aciklama'   => '',
            'Taksit'             => $this->getInstallment(),
            'Islem_Tutar'        => $this->getAmount(),
            'Toplam_Tutar'       => $this->getAmount(),
            'Islem_Hash'         => '',
            'Islem_Guvenlik_Tip' => $this->getSecure() ? PaymentType::SECURE : PaymentType::NON_SECURE,
            'Islem_ID'           => '',
            'IPAdr'              => $this->getClientIp(),
            'Ref_URL'            => '',
        ]);
    }

    /**
     * @param PurchaseRequestModel $purchase_request_model
     * @return HashModel
     */
    protected function hash_object(PurchaseRequestModel $purchase_request_model): HashModel
    {
        return new HashModel([
            'Data' => $purchase_request_model->G->CLIENT_CODE .
                $purchase_request_model->GUID .
                $purchase_request_model->Taksit .
                $purchase_request_model->Islem_Tutar .
                $purchase_request_model->Toplam_Tutar .
                $purchase_request_model->Siparis_ID,
            'G'    => $purchase_request_model->G,
        ]);
    }

    protected function createResponse($data)
    {
        return $this->response = new PurchaseResponse($this, $data);
    }

    /**
     * @param PurchaseRequestModel $data
     * @return PurchaseResponse
     * @throws \SoapFault
     */
    public function sendData($data)
    {
        $client = new SoapClient($this->getEndpoint());

        $data
            ->setIslemHash(
                $client
                    ->SHA2B64($this->hash_object($data))
                    ->SHA2B64Result
            );

        $response = $client->TP_WMD_UCD($data);

        return $this->createResponse($response);
    }
}
