<?php

use App\Helper\Security;
use App\Helper\Helper;
use App\Helper\Date;
use App\Helper\Aidat;
use Model\DairelerModel;
use Model\TahsilatOnayModel;
use Model\KisilerModel;
use App\Helper\FinansalHelper;



$DueHelper = new Aidat();
$Daire = new DairelerModel();
$Kisi = new KisilerModel();

$TahsilatOnay = new TahsilatOnayModel();

$bekleyen_tahsilatlar = $TahsilatOnay->BekleyenTahsilatlar($_SESSION['site_id']);


// //1. Önce session'dan kontrol et
// if (isset($_SESSION["kasa_id"]) && !empty($_SESSION["kasa_id"])) {
//     $kasa_id = $_SESSION["kasa_id"];
// }

// // 2. POST ile yeni seçim geldi mi?
// if (isset($_POST['kasalar'])) {
//     $kasa_id = Security::decrypt($_POST['kasalar']) ?? 0;
//     $_SESSION["kasa_id"] = $kasa_id;
//     //Helper::dd(($kasa_id));

//     echo "<script>history.replaceState({}, '', '/onay-bekleyen-tahsilatlar');</script>";
//     $id = null;
// }

// // 3. URL parametresi var mı?
// if (isset($id) && !empty($id)) {
//     $kasa_id = Security::decrypt($id);
//     $_SESSION["kasa_id"] = $kasa_id;
// }

// // 4. Hiçbiri yoksa varsayılan kasayı al
// if (!$kasa_id) {
//     try {
//         $varsayilanKasa = $Kasa->varsayilanKasa();
//         $kasa_id = $varsayilanKasa ? $varsayilanKasa->id : 1;
//         $_SESSION["kasa_id"] = $kasa_id;
//     } catch (Exception $e) {
//         // Hata durumunda fallback
//         $kasa_id = 1;
//         $_SESSION["kasa_id"] = $kasa_id;
//     }
// }



