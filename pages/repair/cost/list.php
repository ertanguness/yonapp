<?php

use App\Helper\Security;
use Model\BakimMaliyetModel;
use Model\BakimModel;
use Model\UserModel;
use App\Helper\Helper;

$BakimMaliyet = new BakimMaliyetModel();
$Kullanıcılar = new UserModel();

$Maliyetler = $BakimMaliyet->MaliyetKayitlari();
?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Maliyet ve Faturalandırma</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Maliyet Takip</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-header-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Geri</span>
                </a>
            </div>
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <?php
                require_once 'pages/components/download.php';
                ?>
                <a href="#" class="btn btn-primary route-link" data-page="repair/cost/manage">
                    <i class="feather-plus me-2"></i>
                    <span>Yeni İşlem Ekle</span>
                </a>
            </div>

        </div>
    </div>
</div>

<div class="main-content">
    <?php
    $title = "Maliyet ve Faturalandırma";
    $text = "Bu modülde bakım işlemlerinin maliyetlerini ve faturalarını takip edebilirsiniz. 
             Burada işlem yapabilmeniz için Bakım veya Arıza kaydı oluşturmuş olmanız gerekmektedir.";
    require_once 'pages/components/alert.php';
    ?>

    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover datatables" id="MaliyetList">
                                    <thead>
                                        <tr class="text-center">
                                            <th>Sıra</th>
                                            <th>Bakım Türü</th>
                                            <th>Talep No</th>
                                            <th>Toplam Maliyet (₺)</th>
                                            <th>Ödenen Tutar (₺)</th>
                                            <th>Kalan Borç (₺)</th>
                                            <th>Fatura Durumu</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        foreach ($Maliyetler as $item):
                                            $enc_id = Security::encrypt($item->id);
                                        ?>
                                            <tr class="text-center">
                                                <td><?php echo $i; ?></td>
                                                <td>
                                                    <?php
                                                    echo isset(Helper::bakimTuru[$item->bakim_turu])
                                                        ? htmlspecialchars(Helper::bakimTuru[$item->bakim_turu])
                                                        : htmlspecialchars($item->bakim_turu);
                                                    ?>
                                                </td>
                                                <?php
                                                // TalepNoBul fonksiyonunu çağırarak talep_no değerini al
                                                $talepNo = $BakimMaliyet->TalepNoBul($item->talep_no, $item->bakim_turu);
                                                echo '<td>' . htmlspecialchars($talepNo ?? '') . '</td>';
                                                ?>
                                                <td><?php echo htmlspecialchars($item->toplam_maliyet); ?></td>
                                                <td><?php echo htmlspecialchars($item->odenen_tutar); ?></td>
                                                <td>
                                                    <?php
                                                    if ($item->kalan_borc > 0) {
                                                        echo '<span class="text-danger fw-bold"><i class="feather-alert-circle"></i> ' . htmlspecialchars($item->kalan_borc) . '</span>';
                                                    } else {
                                                        echo '<span class="text-success fw-bold"><i class="feather-check-circle"></i> 0</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $makbuzSayisi = $BakimMaliyet->MakbuzSayisi($item->id);
                                                    if ($makbuzSayisi > 0) {
                                                        echo '<span class="text-info"><i class="feather-check-circle"></i> Yüklenmiş ' . $makbuzSayisi . ' Makbuz var</span>';
                                                    } else {
                                                        echo '<span class="text-secondary"><i class="feather-clock"></i> Bekliyor</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <div class="hstack gap-2">
                                                        <a href="javascript:void(0);"
                                                            class="avatar-text avatar-md makbuz-goruntule"
                                                            data-id="<?php echo $enc_id; ?>"
                                                            title="Makbuzu Görüntüle">
                                                            <i class="feather-file-text"></i>
                                                        </a>

                                                        <a href="index?p=repair/cost/manage&id=<?php echo $enc_id; ?>" class="avatar-text avatar-md">
                                                            <i class="feather-edit"></i>
                                                        </a>
                                                        <a href="javascript:void(0);"
                                                            data-name="<?php echo htmlspecialchars($talepNo); ?>"
                                                            data-id="<?php echo $enc_id; ?>"
                                                            class="avatar-text avatar-md sil-maliyet">
                                                            <i class="feather-trash-2"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php
                                            $i++;
                                        endforeach;
                                        ?>
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
<!-- Makbuz Modal başlangıç -->
<!-- Tablo ve modal html kodların önceden senin verdiğin gibi olsun -->

<!-- Makbuz Modal -->
<div class="modal fade" id="makbuzModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" id="makbuzModalContent">
            <!-- İçerik Ajax ile yüklenecek -->
        </div>
    </div>
</div>

<script>
    // Modal açma ve ajax içerik yükleme
    document.addEventListener("DOMContentLoaded", function() {
        const buttons = document.querySelectorAll(".makbuz-goruntule");

        buttons.forEach(btn => {
            btn.addEventListener("click", function() {
                const id = this.dataset.id;

                fetch("pages/repair/cost/makbuzModal.php?id=" + id)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById("makbuzModalContent").innerHTML = html;
                        new bootstrap.Modal(document.getElementById('makbuzModal')).show();
                    })
                    .catch(() => {
                        alert("Makbuz yüklenemedi.");
                    });
            });
        });
    });

    // previewMakbuz fonksiyonunu şu şekilde güncelle:

    let currentPreviewId = null;

    function previewMakbuz(url, ext, idAttr) {
        const previewArea = document.getElementById("makbuz-preview-area");
        const previewContent = document.getElementById("makbuz-preview-content");

        if (currentPreviewId === idAttr) {
            // Aynı makbuz seçilmiş, kapatıyoruz
            previewArea.style.display = 'none';
            previewContent.innerHTML = '';
            currentPreviewId = null;
            return;
        }

        currentPreviewId = idAttr;
        previewArea.style.display = 'block';

        let contentHtml = '';

        if (["jpg", "jpeg", "png", "gif"].includes(ext.toLowerCase())) {
            contentHtml = `<img src="${url}" alt="Makbuz Görseli" class="img-fluid rounded">`;
        } else if (ext.toLowerCase() === "pdf") {
            contentHtml = `<embed src="${url}" type="application/pdf" width="100%" height="600px" />`;
        } else {
            contentHtml = `<p class="text-muted">Bu dosya türü desteklenmiyor. <a href="${url}" target="_blank" rel="noopener noreferrer">İndir</a></p>`;
        }

        previewContent.innerHTML = `<div id="${idAttr}">${contentHtml}</div>`;

        setTimeout(() => {
            const element = document.getElementById(idAttr);
            if (element) {
                element.scrollIntoView({
                    behavior: "smooth",
                    block: "start"
                });
            }
        }, 100);
    }
</script>

<!-- Makbuz Modal bitiş -->