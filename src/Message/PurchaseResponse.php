<?php

namespace Omnipay\Parampos\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\Common\Message\RequestInterface;
use Omnipay\Parampos\Models\PurchaseResponseModel;

class PurchaseResponse extends AbstractResponse implements RedirectResponseInterface
{
    public function __construct(RequestInterface $request, $data)
    {
        parent::__construct($request, $data);

        $this->data = new PurchaseResponseModel((array)$data->TP_WMD_UCDResult);
    }

    public function isSuccessful(): bool
    {
        return $this->data->Sonuc > 0 && $this->data->Islem_ID > 0 && $this->data->UCD_HTML === 'NONSECURE';
    }

    public function isRedirect(): bool
    {
        return $this->data->Sonuc > 0 && $this->data->UCD_HTML !== 'NONSECURE';
    }

    public function getMessage()
    {
        return $this->data->Sonuc_Str;
    }

    public function getRedirectUrl()
    {
    	/** @var PurchaseRequest $request */
    	$request = $this->getRequest();

        return $request->getEndpoint();
    }

    public function getRedirectMethod(): string
    {
        return 'POST';
    }

	public function getRedirectResponse()
	{
		$response = parent::getRedirectResponse();

		$response->setContent($this->data->UCD_HTML);

		return $response;
	}

}