?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Site Tahsilat Listesi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Tahsilatlar</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items ">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-header-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>

                </a>
            </div>
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <div>
                    <form method="post" id="kasalar">

                        <?php //echo FinansalHelper::KasaSelect("kasalar", $kasa_id) 
                        ?>
                    </form>
                </div>
                <div class="d-flex align-items-center gap-2">


                    <a href="/yonetici-aidat-odeme" class="btn btn-outline-secondary">
                        <i class="feather-arrow-left me-2"></i>Listeye Dön
                    </a>
                    <a href="index?p=dues/payment/upload-from-xls" class="btn btn-outline-success">
                        <i class="feather-file-plus me-2"></i>Toplu Onay
                    </a>
                    <a href="/eslesmeyen-odemeler" class="btn btn-warning">
                        <i class="feather-check-circle me-2"></i>Eşleşmeyen Ödemeler
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
    <?php
    $title = "Tahsilat Onaylama";
    $text = "Bu sayfada yüklenen tahsilatlardan kategori bazlı ödeme onayı yapabilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>

    <div class="row mb-5">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card widget-tasks-content">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive tasks-items-wrapper m-3">
                                <table class="table table-hover datatables " id="tahsilatOnayTable">
                                    <thead>
                                        <tr>
                                            <th>Sıra</th>
                                            <th>Referans No</th>
                                            <th style="width:30%">Açıklama</th>
                                            <th>Ödenen Tutarı</th>
                                            <th>İşlenen Tutar</th>
                                            <th>Kalan Tutar</th>
                                            <th>İşlenecek Tutar</th>

                                        </tr>
                                    </thead>
                                    <!-- ... thead kısmı aynı ... -->
                                    <tbody>
                                        <?php
                                        foreach ($bekleyen_tahsilatlar as $index => $onay):
                                            $enc_id = Security::encrypt($onay->id);
                                            $tahsilat_tutari = $onay->tutar ?? 0;
                                            // Bu veriyi optimize edilmiş SQL sorgusundan aldığımızı varsayıyoruz
                                            $islenen_tutar = $onay->onaylanan_toplam_tutar ?? 0;
                                            $kalan_tutar = $onay->kalan_tutar ?? 0;
                                            $unique_id = 'onay-satiri-' . $onay->id;
                                        ?>

                                            <!-- SADECE ANA BİLGİ SATIRI KALACAK -->
                                            <tr class="bekleyen-tahsilat-satiri">
                                                <td><?php echo $index + 1 ?></td>
                                                <td>
                                                    <span class="badge bg-light-secondary"><?php echo Security::escape($onay->id ?? '#'); ?></span>
                                                </td>
                                                <td>
                                                    <div class="fw-bold"><?php echo $onay->daire_kodu  . " | " . $onay->adi_soyadi; ?></div>
                                                    <div class="text-muted fs-12 mt-1">Ödeme Tarihi: <?php echo Date::dmYHis($onay->islem_tarihi); ?></div>
                                                    <p class="fs-12 text-muted mt-1 fst-italic tasks-sort-desc">"<?php echo htmlspecialchars($onay->aciklama) ?>"</p>
                                                    <div class="tasks-list-action d-flex align-items-center gap-3">

                                                        <a href="javascript:void(0);"
                                                            data-id="<?php echo Security::encrypt($onay->id); ?>"
                                                            class="text-primary eslesmeyen-havuza-gonder">Eşleşmeyen Havuzuna Gönder</a> |
                                                        <a href="javascript:void(0);"
                                                            data-aciklama="<?php echo $onay->aciklama; ?>"
                                                            class="text-success aciklamayi-kopyala">Açıklamayı Kopyala</a> | 
                                                        <a href="javascript:void(0);"
                                                            data-id="<?php echo Security::encrypt($onay->id); ?>"
                                                            class="text-danger yuklenen-tahsilat-sil">Sil</a>

                                                    </div>
                                                </td>
                                                <td class="text-end fw-bold"><?= Helper::formattedMoney($tahsilat_tutari) ?></td>
                                                <td class="text-end text-success"><?php echo Helper::formattedMoney($islenen_tutar) ?></td>
                                                <td class="text-end text-danger fw-bold"><?php echo Helper::formattedMoney($kalan_tutar) ?></td>
                                                <td class="text-center">
                                                    <?php if ($kalan_tutar > 0.009): ?>
                                                        <!-- DİKKAT: data-bs-toggle ve data-bs-target KALDIRILDI -->
                                                        <button
                                                            class="btn btn-sm rounded btn-primary borclari-goster-btn"
                                                            data-kisi-id="<?php echo Security::encrypt($onay->kisi_id); ?>"
                                                            data-onay-id="<?php echo $onay->id; ?>">
                                                            <i class="feather-git-merge me-2"></i>Borçları Eşleştir
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="badge bg-success"><i class="feather-check-circle me-1"></i>Tamamlandı</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="tahsilatDetayModal" tabindex="-1" data-bs-keyboard="false" role="dialog">
    <div class="modal-dialog modal-dialog-scrollable modal-xl modal-dialog-centered" role="document">
        <div class="modal-content tahsilat-detay">

            <div class="modal-footer">
                <button id="btn-n-save" class="float-left btn btn-success">Save</button>
                <button class="btn btn-danger" data-dismiss="modal">Discard</button>
                <button id="btn-n-add" class="btn btn-success" disabled="disabled">Add Note</button>
            </div>
        </div>
    </div>
</div>

<style>
    table tr td[colspan="7"] {
        padding: 0 !important;
        margin: 0 !important;
    }
</style>


<!-- Sayfanızın alt kısmına, örneğin </body> etiketinden önce -->

