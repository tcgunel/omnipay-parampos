<?php

namespace Omnipay\Parampos\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;
use Omnipay\Parampos\Models\BinLookupResponseModel;

class BinLookupResponse extends AbstractResponse
{
    public function __construct(RequestInterface $request, $data)
    {
        parent::__construct($request, $data);
        $this->data = json_decode(json_encode($data->BIN_SanalPosResult, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }

    public function isSuccessful(): bool
    {
        return (int) $this->data['Sonuc'] > 0;
    }

    public function getMessage()
    {
        return $this->data['Sonuc_Str'];
    }

    /**
     * @throws \JsonException
     */
    public function getData(): ?BinLookupResponseModel
    {
        if (! empty($this->data['DT_Bilgi']['any'])) {
            preg_match('/<Temp.*?<BIN>'.$this->request->getData()->BIN.'<\/BIN>.*?<\/Temp>/i', $this->data['DT_Bilgi']['any'], $temp);
            if (! empty($temp[0])) {

                $temp[0] = preg_replace('/<Temp.*?>/i', '<Temp>', $temp[0]);

                return new BinLookupResponseModel(json_decode(json_encode(simplexml_load_string($temp[0]), JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR));
            }
        }

        return null;
    }
}
