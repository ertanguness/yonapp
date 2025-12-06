<?php

require_once dirname(__DIR__, levels: 4) . '/configs/bootstrap.php';

use App\Helper\Date;
use App\Helper\Helper;
use App\Services\Gate;
use Model\SitelerModel;
use App\Helper\Security;
use Model\TahsilatModel;
use Model\TahsilatDetayModel;



//Gate::authorizeOrDie('dues_payment_view', 'Makbuzları görüntüleme yetkiniz bulunmamaktadır.', false);

$SiteModel = new SitelerModel();
$TahsilatModel = new TahsilatModel();
$TahsilatDetayModel = new TahsilatDetayModel();



$site_id = $_SESSION['site_id'];
$site = $SiteModel->find($site_id);

$logoPath = $site->logo_path ?? '';
$logoSrc = '/assets/images/logo/' . ($logoPath ? $logoPath : 'default-logo.png');


$id = Security::decrypt($_GET['makbuz_id'] ?? null);

$tahsilat = $TahsilatModel->getPaymentWithCaseName($id);

//Tahsilatın işlenmiş detaylarını al
$tahsilat_detaylar = $TahsilatDetayModel->findAllByTahsilatIdWithDueDetails($tahsilat->id);


// echo "<pre>";
// print_r($tahsilat_detaylar);
// echo "</pre>";


if ($id === null) {
    echo "Invalid ID";
    exit;
}

?>

<div class="modal-header">
    <div>
        <h2 class="fs-16 fw-700 text-truncate-1-line mb-0 mb-sm-1">Tahsilat Makbuzu</h2>

    </div>
    <div class="d-flex align-items-center justify-content-center">

        <a href="javascript:void(0)" class="d-flex me-1 printBTN">
            <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" title="" data-bs-original-title="Makbuz Yazdır" aria-label="Print Invoice"><i class="feather feather-printer"></i></div>
        </a>
        <a href="javascript:void(0)" class="d-flex me-1 downloadBTN" data-id="<?php echo $_GET['makbuz_id'] ?? '' ?>">
            <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" title="" data-bs-original-title="Makbuz İndir" aria-label="Makbuz İndir"><i class="feather feather-download"></i></div>
        </a>


        <a href="javascript:void(0)" class="d-flex me-1" data-bs-dismiss="modal" aria-label="Close">
            <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" title="" data-bs-original-title="Close">
                <i class="feather feather-x"></i>
            </div>
        </a>
    </div>
