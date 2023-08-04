<?php

namespace Omnipay\Parampos\Models;

class PurchaseResponseModel extends BaseModel
{
    public int $Islem_ID;
    public ?string $Islem_GUID = null;
    public string $UCD_HTML;
    public ?string $UCD_MD = null;

    /**
     * Sonuc parametresi “0” dan büyükse, 3D Güvenli Ödeme işlemini başlatmak için UCD_HTML içeriğini ekrana bastırınız.
     * @var
     */
    public string $Sonuc;
    public string $Sonuc_Str;
    public int $Banka_Sonuc_Kod;
    public string $Siparis_ID;

    public ?string $Bank_Trans_ID;

    public ?string $Bank_AuthCode;

    public ?string $Bank_HostMsg;

    public ?string $Bank_Extra;

    public ?string $Bank_HostRefNum;
}
