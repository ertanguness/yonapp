<?php

require_once dirname(__DIR__, levels: 2) . '/configs/bootstrap.php';


use App\Helper\Security;
use App\Helper\Date;
use App\Helper\Helper;
use Model\SitelerModel;
use Model\DueModel;

$Due = new DueModel();
$SiteModel = new SitelerModel();

$logger = \getLogger();

//Siteleri getir

$siteler = $SiteModel->all();

foreach ($siteler as $site) {
    //Her site için işlemleri yap
    $logger->info("Processing site: " . $site->site_adi . " (ID: " . $site->id . ")" . "\n");

    //sitenin auto_renew özelliği açık borçlandırmalarını getir
    $auto_renew_dues = $Due->getAutoRenewDues($site->id);

    foreach ($auto_renew_dues as $due) {
        $day = (int)date('d'); //Bugünün günü
        $period = DueModel::PERIOD[$due->period];

        $logger->info("İşlenen Aidat: " . $due->due_name .
            "(ID: " . $due->id . ") 
                               (site ID: " . $site->id . ")
                               (borclandirma_tipi: " . $due->borclandirma_tipi . ")
                               (başlangıç tarihi: " . $due->start_date .  ")  
                               (bitiş tarihi: " . $due->end_date .  ")
                               (periyot: " . DueModel::PERIOD[$due->period] . ")
                               (ayın günü: " . $day . ")
                               (periyot günü: " . $due->day_of_period . ")
                               ");

        //Periyot günü bugünün gününe eşit mi kontrol et
        if ($period == "Aylık" && $day != $due->day_of_period) {
            $logger->info("Periyot türü (" . $period . ")
                                    bugünün günü (" . $day . ") 
                                    periyot günü (" .  $due->day_of_period . ") ile eşleşmiyor. 
                                    Atlanıyor.");
            continue; //Eşleşmiyorsa atla
        }

        //3 Aylık ise başlangıç tarihinden itibaren 3 ayda bir bugünün gününe eşit mi kontrol et
        // Başlangıç tarihini ve bugünün tarihini DateTime nesnesine dönüştürün
        $start_date_obj = new DateTime($due->start_date);
        $today_date_obj = new DateTime(date('Y-m-d'));

        // Başlangıç günü (örneğimizde 01)
        $start_day = (int)$start_date_obj->format('d');

        // BUGÜNÜN GÜNÜ BAŞLANGIÇ GÜNÜNE EŞİT Mİ?
        if ($day != $start_day) {
            $logger->info("Periyot türü (" . $period . ") - bugünün günü (" . $day . ") başlangıç günü (" . $start_day . ") ile eşleşmiyor. Atlanıyor.");
            continue; // Gün eşleşmiyorsa, borçlandırma olmaz.
        }

        // TOPLAM AY FARKINI HESAPLA (Yıl atlamalarını dikkate alarak)
        $diff = $start_date_obj->diff($today_date_obj);

        // Bu, başlangıç ve bugün arasındaki toplam ay farkıdır.
        // Yılları aya çevirip (diff->y * 12) + ay farkını (diff->m) ekliyoruz.
        $total_months_diff = ($diff->y * 12) + $diff->m;

        // Eğer bugün başlangıç tarihinden küçükse borçlandırma yapmayız.
        if ($start_date_obj > $today_date_obj) {
            $logger->info("Başlangıç tarihi henüz gelmedi. Atlanıyor.");
            continue;
        }


        // 3 AYLIK KONTROL: Toplam ay farkı periyoda bölünebiliyor mu?
        if ($period == "3 Aylık") {

            // Yalnızca gün eşleştiyse bu kontrol yapılır.
            if ($total_months_diff % 3 != 0) {
                $logger->info("Periyot türü (" . $period . ") kontrolü: Toplam ay farkı (" . $total_months_diff . ") tam 3 aya bölünmüyor. Atlanıyor.");
                continue;
            }
        }
        // Diğer periyotlar için (6 Aylık, Yıllık) bu bloklar tekrarlanır.
        elseif ($period == "6 Aylık") {
            if ($total_months_diff % 6 != 0) {
                $logger->info("Periyot türü (" . $period . ") kontrolü: Toplam ay farkı (" . $total_months_diff . ") tam 6 aya bölünmüyor. Atlanıyor.");
                continue;
            }
        }
        // ... Diğer işlemler (Borçlandırma yap)



        $borclandirma_tipi = $due->borclandirma_tipi;
        if ($borclandirma_tipi == 'person') {
            $borclandirilacakKisiler = json_decode($due->borclandirilacaklar, true);
            $logger->info("Borçlandırılacak kişiler: " . implode(", ", $borclandirilacakKisiler));
        }




        //Burada borçlandırma işlemlerini yapabilirsiniz
        //Örneğin, borçlandırma tarihlerini kontrol edip yeni borçlar ekleyebilirsiniz
    }
}
//sitenin auto_renew özelliği açık borçlandırmalarını getir
