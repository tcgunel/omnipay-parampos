<?php

namespace Omnipay\Parampos\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class PaymentInquiryResponse extends AbstractResponse
{
    public function __construct(RequestInterface $request, $data)
    {
        parent::__construct($request, $data);

        $this->data = json_decode(json_encode($data->TP_Islem_Sorgulama4Result, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }

    public function isSuccessful(): bool
    {
        return (int)$this->data['Sonuc'] > 0 && (int)$this->data['DT_Bilgi']['Odeme_Sonuc'] === 1;
    }

    public function getMessage()
    {
        return $this->data['Sonuc_Str'];
    }
}
