<?php

namespace Omnipay\Parampos\Models;

class PaymentInquiryRequestModel extends BaseModel
{
    public function __construct(?array $abstract)
    {
        parent::__construct($abstract);
    }

    /**
     * @required
     * @var GModel
     */
    public GModel $G;

    /**
     * @required
     */
    public string $GUID;

    /**
     * @required
     */
    public ?string $Dekont_ID;

    /**
     * @required
     */
    public ?string $Siparis_ID;

    /**
     * @required
     */
    public ?string $Islem_ID;
}
