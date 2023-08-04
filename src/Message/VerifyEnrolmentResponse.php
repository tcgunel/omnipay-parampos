<?php

namespace Omnipay\Parampos\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;
use Omnipay\Parampos\Models\VerifyEnrolmentResponseModel;

class VerifyEnrolmentResponse extends AbstractResponse
{
    public function __construct(RequestInterface $request, $data)
    {
        parent::__construct($request, $data);

        $this->data = new VerifyEnrolmentResponseModel((array)$data->TP_WMD_PayResult);
    }

    public function isSuccessful(): bool
    {
        return $this->data->Sonuc > 0 && $this->data->Dekont_ID > 0;
    }

    public function getMessage()
    {
        return $this->data->Sonuc_Ack;
    }
}
