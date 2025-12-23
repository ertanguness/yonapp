<?php
require_once dirname(__DIR__, 4) . '/configs/bootstrap.php';

use App\Helper\Helper;
use App\Helper\Security;
use App\Services\Gate;
use Model\FinansalRaporModel;
use Model\TahsilatModel;
use Model\BorclandirmaDetayModel;
use Model\TahsilatDetayModel;

header('Content-Type: application/json; charset=utf-8');

try {
    Gate::authorizeOrDie('yonetici_aidat_odeme');

    $siteId = (int)($_SESSION['site_id'] ?? 0);
    if ($siteId <= 0) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Site oturumu bulunamadı.']);
        exit;
    }

    $enc = (string)($_GET['kisi'] ?? '');
    if ($enc === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'kisi parametresi zorunlu.']);
        exit;
    }

    $kisiId = (int)Security::decrypt($enc);
    if ($kisiId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Geçersiz kisi parametresi.']);
        exit;
    }

    $fin = new FinansalRaporModel();
    $tah = new TahsilatModel();
    $bdm = new BorclandirmaDetayModel();
    $tdm = new TahsilatDetayModel();

    // Site güvenliği: kişi bu siteye mi ait? (borç view'ından doğrula)
    $all = $fin->getGuncelBorclarGruplu($siteId);
    $person = null;
    foreach ($all as $r) {
        if ((int)($r->kisi_id ?? 0) === $kisiId) { $person = $r; break; }
    }
    if (!$person) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Kişi bulunamadı veya bu siteye ait değil.']);
        exit;
    }

    $ozet = $fin->KisiFinansalDurum($kisiId);

    // Eski modal ile uyumlu: kişinin TÜM borçlandırmaları
    // Modal dosyası (`tahsilat-detay.php`) bu metodu kullanıyor.
    $kisiBorclari = $fin->getKisiBorclar($kisiId);

    $fmt = fn($v) => Helper::formattedMoney((float)($v ?? 0));

    // Tahsilatlar
    // Not: UI tarafında satır bazlı liste + silme gerekiyor.
    // Bu yüzden hangi model metodu kullanılırsa kullanılsın, burada normalize edip row list döndürüyoruz.
    $tahsilatlarRaw = [];
    if (method_exists($tah, 'KisiTahsilatlariWithDetails')) {
        $tahsilatlarRaw = $tah->KisiTahsilatlariWithDetails($kisiId);
    } elseif (method_exists($tah, 'KisiTahsilatlari')) {
        $tahsilatlarRaw = $tah->KisiTahsilatlari($kisiId);
    }

    $tahType = 'rows';
    $tahsilatlar = array_values(array_filter(array_map(function ($t) use ($fmt, $tdm) {
        if (!$t) return null;

        // Model bazen stdClass, bazen associative array döndürebiliyor.
        $get = function ($key, $default = null) use ($t) {
            if (is_array($t)) {
                return array_key_exists($key, $t) ? $t[$key] : $default;
            }
            if (is_object($t)) {
                return isset($t->$key) ? $t->$key : $default;
            }
            return $default;
        };

        // farklı kaynaklarda id alanı değişebiliyor
        $id = (int)($get('id') ?? $get('tahsilat_id') ?? $get('tahsilatId') ?? 0);

        $tutarVal = null;
        foreach (['tutar', 'tahsilat_tutari', 'toplam_tutar', 'odeme', 'amount'] as $k) {
            $vv = $get($k);
            if ($vv !== null && $vv !== '') {
                $tutarVal = (float)$vv;
                break;
            }
        }
        $tutarVal = (float)($tutarVal ?? 0);

        $tarih = (string)($get('islem_tarihi') ?? $get('tarih') ?? $get('created_at') ?? '');
        $aciklama = (string)($get('aciklama') ?? $get('ana_aciklama') ?? $get('tahsilat_turu') ?? '');

        // Kredi ile karşılanan kısım (varsa)
        $kullanilanKrediVal = null;
        foreach (['kullanilan_kredi', 'kredi_kullanimi', 'kredi', 'used_credit', 'kullanilanKredi'] as $k) {
            $vv = $get($k);
            if ($vv !== null && $vv !== '') {
                $kullanilanKrediVal = (float)$vv;
                break;
            }
        }
        $kullanilanKrediVal = (float)($kullanilanKrediVal ?? 0);

        // Boş/yanlış kayıtları UI'da göstermeyelim (ekranda 0,00 TL satırları gibi)
        $hasMeaningful = ($id > 0) || ($tutarVal > 0.00001) || ($tarih !== '') || ($aciklama !== '');
        if (!$hasMeaningful) return null;

        $detaylar = [];
        if ($id > 0 && method_exists($tdm, 'findAllByTahsilatIdWithDueDetails')) {
            try {
                $rawDetaylar = $tdm->findAllByTahsilatIdWithDueDetails($id);
                if (is_array($rawDetaylar)) {
                    $detaylar = array_values(array_filter(array_map(function ($d) use ($fmt) {
                        if (!$d) return null;
                        $borcAdi = (string)($d->borc_adi ?? '');
                        $borcAciklama = (string)($d->borc_aciklama ?? '');
                        $odenen = (float)($d->odenen_tutar ?? 0);
                        // boş satırları atla
                        if ($borcAdi === '' && $borcAciklama === '' && $odenen <= 0) return null;
                        return [
                            'borc_adi' => $borcAdi,
                            'aciklama' => $borcAciklama,
                            'odenen_tutar' => $odenen,
                            'odenen_tutar_fmt' => $fmt($odenen),
                        ];
                    }, $rawDetaylar)));
                }
            } catch (Throwable $e) {
                // Detay alınamazsa ana liste çalışmaya devam etsin.
                $detaylar = [];
            }
        }

        return [
            'id' => $id,
            'id_enc' => $id > 0 ? Security::encrypt($id) : '',
            'islem_tarihi' => $tarih,
            'aciklama' => $aciklama,
            // UI `tutar` alanını string olarak basıyor
            'tutar' => $fmt($tutarVal),
            'tutar_val' => $tutarVal,
            'kullanilan_kredi' => $kullanilanKrediVal,
            'kullanilan_kredi_fmt' => $fmt($kullanilanKrediVal),
            // Tahsilat dağılımı (hangi borç/kalemlerden tahsil edildi)
            'detaylar' => $detaylar,
        ];
    }, is_array($tahsilatlarRaw) ? $tahsilatlarRaw : [])));

    $kalan = (float)($person->toplam_kalan_borc ?? 0);
    $kredi = (float)($person->kredi_tutari ?? 0);
    $net = $kredi - $kalan;

    $payload = [
        'success' => true,
        'data' => [
            'person' => [
                'kisi_id' => $kisiId,
                'kisi_enc' => Security::encrypt($kisiId),
                'adi_soyadi' => (string)($person->adi_soyadi ?? ''),
                'daire_kodu' => (string)($person->daire_kodu ?? ''),
                'telefon' => (string)($person->telefon ?? ''),
                'kredi_tutari' => (float)($person->kredi_tutari ?? 0),
                'kredi_tutari_fmt' => ($person->kredi_tutari ?? 0),
                'net' => $net,
                'status' => ($net < 0) ? 'Borçlu' : (($net == 0) ? 'Temiz' : 'Alacaklı'),
            ],
            'kpi' => [
                'toplam_borc' => (float)($ozet->toplam_borc ?? 0),
                'toplam_tahsilat' => (float)($ozet->toplam_tahsilat ?? 0),
                'kalan_borc' => (float)($ozet->kalan_borc ?? max(0, -$net)),
                'toplam_borc_fmt' => $fmt($ozet->toplam_borc ?? 0),
                'toplam_tahsilat_fmt' => $fmt($ozet->toplam_tahsilat ?? 0),
                'kalan_borc_fmt' => $fmt($ozet->bakiye ?? 0),
                'bakiye' => (float)($ozet->bakiye ?? 0),
            ],
            // UI tarafı bu alanı okuyor; burada artık "tüm borçlar" dönüyoruz.
            'borclandirma_detaylari' => array_map(function($d) use ($fmt){
                $id = (int)($d->id ?? 0);
                $tutar = (float)($d->tutar ?? 0);
                $gecikme = (float)($d->hesaplanan_gecikme_zammi ?? 0);
                $odenen = (float)($d->yapilan_tahsilat ?? 0);
                $kalan = (float)($d->toplam_kalan_borc ?? max(0, ($tutar + $gecikme) - $odenen));
                return [
                    'id' => $id,
                    'id_enc' => Security::encrypt($id),
                    'borclandirma_id' => (int)($d->borclandirma_id ?? 0),
                    'borc_adi' => (string)($d->borc_adi ?? ''),
                    'aciklama' => (string)($d->aciklama ?? ($d->borc_adi ?? '')),
                    'baslangic_tarihi' => (string)($d->baslangic_tarihi ?? ''),
                    'bitis_tarihi' => (string)($d->bitis_tarihi ?? ''),
                    'son_odeme_tarihi' => (string)($d->son_odeme_tarihi ?? ''),
                    'tutar' => $tutar,
                    'tutar_fmt' => $fmt($tutar),
                    'hesaplanan_gecikme_zammi' => $gecikme,
                    'hesaplanan_gecikme_zammi_fmt' => $fmt($gecikme),
                    'yapilan_tahsilat' => $odenen,
                    'yapilan_tahsilat_fmt' => $fmt($odenen),
                    'toplam_kalan_borc' => $kalan,
                    'toplam_kalan_borc_fmt' => $fmt($kalan),
                ];
            }, $kisiBorclari ?: []),
            'tahsilatlar_type' => $tahType,
            'tahsilatlar' => $tahsilatlar,
        ]
    ];

    echo json_encode($payload);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Sunucu hatası', 'detail' => $e->getMessage()]);
}
