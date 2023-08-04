<?php

namespace Omnipay\Parampos;

use Omnipay\Common\AbstractGateway;
use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Parampos\Traits\PurchaseGettersSetters;

/**
 * Parampos Gateway
 * (c) Tolga Can Günel
 * 2015, mobius.studio
 * http://www.github.com/tcgunel/omnipay-parampos
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
            "installment" => "1",
            "secure"      => true,

        ];
    }

    public function purchase(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\Parampos\Message\PurchaseRequest', $parameters);
    }

    /**
     * @param array $parameters
     *
     * @return AbstractRequest|\Omnipay\Common\Message\RequestInterface
     */
    public function completePurchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Parampos\Message\VerifyEnrolmentRequest', $parameters);
    }
}