<template id="borc-eslestirme-sablonu">
    <form class="borc-eslestirme-form" data-onay-id="{ONAY_ID}">
        <div class="row">
            <div class="alert alert-warning mt-2 fs-12 uyari-mesaji" style="display: none;">
                Uyarı mesajı alanı...
            </div>
            <div class="col-xxl-8 col-lg-8">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title">İşlenecek Tutarı Borçlara Dağıtın</h5>

                    </div>
                    <div class="card-body custom-card-action">
                        <ul class="list-unstyled mb-0 activity-feed-1 borclar-checkbox-grubu">
                            <!-- Borç satırları buraya eklenecek -->
                        </ul>
                    </div>
                </div>
            </div>



            <!-- Sağ Taraf - İşlem Alanı -->
            <div class="col-md-4">
                <div class="processing-area bg-white p-4 rounded-3">
                    <!-- Seçilen Borç Toplamı - ÜSTTE -->
                    <div class="mb-4">
                        <span class="d-block fs-12 text-muted">Seçilen Borç Toplamı</span>
                        <strong class="secilen-borc-toplami fs-4">0,00 ₺</strong>
                    </div>

                    <!-- İşlenecek Tutar - ORTADA -->
                    <div class="mb-4">
                        <label for="islenecek-tutar" class="form-label fs-12 text-muted">İşlenecek Tutar</label>
                        <input type="text" class="form-control money islenecek-tutar-input" id="islenecek-tutar" value="400,00 TL">

                        <input type="checkbox" class="form-check-input" id="artani-kredi-olarak-ekle" name="artani-kredi-olarak-ekle">
                        <label class="form-check-label fs-12" for="artani-kredi-olarak-ekle">Artanı kredi olarak ekle</label>
                    </div>

                    <!-- Butonlar --->
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-secondary iptal-btn">İptal</button>
                        <button type="submit" class="btn btn-success tahsilati-onayla-btn">
                            <i class="feather-check me-1"></i>Onayla ve Kaydet
                        </button>
                    </div>
                </div>
            </div>
        </div>



        <!-- İŞLEM ALANI -->

    </form>
</template>

<!-- Borç satırları için ayrı bir template de yapabiliriz, bu daha da modüler olur -->
<template id="borc-satiri-sablonu">
    <div class="form-check border-bottom py-2">
        <input class="form-check-input borc-checkbox" type="checkbox" value="{BORC_DETAY_ID}" id="borc-{BORC_ID_UNIQUE}">
        <label class="form-check-label d-flex justify-content-between w-100" for="borc-{BORC_ID_UNIQUE}">
            <!-- Kişi id gizli inputa -->
            <input type="hidden" name="kisi_id" id="kisi_id" value="{KISI_ID}">
            <span>
                <strong>{BORC_ADI}</strong>
                <small class="text-muted d-block">{ACIKLAMA}</small>
                <small class="text-muted d-block">Son Ödeme: {SON_ODEME_TARIHI}</small>
            </span>
            <span class="text-end">
                <span class="d-block">Anapara: <strong>{ANAPARA_TUTARI}</strong></span>
                <span class="d-block text-danger">Gecikme Zammı: <strong>{GECIKME_ZAMMI}</strong></span>
            </span>
        </label>
    </div>
</template>

