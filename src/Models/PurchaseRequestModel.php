<?php

namespace Omnipay\Parampos\Models;

class PurchaseRequestModel extends BaseModel
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
    public string $KK_Sahibi;

    /**
     * @required
     */
    public string $KK_No;

    /**
     * @required
     */
    public string $KK_SK_Ay;

    /**
     * @required
     */
    public string $KK_SK_Yil;

    /**
     * @required
     */
    public string $KK_CVC;

    /**
     * @required
     */
    public string $KK_Sahibi_GSM;

    /**
     * @required
     */
    public string $Hata_URL;

    /**
     * @required
     */
    public string $Basarili_URL;

    /**
     * @required
     */
    public string $Siparis_ID;

    public string $Siparis_Aciklama;

    /**
     * @required
     */
    public int $Taksit;

    /**
     * @required
     */
    public string $Islem_Tutar;

    /**
     * @required
     */
    public string $Toplam_Tutar;

    /**
     * @required
     */
    public string $Islem_Hash;

    /**
     * @required
     */
    public string $Islem_Guvenlik_Tip;
    public string $Islem_ID;

    /**
     * @required
     */
    public string $IPAdr;

    public string $Ref_URL;

    public function setGUID(string $GUID): void
    {
        $this->GUID = substr(trim($GUID), 0, 36);
    }

    public function setKKSahibi(string $KK_Sahibi): void
    {
        $this->KK_Sahibi = substr(trim($KK_Sahibi), 0, 100);
    }

    public function setKKNo(string $KK_No): void
    {
        $this->KK_No = substr(trim($KK_No), 0, 16);
    }

    public function setKKSKAy(string $KK_SK_Ay): void
    {
        $this->KK_SK_Ay = (int)substr(trim($KK_SK_Ay), -2);
    }

    public function setKKSKYil(string $KK_SK_Yil): void
    {
        $this->KK_SK_Yil = (int)substr(trim($KK_SK_Yil), -2);
    }

    public function setKKCVC(string $KK_CVC): void
    {
        $this->KK_CVC = substr(trim($KK_CVC), 0, 3);
    }

    public function setKKSahibiGSM(string $KK_Sahibi_GSM): void
    {
        $this->KK_Sahibi_GSM = substr(trim($KK_Sahibi_GSM), -10);
    }

    public function setHataURL(string $Hata_URL): void
    {
        $this->Hata_URL = substr(trim($Hata_URL), 0, 256);
    }

    public function setBasariliURL(string $Basarili_URL): void
    {
        $this->Basarili_URL = substr(trim($Basarili_URL), 0, 256);
    }

    public function setSiparisID(string $Siparis_ID): void
    {
        $this->Siparis_ID = substr(trim($Siparis_ID), 0, 50);
    }

    public function setSiparisAciklama(string $Siparis_Aciklama): void
    {
        $this->Siparis_Aciklama = substr(trim($Siparis_Aciklama), 0, 250);
    }

    public function setTaksit(int $Taksit): void
    {
        $this->Taksit = $Taksit;
    }

    public function setIslemTutar(string $Islem_Tutar): void
    {
        $this->Islem_Tutar = number_format($Islem_Tutar, 2, ',', '');
    }

    public function setToplamTutar(string $Toplam_Tutar): void
    {
        $this->Toplam_Tutar = number_format($Toplam_Tutar, 2, ',', '');
    }

    public function setIslemHash(string $Islem_Hash): void
    {
        $this->Islem_Hash = $Islem_Hash;
    }

    public function setIslemGuvenlikTip(string $Islem_Guvenlik_Tip): void
    {
        $this->Islem_Guvenlik_Tip = $Islem_Guvenlik_Tip;
    }

    public function setIslemID(string $Islem_ID): void
    {
        $this->Islem_ID = $Islem_ID;
    }

    public function setIPAdr(string $IPAdr): void
    {
        $this->IPAdr = substr(trim($IPAdr), 0, 50);
    }

    public function setRefURL(string $Ref_URL): void
    {
        $this->Ref_URL = substr(trim($Ref_URL), 0, 256);
    }
}
