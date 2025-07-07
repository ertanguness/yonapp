<?php
namespace App\Helper;

use App\Helper\Security;
use Model\KasaModel;
use DateTime;

class FinansalHelper
{
    protected $KasaModel = null;

    public function __construct()
    {
        $this->KasaModel = new KasaModel(); // KasaModel sınıfından bir örnek oluştur
    }   
   
    /**Sitenin Kasa Listesini getir, Aktif olan seçili gelir
     * @param string $site_id
     * @return string
     */
    public static function KasaSelect($name = 'kasa', $id = null, $disabled = null)
    {
        $KasaModel = new KasaModel(); // KasaModel sınıfından bir örnek oluştur
        $results = $KasaModel->SiteKasalari(); // Kasa listesini al
        $select = '<select name="' . $name . '" class="form-select select2 w-100" id="' . $name . '" style="min-width:200px;width:100%" ' . $disabled . '>';
        
        foreach ($results as $row) { // $results üzerinde döngü
            $selected = ($id == $row->id || (empty($id) && $row->varsayilan_mi == 1)) ? ' selected' : ''; // Eğer id eşitse veya id boşsa ve varsayılan kasa ise seçili yap
            $select .= '<option value="' . Security::encrypt($row->id) . '"' . $selected . '>' . htmlspecialchars($row->kasa_adi) . '</option>'; // Kasa adını güvenli şekilde ekle
        }
        
        $select .= '</select>';
        return $select;
    }


    /**
 * Verilen borç bilgileri için güncel gecikme zammını hesaplar.
 *
 * @param float $kalanAnaPara Borcun ödenmemiş anapara tutarı.
 * @param string $sonOdemeTarihi 'Y-m-d' formatında son ödeme tarihi.
 * @param float $aylikCezaOrani Aylık yüzde olarak ceza oranı (örn: 5.00 for %5).
 * @return float Hesaplanmış toplam gecikme zammı.
 */
static function hesaplaGecikmeZammi(float $kalanAnaPara, string $sonOdemeTarihi, float $aylikCezaOrani): float
{
    // Eğer ceza oranı 0 ise veya anapara ödenmişse, gecikme zammı olmaz.
    if ($aylikCezaOrani <= 0 || $kalanAnaPara <= 0) {
        return 0.0;
    }

    $bugun = new DateTime();
    $sonOdeme = new DateTime($sonOdemeTarihi);

    // Eğer son ödeme tarihi geçmemişse, gecikme zammı olmaz.
    if ($bugun <= $sonOdeme) {
        return 0.0;
    }

    // Geciken gün sayısını hesapla
    $gecikenGunSayisi = $bugun->diff($sonOdeme)->days;
    
    // Günlük faiz oranını hesapla (aylık oran / 30 gün)
    $gunlukFaizOrani = $aylikCezaOrani / 100 / 30;

    // Toplam gecikme zammını hesapla
    $toplamGecikmeZammi = $kalanAnaPara * $gunlukFaizOrani * $gecikenGunSayisi;

    // Sonucu 2 ondalık basamağa yuvarla
    return round($toplamGecikmeZammi, 2);
}

}