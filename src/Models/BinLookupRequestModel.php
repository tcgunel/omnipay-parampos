<?php

namespace Omnipay\Parampos\Models;

class BinLookupRequestModel extends BaseModel
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
    public string $BIN;
}
