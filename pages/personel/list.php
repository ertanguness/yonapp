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
                <a href="/personel-ekle" class="btn btn-primary route-link">
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
    <style>
       .main-content, .table-responsive {
            overflow-x: visible !important;
        }
        .dropdown-menu {
  position: absolute !important;
}

    </style>
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
                                            <th>Personel Tipi</th>
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
                                                        <?= htmlspecialchars($personel->personel_tipi ?? '-') ?>
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
                                                        <div class="dropdown">
                                                            <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <i class="feather feather-more-horizontal"></i>
                                                            </a>
                                                            <ul class="dropdown-menu" data-bs-popper="static">
                                                                <li>
                                                                    <a class="dropdown-item personel-gorev-ekle" href="javascript:void(0)" data-person-id="<?= htmlspecialchars($personel->id) ?>" data-person-name="<?= htmlspecialchars($personel->adi_soyadi ?? '-') ?>">
                                                                        <i class="feather feather-check-square me-3"></i>
                                                                        <span>Görev Ekle</span>
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item personel-izin-ekle" href="javascript:void(0)" data-person-id="<?= htmlspecialchars($personel->id) ?>" data-person-name="<?= htmlspecialchars($personel->adi_soyadi ?? '-') ?>">
                                                                        <i class="feather feather-calendar me-3"></i>
                                                                        <span>İzin Ekle</span>
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item personel-odeme-ekle" href="javascript:void(0)" data-person-id="<?= htmlspecialchars($personel->id) ?>" data-person-name="<?= htmlspecialchars($personel->adi_soyadi ?? '-') ?>">
                                                                        <i class="feather feather-credit-card me-3"></i>
                                                                        <span>Ödeme Ekle</span>
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item" href="personel-duzenle/<?= $enc_id ?>">
                                                                        <i class="feather feather-edit-3 me-3"></i>
                                                                        <span>Güncelle</span>
                                                                    </a>
                                                                </li>
                                                                <li class="dropdown-divider"></li>
                                                                <li>
                                                                    <a class="dropdown-item bg-danger text-white delete-personel" href="javascript:void(0)" data-personel-id="<?= htmlspecialchars($enc_id) ?>" data-personel-name="<?= htmlspecialchars($personel->adi_soyadi ?? 'Personel') ?>">
                                                                        <i class="feather feather-trash-2 me-3"></i>
                                                                        <span>Sil</span>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                  
                                           
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

    <div class="modal fade" id="personTaskModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content task-modal"></div>
        </div>


    </div>
    <div class="modal fade" id="personLeaveModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content leave-modal"></div>
        </div>

    </div>
    <div class="modal fade" id="personPaymentModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content payment-modal"></div>
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

                    const response = await fetch('/pages/personel/api/personInfoApi.php', {
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
                            /** tablo verilerini ajax ile yeniden yükle */
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

        $(document).on('click', '.personel-gorev-ekle', function() {
            var pid = $(this).data('personId');
            $('#personTaskModal').data('personId', pid);
            $.get('/pages/personel/modal/task_modal.php', {
                    id: 0
                })
                .done(function(html) {
                    $('#personTaskModal .task-modal').html(html);
                    $('#personTaskModal').modal('show');
                    $('.flatpickr').flatpickr({
                        dateFormat: 'd.m.Y',
                        locale: 'tr'
                    });
                    $('.select2').select2({
                        dropdownParent: $('#personTaskModal')
                    });
                })
                .fail(function() {
                    $('#personTaskModal .task-modal').html('<div class="p-3">İçerik yüklenemedi</div>');
                    $('#personTaskModal').modal('show');
                });
        });

        $(document).on('click', '#saveTaskBtn', function() {
            var form = $('#taskForm');
            var fd = new FormData(form[0]);
            fd.append('person_id', $('#personTaskModal').data('personId') || 0);
            $.ajax({
                url: '/pages/personel/api/taskApi.php',
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function(response) {
                    var data = {};
                    try {
                        data = JSON.parse(response);
                    } catch (e) {
                        data = {
                            status: 'error',
                            message: 'Geçersiz cevap'
                        };
                    }
                    var title = data.status == 'success' ? 'Başarılı' : 'Hata';
                    swal.fire({
                            title: title,
                            text: data.message,
                            icon: data.status,
                            confirmButtonText: 'Tamam'
                        })
                        .then(function() {
                            if (data.status === 'success') {
                                $('#personTaskModal').modal('hide');
                            }
                        });
                },
                error: function(xhr, status, err) {
                    console.error('AJAX error', status, err, xhr.responseText);
                    alert('İstek gönderilemedi. Konsolu kontrol edin.');
                }
            });
        });

        $(document).on('click', '.personel-izin-ekle', function() {
            var pid = $(this).data('personId');
            $('#personLeaveModal').data('personId', pid);
            $.get('/pages/personel/modal/leave_modal.php', {
                    id: 0
                })
                .done(function(html) {
                    $('#personLeaveModal .leave-modal').html(html);
                    $('#personLeaveModal').modal('show');
                    $('.flatpickr').flatpickr({
                        dateFormat: 'd.m.Y',
                        locale: 'tr'
                    });
                })
                .fail(function() {
                    $('#personLeaveModal .leave-modal').html('<div class="p-3">İçerik yüklenemedi</div>');
                    $('#personLeaveModal').modal('show');
                });
        });

        $(document).on('click', '#saveLeaveBtn', function() {
            var form = $('#leaveForm');
            var fd = new FormData(form[0]);
            fd.append('person_id', $('#personLeaveModal').data('personId') || 0);
            $.ajax({
                url: '/pages/personel/api/leaveApi.php',
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function(response) {
                    var data = {};
                    try {
                        data = JSON.parse(response);
                    } catch (e) {
                        data = {
                            status: 'error',
                            message: 'Geçersiz cevap'
                        };
                    }
                    var title = data.status == 'success' ? 'Başarılı' : 'Hata';
                    swal.fire({
                            title: title,
                            text: data.message,
                            icon: data.status,
                            confirmButtonText: 'Tamam'
                        })
                        .then(function() {
                            if (data.status === 'success') {
                                $('#personLeaveModal').modal('hide');
                            }
                        });
                },
                error: function(xhr, status, err) {
                    console.error('AJAX error', status, err, xhr.responseText);
                    alert('İstek gönderilemedi. Konsolu kontrol edin.');
                }
            });
        });

        $(document).on('click', '.personel-odeme-ekle', function() {
            var pid = $(this).data('personId');
            $('#personPaymentModal').data('personId', pid);
            $.get('/pages/personel/modal/payment_modal.php', {
                    id: 0
                })
                .done(function(html) {
                    $('#personPaymentModal .payment-modal').html(html);
                    $('#personPaymentModal').modal('show');
                    $(".flatpickr-input").flatpickr({
                        dateFormat: 'Y-m-d',
                        locale: 'tr'
                    });
                    $('.select2').select2({
                        dropdownParent: $('#personPaymentModal')
                    });
                })
                .fail(function() {
                    $('#personPaymentModal .payment-modal').html('<div class="p-3">İçerik yüklenemedi</div>');
                    $('#personPaymentModal').modal('show');
                });
        });

        $(document).on('click', '#savePaymentBtn', function() {
            var form = $('#paymentForm');
            var fd = new FormData(form[0]);
            fd.append('person_id', $('#personPaymentModal').data('personId') || 0);
            $.ajax({
                url: '/pages/personel/api/paymentApi.php',
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function(response) {
                    var data = {};
                    try {
                        data = JSON.parse(response);
                    } catch (e) {
                        data = {
                            status: 'error',
                            message: 'Geçersiz cevap'
                        };
                    }
                    var title = data.status == 'success' ? 'Başarılı' : 'Hata';
                    swal.fire({
                            title: title,
                            text: data.message,
                            icon: data.status,
                            confirmButtonText: 'Tamam'
                        })
                        .then(function() {
                            if (data.status === 'success') {
                                $('#personPaymentModal').modal('hide');
                            }
                        });
                },
                error: function(xhr, status, err) {
                    console.error('AJAX error', status, err, xhr.responseText);
                    alert('İstek gönderilemedi. Konsolu kontrol edin.');
                }
            });
        });
    </script>