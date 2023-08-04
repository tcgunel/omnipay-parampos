<?php

namespace Omnipay\Parampos\Traits;

trait PurchaseGettersSetters
{
    public function setClientUsername($value)
    {
        return $this->setParameter('clientUsername', $value);
    }

	public function getClientUsername()
	{
		return $this->getParameter('clientUsername');
	}

    public function setClientPassword($value)
    {
        return $this->setParameter('clientPassword', $value);
    }

	public function getClientPassword()
	{
		return $this->getParameter('clientPassword');
	}
    public function setClientCode($value)
    {
        return $this->setParameter('clientCode', $value);
    }

	public function getClientCode()
	{
		return $this->getParameter('clientCode');
	}

    public function setGuid($value)
    {
        return $this->setParameter('guid', $value);
    }

	public function getGuid()
	{
		return $this->getParameter('guid');
	}

    public function setSecure($value)
    {
        return $this->setParameter('secure', $value);
    }

	public function getSecure()
	{
		return $this->getParameter('secure');
	}

    public function getEndpoint()
    {
        return $this->endpoint;
    }

    public function setInstallment($value)
    {
        return $this->setParameter('installment', $value);
    }

    public function getInstallment()
    {
        return $this->getParameter('installment');
    }

    public function getMd()
    {
        return $this->getParameter('md');
    }

    public function setMd($value)
    {
        return $this->setParameter('md', $value);
    }

    public function getMdStatus()
    {
        return $this->getParameter('mdStatus');
    }

    public function setMdStatus($value)
    {
        return $this->setParameter('mdStatus', $value);
    }

    public function getOrderId()
    {
        return $this->getParameter('orderId');
    }

    public function setOrderId($value)
    {
        return $this->setParameter('orderId', $value);
    }

    public function getIslemGUID()
    {
        return $this->getParameter('islemGUID');
    }

    public function setIslemGUID($value)
    {
        return $this->setParameter('islemGUID', $value);
    }

    public function getIslemHash()
    {
        return $this->getParameter('islemHash');
    }

    public function setIslemHash($value)
    {
        return $this->setParameter('islemHash', $value);
    }
}