</div>
<div class="makbuz-icerik">


    <div class="row overflow-auto">
        <div class="col-lg-12">

            <div class="px-4 pt-4">
                <div class="makbuz-header d-flex justify-content-between align-items-center">
                  


                        <div class="d-flex align-items-center gap-2">
                            <img src="<?php echo $logoSrc; ?>" alt="Logo" style="height:72px;width:auto;" />
                            <div>

                                <div class="fs-24 fw-bolder font-montserrat-alt text-uppercase"><?php echo $site->site_adi; ?></div>
                                <address class="text-muted" style="white-space:nowrap;">
                                    <?php echo $site->tam_adres . ' ' . $site->il . ' - ' . $site->ilce; ?>
                                </address>
                            </div>
                        </div>


                        <div class="lh-lg pt-3 pt-sm-0 doc-meta ms-auto text-end">
                            <div class="fs-4 fw-bold text-primary">Makbuz</div>
                            <div><span class="fw-bold text-dark">Tarih:</span> <span class="text-muted"><?php echo date("d.m.Y H:i"); ?></span></div>
                        </div>

                   
                </div>
                <hr class="border-dashed">
                <div class="d-sm-flex gap-4">

                    <div class="border-end border-end-dashed border-gray-500 d-none d-sm-block"></div>
                    <div class="mt-4 mt-sm-0">
                        <h2 class="fs-16 fw-bold text-dark mb-3">Tahsilat Detayı:</h2>
                        <div class="text-muted lh-lg">
                            <div>
                                <span class="text-muted">İşlem Tarihi:</span>
                                <span class="fw-bold text-dark">
                                    <?php echo Date::dmYHIS($tahsilat->islem_tarihi) ?>
                                </span>
                            </div>
                            <div>
                                <span class="text-muted">Tutar :</span>
                                <span class="fw-bold text-dark">
                                    <?php echo Helper::formattedMoney($tahsilat->tutar) ?>
                                </span>
                            </div>
                            <div>
                                <span class="text-muted">Ödeme Açıklaması:</span>
                                <span class="fw-bold text-warning"><?php echo $tahsilat->aciklama; ?></span>
                            </div>

                            <div>
                                <span class="text-muted">Ödeme Yeri:</span>
                                <span class="fw-bold text-dark"><?php echo $tahsilat->kasa_adi; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <hr class="border-dashed mb-0">
                <div class="table-responsive m-3">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Sıra</th>
                                <th>Borç Adı</th>
                                <th>Tutar</th>
                                <th>Açıklama</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            foreach ($tahsilat_detaylar as $detay) :

                            ?>
                                <tr>
                                    <td><a href="javascript:void(0)"><?php echo $i++; ?> </a></td>
                                    <td class="text-wrap"><?php echo $detay->borc_adi; ?></td>
                                    <td><?php echo Helper::formattedMoney($detay->odenen_tutar); ?></td>
                                    <td class="text-wrap"><?php echo $detay->borc_aciklama; ?></td>
                                </tr>
                            <?php endforeach; ?>



                        </tbody>
                    </table>
                </div>
                <!-- <hr class="border-dashed mt-0"> -->

                <div class="px-4 pt-4 mb-4 d-sm-flex align-items-center justify-content-between">
                    <div class="mb-5 mb-sm-0">
                        <?php
                        $aciklama = $tahsilat->tutar < 0 ? 'iade' : "tahsil";
                        ?>
                        Yalnız <b><?php echo Helper::sayiyiYaziyaCevir($tahsilat->tutar)  ?></b> TL <?php echo $aciklama; ?> edilmiştir.
                    </div>
                    <div class="text-center">
                        <h6 class="fs-13 fw-bold mt-2">Yönetici İmza</h6>
                        <p class="fs-11 fw-semibold text-muted">......./......../............</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Makbuz Modalının Altına -->
    <div id="print-area" style="display:none"></div>
    <script>
        $(".printBTN").on("click", function() {
            // Yazdırılacak içeriği al
            const content = document.querySelector(".makbuz-icerik").outerHTML;

            // Yeni pencere oluştur
            const printWindow = window.open("", "_blank");

            // Pencere boşsa (popup engelleyici devredeyse)
            if (!printWindow) {
                alert("Yazdırma penceresi engellendi. Lütfen tarayıcınızda açılmasına izin verin.");
                return;
            }

            // Stil dosyalarını (CSS linklerini) bul ve yeni pencereye aktar
            let styles = "";
            document.querySelectorAll("link[rel='stylesheet'], style").forEach((node) => {
                styles += node.outerHTML;
            });

            // İçeriği yazdırma penceresine yaz
            printWindow.document.open();
            printWindow.document.write(`
        <!doctype html>
        <html lang="tr">
        <head>
            <meta charset="utf-8">
            <title>Makbuz</title>
            ${styles}
            <style>
                @page {
                    margin: 3mm;
                }
                body {
                    background: #fff;
                    padding: 6mm;
                    margin: 0;
                    color: #000;
                }
                .makbuz-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; }
                .makbuz-header .brand { display: flex; align-items: center; }
                .makbuz-header .brand .logo { height: 72px; width: auto; }
                .makbuz-header .brand .brand-text { display: inline-block; margin-left: 8px; }
                .doc-meta { margin-left: auto; text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 4px; align-self: flex-end; }
                .makbuz-header address, address { white-space: nowrap; }
                .modal-header, .modal-footer, .printBTN {
                    display: none !important;
                }
                table {
                    border-collapse: collapse;
                    width: 100%;
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 6px;
                }
            </style>
        </head>
        <body>
            ${content}
        </body>
        </html>
    `);
            printWindow.document.close();

            // Yazdırma penceresi tam yüklendikten sonra otomatik print et
            printWindow.onload = function() {
                printWindow.focus();
                printWindow.print();
                // setTimeout(() => {
                //     printWindow.close();
                // }, 500);
            };
        });

        $(".downloadBTN").on("click", function() {
            const encIdAttr = $(this).data("id");
            const encId = encIdAttr ? String(encIdAttr) : <?php echo json_encode($_GET['makbuz_id'] ?? null); ?>;
            if (!encId) {
                alert("Makbuz bulunamadı.");
                return;
            }
            const url = '/pages/dues/payment/export/makbuz_pdf.php?makbuz_id=' + encodeURIComponent(encId);
            window.open(url, "_blank");
        });
    </script>