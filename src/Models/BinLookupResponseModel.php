<?php

namespace Omnipay\Parampos\Models;

class BinLookupResponseModel extends BaseModel
{
    public function __construct(?array $abstract)
    {
        parent::__construct($abstract);
    }

    public mixed $BIN;
    public mixed $BinBaslangic;
    public mixed $BinBitis;
    public mixed $BinUzunluk;
    public mixed $SanalPOS_ID;
    public mixed $Kart_Banka;
    public mixed $DKK;
    public mixed $Kart_Tip;
    public mixed $Kart_Org;
    public mixed $Banka_Kodu;
    public mixed $Kart_Ticari;
    public mixed $Kart_Marka;
}
