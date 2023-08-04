<?php

namespace Omnipay\Parampos\Models;

class VerifyEnrolmentResponseModel extends BaseModel
{
    public string $Sonuc;
    public string $Sonuc_Ack;
    public string $Dekont_ID;
    public string $Siparis_ID;
    public string $UCD_MD;
    public string $Bank_Trans_ID;
    public string $Bank_AuthCode;
    public string $Bank_HostMsg;
    public string $Bank_Extra;
    public int $Bank_Sonuc_Kod;
    public string $Bank_HostRefNum;
}
