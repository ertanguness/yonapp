<?php 

use Model\PersonelModel;
use App\Helper\Security;

$Personel = new PersonelModel();

$personelList = $Personel->getPersonel();

?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Personeller</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Personeller Takip</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-header-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Back</span>
                </a>
            </div>
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <?php
                require_once 'pages/components/search.php';
                require_once 'pages/components/download.php';
                ?>
                <a href="/personel-ekle" class="btn btn-primary route-link" >
                    <i class="feather-plus me-2"></i>
                    <span>Yeni Personel Ekle</span>
                </a>
            </div>
        </div>
        <div class="d-md-none d-flex align-items-center">
            <a href="javascript:void(0)" class="page-header-right-open-toggle">
                <i class="feather-align-right fs-20"></i>
            </a>
        </div>
    </div>
</div>

<div class="main-content">
    <?php
    $title = "Personel Listesi";
    $text = "Sistemde kayıtlı personelleri görüntüleyebilir, yeni personel ekleyebilir, düzenleyebilir veya silebilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover datatables" id="personnelList">
                                    <thead>
                                        <tr class="text-center">
                                            <th>Sıra</th>
                                            <th>Adı Soyadı</th>
                                            <th>TC Kimlik No</th>
                                            <th>Pozisyon</th>
                                            <th>Telefon</th>
                                            <th>E-Posta</th>
                                            <th>Durumu</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($personelList)): ?>
                                            <?php foreach ($personelList as $index => $personel): ?>
                                                <?php $enc_id = Security::encrypt($personel->id); ?>
                                                <tr>
                                                    <td class="text-center"><?= $index + 1 ?></td>
                                                    <td>
                                                        <div class="fw-bold"><?= htmlspecialchars($personel->adi_soyadi ?? '-') ?></div>
                                                    </td>
                                                    <td>
                                                        <?= htmlspecialchars($personel->tc_kimlik_no ?? '-') ?>
                                                    </td>
                                                    <td>
                                                        <?= htmlspecialchars($personel->gorev_yeri ?? '-') ?>
                                                    </td>
                                                    <td>
                                                        <?= htmlspecialchars($personel->telefon ?? '-') ?>
                                                    </td>
                                                    <td>
                                                        <?= htmlspecialchars($personel->eposta ?? '-') ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($personel->durum === 'Aktif' || $personel->durum === '1'): ?>
                                                            <span class="badge bg-success">Aktif</span>
                                                        <?php elseif ($personel->durum === 'Pasif' || $personel->durum === '0'): ?>
                                                            <span class="badge bg-danger">Pasif</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary"><?= htmlspecialchars($personel->durum) ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="hstack gap-2">
                                                            <a href="?p=persons/manage&id=<?= htmlspecialchars($personel->id) ?>" class="avatar-text avatar-md" title="Görüntüle">
                                                                <i class="feather-eye"></i>
                                                            </a>
                                                            <a href="personel-duzenle/<?= $enc_id ?>" class="avatar-text avatar-md" title="Düzenle">
                                                                <i class="feather-edit"></i>
                                                            </a>
                                                            <a href="javascript:void(0);" class="avatar-text avatar-md delete-personel" title="Sil" 
                                                               data-personel-id="<?= htmlspecialchars($personel->id) ?>" 
                                                               data-personel-name="<?= htmlspecialchars($personel->adi_soyadi ?? 'Personel') ?>">
                                                                <i class="feather-trash-2"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">
                                                    <i class="feather-inbox me-2"></i>Henüz personel kaydı yok
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Personel silme işlemi
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-personel')) {
            const button = e.target.closest('.delete-personel');
            const personelId = button.dataset.personelId;
            const personelName = button.dataset.personelName;
            
            // SweetAlert ile onay diyaloğu
            Swal.fire({
                title: 'Emin misiniz?',
                html: `<strong>${personelName}</strong> adlı personeli silmek istediğinize emin misiniz?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Evet, Sil',
                cancelButtonText: 'İptal'
            }).then((result) => {
                if (result.isConfirmed) {
                    deletePersonel(personelId);
                }
            });
        }
    });

    async function deletePersonel(personelId) {
        try {
            const formData = new FormData();
            formData.append('action', 'delete_personel');
            formData.append('personel_id', personelId);
            
            const response = await fetch('/pages/persons/api.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {
                Swal.fire({
                    title: 'Silindi!',
                    text: 'Personel başarıyla silindi.',
                    icon: 'success',
                    timer: 1500,
                    timerProgressBar: true
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Hata!',
                    text: data.message || 'Silme işlemi başarısız',
                    icon: 'error'
                });
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                title: 'Hata!',
                text: 'İşlem sırasında hata oluştu',
                icon: 'error'
            });
        }
    }
});
</script>