<script>
    let APIurl = "/pages/dues/payment/api/APItahsilat_onay.php";
    $(function() {

        // Mevcut .aciklamayi-kopyala click handler'ını değiştir (satır ~250 civarı):

$(".aciklamayi-kopyala").on("click", function() {
    let aciklama = $(this).data("aciklama");
    console.log(aciklama);
    
    // Basit try-catch ile güvenli kopyalama
    try {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(aciklama).then(function() {
                alert("Açıklama kopyalandı: " + aciklama);
            });
        } else {
            // Fallback - geçici textarea yöntemi
            const textArea = document.createElement("textarea");
            textArea.value = aciklama;
            textArea.style.position = "fixed";
            textArea.style.opacity = "0";
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            Toastify({
                        text: "Açıklama kopyalandı: " + aciklama,
                        duration: 3000,
                        className: "border-radius-10",
                        close: true,
                        gravity: "top", // `top` or `bottom`
                        position: "center", // `left`, `center` or `right`
                        stopOnFocus: true, // Prevents dismissing of toast on hover
                        style: {
                            background: "#000"
                        }
                    }).showToast();
        }
    } catch (err) {
        console.error('Kopyalama hatası:', err);
        alert('Kopyalama başarısız oldu');
    }
});

        //#kasalar'da değişiklik olduğunda
        $("#kasalar").on("change", function() {
            //kasalar formunu submit et
            $("#kasalar").submit();
        });

        // =g=====================================================
        // 2. GEREKLİ TEMPLATE'LERİ SEÇME
        // =======================================================
        const borcEslestirmeSablonu = $('#borc-eslestirme-sablonu');
        const borcSatiriSablonu = $('#borc-satiri-sablonu');

        if (borcEslestirmeSablonu.length === 0 || borcSatiriSablonu.length === 0) {
            console.error('Gerekli HTML template (şablon) etiketleri bulunamadı!');
            return;
        }


        // --- 1. MERKEZİ FONKSİYON: Bir satırın çocuk (child) bölümünü açar/kapatır ---

        function toggleChildRow(mainRow) {
            const $mainRow = $(mainRow); // jQuery nesnesi olduğundan emin ol
            const $button = $mainRow.find('button.borclari-goster-btn');
            const row = table.row($mainRow);

            if (row.child.isShown()) {
                // --- KAPATMA İŞLEMİ ---
                row.child.hide();
                $mainRow.removeClass('details-shown');
                $button.html('<i class="feather-git-merge me-2"></i>Borçları Eşleştir')
                    .removeClass('btn-secondary')
                    .addClass('btn-primary');
            } else {
                // --- AÇMA İŞLEMİ ---
                $button.html('<i class="feather-x me-2"></i>Kapat')
                    .removeClass('btn-primary')
                    .addClass('btn-secondary');

                row.child('<div class="borc-listesi-wrapper text-center">Yükleniyor...</div>').show();
                $mainRow.addClass('details-shown');

                const kisiId = $button.data('kisi-id');
                const onayId = $button.data('onay-id');

                //console.log('Kişi ID:', kisiId, 'Onay ID:', onayId);

                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'get_kisi_borclari',
                        kisi_id: kisiId
                    },
                    success: function(response) {
                        if (row.child.isShown()) { // Kullanıcı beklerken kapatmamışsa içeriği bas
                            if (response.status === 'success') {
                                const content = renderBorclarFormu(response.data, $mainRow, onayId);
                                row.child(content).show();
                            } else {
                                row.child('<div class="alert alert-danger m-3">Hata: ' + response.message + '</div>').show();
                            }
                        }
                        console.log('Borçlar yüklendi:', response.data);
                    },
                    error: function() {
                        if (row.child.isShown()) {
                            row.child('<div class="alert alert-danger m-3">Borçlar yüklenirken bir sunucu hatası oluştu.</div>').show();
                        }
                    }
                });
            }
        }

        // --- 2. OLAY YÖNETİCİLERİ (EVENT HANDLERS) ---

        // a) "Borçları Eşleştir / Kapat" butonuna tıklandığında
        $('#tahsilatOnayTable tbody').on('click', 'button.borclari-goster-btn', function() {
            const mainRow = $(this).closest('tr');
            toggleChildRow(mainRow); // Merkezi fonksiyonu çağır
        });

        // b) Form içindeki "İptal" butonuna tıklandığında
        $('#tahsilatOnayTable tbody').on('click', '.iptal-btn', function() {
            // Önceki cevaptaki gibi, çocuk satırın bir önceki kardeşini (ana satırı) bul
            const childRow = $(this).closest('tr');
            const mainRow = childRow.prev('.dt-hasChild');

            if (mainRow.length > 0) {
                toggleChildRow(mainRow); // Yine aynı merkezi fonksiyonu çağır
            } else {
                console.error("Ana satır (.prev()) bulunamadı. DOM yapısını kontrol edin.");
            }
        });





    });


    /**
     * Gelen borç verileriyle formu oluşturur ve bir jQuery nesnesi olarak DÖNDÜRÜR.
     * @param {Array} borclar - API'den gelen borçlar dizisi
     * @param {jQuery} $anaSatir - Ana <tr> satırının jQuery nesnesi
     * @param {string|number} onayId - İşlem yapılan onay kaydının ID'si
     * @returns {jQuery} Oluşturulan formun tamamını içeren bir div'in jQuery nesnesi
     */
    function renderBorclarFormu(borclar, $anaSatir, onayId) {
        // Kalan tutarı ana satırın 6. hücresinden (index 5) al
        const kalanTutar = $anaSatir.find('td').eq(5).text().trim();

        // Ana formu template'den klonla
        const $formClone = $($('#borc-eslestirme-sablonu').html());

        // Formun ana bilgilerini doldur
        $formClone.attr('data-onay-id', onayId);
        $formClone.find('.islenecek-tutar-input').val(kalanTutar).attr('id', `islenecek-tutar-${onayId}`);

        const $borclarListesiDiv = $formClone.find('.borclar-checkbox-grubu');
        const borcSatiriHtml = $('#borc-satiri-sablonu').html();

        if (borclar.length === 0) {
            $borclarListesiDiv.html('<div class="alert alert-info">Bu kişiye ait ödenmemiş borç bulunamadı.</div>');
        } else {
            // Her borç için satırları oluştur ve listeye ekle
            $.each(borclar, function(index, borc) {
                let uniqueId = `${onayId}-${borc.id}`;
                let $borcSatiri = $(borcSatiriHtml);

                $borcSatiri.find('.borc-checkbox').val(borc.id).attr('id', `borc-${uniqueId}`);
                $borcSatiri.find('.form-check-label').attr('for', `borc-${uniqueId}`);

                let labelHtml = $borcSatiri.find('.form-check-label').html()
                    .replace('{ONAY_ID}', onayId)
                    .replace('{KISI_ID}', borc.kisi_id)
                    .replace('{BORC_ADI}', borc.borc_adi)
                    .replace('{ACIKLAMA}', borc.aciklama || '')
                    .replace('{SON_ODEME_TARIHI}', borc.son_odeme_tarihi)
                    .replace('{ANAPARA_TUTARI}', borc.anapara)
                    .replace('{GECIKME_ZAMMI}', borc.gecikme_zammi);

                $borcSatiri.find('.form-check-label').html(labelHtml);
                $borclarListesiDiv.append($borcSatiri);
            });
        }

        // Yeni eklenen .money input'ları için inputmask'ı başlat (varsa)
        $formClone.find('.money').inputmask();

        // Oluşturulan formu bir wrapper div içinde döndür. Bu, child row'un stilini korur.
        return $('<div class="borc-listesi-wrapper bg-light rounded"></div>').append($formClone);
    }

    $(function() {
        let guncellemeIstegi; // Sadece debounce için kullanılacak.


        // =======================================================
        // CHECKBOX DEĞİŞİM OLAYI
        // =======================================================
        $('#tahsilatOnayTable tbody').on('change', '.borc-checkbox', function() {
            const $form = $(this).closest('.borc-eslestirme-form');

            // Dizi, olay içinde anlık olarak oluşturuluyor.
            const secilenBorcIdleri = $form.find('.borc-checkbox:checked').map(function() {

                return $(this).val();
            }).get();

            $form.find('.secilen-borc-toplami').html('<span class="spinner-border spinner-border-sm"></span>');

            clearTimeout(guncellemeIstegi);
            guncellemeIstegi = setTimeout(function() {
                guncelleToplamTutarSunucudan(secilenBorcIdleri, $form);
            }, 300);
        });

        // =======================================================
        // "İŞLENECEK TUTAR" INPUT DEĞİŞİM OLAYI
        // =======================================================
        $('#tahsilatOnayTable tbody').on('input', '.islenecek-tutar-input', function() {
            const $form = $(this).closest('.borc-eslestirme-form');
            uyariMesajiniGuncelle($form);
        });
    });


    /**
     * Sunucuya AJAX isteği göndererek seçilen borçların toplamını alır ve ekranı günceller.
     */
    function guncelleToplamTutarSunucudan(borcIdleri, $form) {
        const $toplamGosterge = $form.find('.secilen-borc-toplami');
        console.log('Seçilen borç ID\'leri:', borcIdleri);
        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: {
                "action": 'hesapla_toplam_tutar',
                "borc_idler": borcIdleri
                // kisi_id gereksiz olduğu için kaldırıldı.
            },
            success: function(response) {
                // DÜZELTME 1: PHP'den gelen 'success' anahtarını kontrol ediyoruz.
                if (response.success) {

                    console.log('Gelen cevap:', response);
                    const toplamTutar = parseFloat(response.toplam_tutar) || 0;

                    // DÜZELTME 2: Ham sayısal değeri data attribute'üne kaydet.
                    $toplamGosterge.data('raw-total', toplamTutar);

                    // DÜZELTME 3: Gelen sayıyı JavaScript ile para formatına çevir.
                    const formatliTutar = toplamTutar.toLocaleString('tr-TR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }) + ' ₺';
                    $toplamGosterge.text(formatliTutar);

                    // Toplam güncellendi, uyarıyı kontrol et.
                    uyariMesajiniGuncelle($form);

                } else {
                    $toplamGosterge.text('Hata!').addClass('text-danger');
                    // response.message PHP'den geliyorsa göster, gelmiyorsa genel bir mesaj yaz.
                    const message = response.message || 'Toplam hesaplanamadı.';
                    Swal.fire('Hata!', message, 'error');
                }
            },
            error: function() {
                $toplamGosterge.text('Hata!').addClass('text-danger');
                Swal.fire('Hata!', 'Toplam tutar hesaplanırken sunucuya ulaşılamadı.', 'error');
            }
        });
    }


    /**
     * Sadece uyarı mesajının görünürlüğünü kontrol eder.
     */
    function uyariMesajiniGuncelle($form) {
        const $uyariMesaji = $form.find('.uyari-mesaji');
        const $toplamGosterge = $form.find('.secilen-borc-toplami');
        const $islenecekTutarInput = $form.find('.islenecek-tutar-input');

        const secilenToplam = parseFloat($toplamGosterge.data('raw-total')) || 0;
        const islenecekTutar = parseFloat($islenecekTutarInput.val().replace(/\./g, '').replace(',', '.')) || 0;

        if (secilenToplam > 0 && islenecekTutar < secilenToplam) {
            const formatliToplam = secilenToplam.toLocaleString('tr-TR', {
                style: 'currency',
                currency: 'TRY'
            });
            $uyariMesaji.text(`İşlenecek tutar, seçilen borçların toplamından (${formatliToplam}) az. Ödeme kısmi olarak yansıtılacaktır.`).show();
        } else {
            $uyariMesaji.hide();
        }
    }


    //ctrl + s ile submitBtn'a tıkla
    $(document).on('keydown', function(e) {
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            $('.tahsilati-onayla-btn:visible').first().click();
        }
    });

    /**
     * Yapılan Tahsilatı borç ile eşleştirir ve kaydeder.
     */
    $('#tahsilatOnayTable tbody').on('submit', '.borc-eslestirme-form', function(e) {
        e.preventDefault();

        const $form = $(this);
        const onayId = $form.data('onay-id');
        const islenecekTutar = parseFloat($form.find('.islenecek-tutar-input').val().replace(/\./g, '').replace(',', '.')) || 0;
        const secilenBorcIdleri = $form.find('.borc-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
        const $submitBtn = $form.find('.tahsilati-onayla-btn');
        const $iptalBtn = $form.find('.iptal-btn');
        const $toplamGosterge = $form.find('.secilen-borc-toplami');
        const secilenToplam = parseFloat($toplamGosterge.data('raw-total')) || 0;
        console.log('Form gönderildi. Onay ID:', onayId, 'İşlenecek Tutar:', islenecekTutar, 'Seçilen Borç ID\'leri:', secilenBorcIdleri);

        if (secilenBorcIdleri.length === 0 && $form.find('#artani-kredi-olarak-ekle').is(':checked') === false) {
            Swal.fire('Uyarı', 'Lütfen en az bir borç seçin.', 'warning');
            return;
        }
        if (islenecekTutar <= 0 && $form.find('#artani-kredi-olarak-ekle').is(':checked') === false) {
            Swal.fire('Uyarı', 'İşlenecek tutar sıfır veya negatif olamaz.', 'warning');
            return;
        }
        if (islenecekTutar > secilenToplam && $form.find('#artani-kredi-olarak-ekle').is(':checked') === false) {
            Swal.fire('Uyarı', 'İşlenecek tutar, seçilen borçların toplamından fazla olamaz.', 'warning');
            return;
        }
        $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Kaydediliyor...');
        $iptalBtn.prop('disabled', true);

        // AJAX isteği

        $.ajax({
            url: APIurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'tahsilati_borc_ile_eslestir',
                onay_id: onayId,
                islenecek_tutar: islenecekTutar,
                borc_idler: secilenBorcIdleri,
                artani_kredi_olarak_ekle: $form.find('#artani-kredi-olarak-ekle').is(':checked') ? 1 : 0
            },
            success: function(response) {
                if (response.success) {
                    Toastify({
                        text: response.message,
                        duration: 3000,
                        className: "border-radius-10",
                        close: true,
                        gravity: "top", // `top` or `bottom`
                        position: "center", // `left`, `center` or `right`
                        stopOnFocus: true, // Prevents dismissing of toast on hover
                        style: {
                            background: "#000"
                        }
                    }).showToast();

                    console.log('Tahsilat başarıyla kaydedildi:', response);
                    // İşlem başarılı ise, ilgili satırı tablodan kaldır
                    const $anaSatir = $form.closest('tr').prev('.dt-hasChild');
                    if ($anaSatir.length > 0) {
                        const row = table.row($anaSatir);
                        //Eğer kalan_tutar 0 ise satırı kaldır
                        if (response.data.kalan_tutar <= 0.0009) {
                            row.remove().draw();
                        } else {
                            //Tablonun 
                            // İşlenen tutarı güncelle
                            $anaSatir.find('td').eq(4).text(response.data.islenen_tutar);
                            // Kalan tutarı güncelle
                            $anaSatir.find('td').eq(5).text(response.data.kalan_tutar);
                        }
                    }
                } else {
                    const message = response.message || 'Tahsilat borçlara yansıtılırken bir hata oluştu.';
                    Swal.fire('Hata', message, 'error');
                }
            },
            error: function() {
                Swal.fire('Hata', 'Sunucuya ulaşılamadı veya bir hata oluştu.', 'error');
            },
            complete: function() {
                $submitBtn.prop('disabled', false).html('<i class="feather-check me-1"></i>Onayla ve Kaydet');
                $iptalBtn.prop('disabled', false);
            }
        });
    });


    /**
     * Eşleşmeyen havuzuna gönder
     */
    $(document).on('click', '.eslesmeyen-havuza-gonder', function() {
        const tahsilatId = $(this).data('id');
        row = table.row($(this).closest('tr'));

        swal.fire({
            title: 'Uyarı!',
            text: "Bu tahsilatı eşleşmeyen havuzuna göndermek istediğinize emin misiniz?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Evet, Gönder',
            cancelButtonText: 'İptal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: APIurl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'eslesmeyen_havuza_gonder',
                        tahsilat_id: tahsilatId
                    },
                    success: function(response) {

                        if (response.status === 'success') {
                            row.remove().draw();
                            Toastify({
                                text: response.message,
                                duration: 3000,
                                className: "border-radius-10",
                                close: true,
                                gravity: "top", // `top` or `bottom`
                                position: "center", // `left`, `center` or `right`
                                stopOnFocus: true, // Prevents dismissing of toast on hover
                                style: {
                                    background: "#000"
                                }
                            }).showToast();

                            // İlgili satırı tablodan kaldır
                        } else {
                            const message = response.message || 'Eşleşmeyen havuza gönderilirken bir hata oluştu.';
                            Swal.fire('Hata', message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Hata', 'Sunucuya ulaşılamadı veya bir hata oluştu.', 'error');
                    }
                });
            }
        });
        // AJAX isteği

    });

    $(document).on('click', '.yuklenen-tahsilat-sil', function() {
        const tahsilatId = $(this).data('id');
        row = table.row($(this).closest('tr'));


        Swal.fire({
            title: 'Uyarı!',
            text: "Bu tahsilatı silmek istediğinize emin misiniz?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, Sil',
            cancelButtonText: 'İptal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: APIurl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'yuklenen_tahsilat_sil',
                        tahsilat_id: tahsilatId
                    },
                    success: function(response) {

                        if (response.status === 'success') {
                            row.remove().draw();
                            Toastify({
                                text: response.message,
                                duration: 3000,
                                className: "border-radius-10",
                                close: true,
                                gravity: "top", // `top` or `bottom`
                                position: "center", // `left`, `center` or `right`
                                stopOnFocus: true, // Prevents dismissing of toast on hover
                                style: {
                                    background: "#000"
                                }
                            }).showToast();

                            // İlgili satırı tablodan kaldır
                        } else {
                            const message = response.message || 'Tahsilat silinirken bir hata oluştu.';
                            Swal.fire('Hata', message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Hata', 'Sunucuya ulaşılamadı veya bir hata oluştu.', 'error');
                    }
                });
            }
        });

    });
</script>