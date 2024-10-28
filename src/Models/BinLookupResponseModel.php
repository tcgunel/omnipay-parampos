<?php

namespace Omnipay\Parampos\Models;

class BinLookupResponseModel extends BaseModel
{
    public function __construct(?array $abstract)
    {
        parent::__construct($abstract);
    }

    public ?string $BIN;
    public ?string $BinBaslangic;
    public ?string $BinBitis;
    public ?string $BinUzunluk;
    public ?string $SanalPOS_ID;
    public ?string $Kart_Banka;
    public ?string $DKK;
    public ?string $Kart_Tip;
    public ?string $Kart_Org;
    public ?string $Banka_Kodu;
    public ?string $Kart_Ticari;
    public mixed $Kart_Marka;
}
