<?php

namespace Omnipay\Parampos\Models;

class VerifyEnrolmentRequestModel extends BaseModel
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
    public string $UCD_MD;

    /**
     * @required
     */
    public string $Islem_GUID;

    /**
     * @required
     */
    public string $Siparis_ID;
}
