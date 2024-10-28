<?php

namespace Omnipay\Parampos;

use Omnipay\Common\AbstractGateway;
use Omnipay\Parampos\Message\BinLookupRequest;
use Omnipay\Parampos\Message\PaymentInquiryRequest;
use Omnipay\Parampos\Message\PurchaseRequest;
use Omnipay\Parampos\Message\VerifyEnrolmentRequest;
use Omnipay\Parampos\Traits\PurchaseGettersSetters;

/**
 * Parampos Gateway
 * (c) Tolga Can Günel
 * 2015, mobius.studio
 * http://www.github.com/tcgunel/omnipay-parampos
 *
 * @method \Omnipay\Common\Message\NotificationInterface acceptNotification(array $options = [])
 * @method \Omnipay\Common\Message\RequestInterface completeAuthorize(array $options = [])
 */
class Gateway extends AbstractGateway
{
    use PurchaseGettersSetters;

    public function getName(): string
    {
        return 'Parampos';
    }

    public function getDefaultParameters()
    {
        return [
            'installment' => '1',
            'secure' => true,

        ];
    }

    public function purchase(array $options = [])
    {
        return $this->createRequest(PurchaseRequest::class, $options);
    }

    public function completePurchase(array $options = [])
    {
        return $this->createRequest(VerifyEnrolmentRequest::class, $options);
    }

    public function paymentInquiry(array $options = [])
    {
        return $this->createRequest(PaymentInquiryRequest::class, $options);
    }

    public function binLookup(array $options = [])
    {
        return $this->createRequest(BinLookupRequest::class, $options);
    }
}
