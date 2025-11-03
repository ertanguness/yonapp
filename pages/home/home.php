<?php

use App\Helper\Helper;
use Model\BorclandirmaModel;
use Model\FinansalRaporModel;


$BorclandirmaModel = new BorclandirmaModel();
$FinansalRaporModel = new FinansalRaporModel();

$site_id = $_SESSION['site_id'];

$toplam_aidat_geliri = $FinansalRaporModel->getToplamAidatGeliri($site_id);
$geciken_tahsilat_sayisi = $FinansalRaporModel->getGecikenTahsilatSayisi($site_id);
$toplam_gider = $FinansalRaporModel->getToplamGiderler($site_id);
$geciken_odeme_tutari = $FinansalRaporModel->getGecikenOdemeTutar($site_id);





?>




<div class="main-content mb-5">
    <style>
        .flex-fill {
            transition: background-color 0.3s ease-in-out;
            display: inline-block;
            /* Satırı tamamen kaplamasını engeller */
            align-items: center;
            /* İçerikleri ortalar */
            text-align: center;
            /* Metni ortalar */
            margin-bottom: 4px;
        }

        .flex-fill:hover {
            background-color: #f8f9fa;
            /* Hover durumunda arka plan rengi değişir */
        }

        .flex-fill i {
            font-size: 24px;
            /* İkon boyutunu artırır */
        }
    </style>
    <div class="col-xxl-12">
        <div class="card stretch stretch-full">
            <div class="card-header">
                <h5 class="card-title">Hızlı İşlemler</h5>
            </div>
            <div class="card-body">


                <a href="site-ekle"
                    class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5">
                    <i class="bi bi-diagram-3"></i>
                    <p class="fs-12 text-muted mb-0">Site Ekle</p>
                </a>
                <a href="blok-ekle"
                    class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5">
                    <i class="bi bi-building"></i>
                    <p class="fs-12 text-muted mb-0">Blok Ekle</p>
                </a>
                <a href="daire-ekle"
                    class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5">
                    <i class="bi bi-textarea"></i>
                    <p class="fs-12 text-muted mb-0">Daire Ekle</p>
                </a>
                <a href="/site-sakini-ekle"
                    class="flex-fill py-3 px-4 me-4 rounded-1 cursor-pointer border border-dashed border-gray-5">
                    <i class="feather-user-plus"></i>
                    <p class="fs-12 text-muted mb-0">Kişi Ekle</p>
                </a>


                <a href="#"
                    class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5 gelir-ekle">
                    <i class="bi bi-credit-card"></i>
                    <p class="fs-12 text-muted mb-0">Gelir Ekle</p>
                </a>
                <a href="#" class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5 gider-ekle">
                    <i class="bi bi-credit-card-2-back"></i>
                    <p class="fs-12 text-muted mb-0">Gider Ekle</p>
                </a>
                <a href="/gelir-gider-islemleri"
                    class="flex-fill py-3 px-4 me-4 rounded-1 cursor-pointer border border-dashed border-gray-5">
                    <i class="bi bi-wallet2"></i>
                    <p class="fs-12 text-muted mb-0">Finansal İşlemler</p>
                </a>


                <a href="/aidat-turu-tanimlama" class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5">
                    <i class="bi bi-folder-plus"></i>
                    <p class="fs-12 text-muted mb-0">Aidat Tanımla</p>
                </a>
                <a href="/borclandirma-yap" class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5">
                    <i class="bi bi-clipboard-plus"></i>
                    <p class="fs-12 text-muted mb-0">Borçlandırma Yap</p>
                </a>
                <a href="/yonetici-aidat-odeme"
                    class="flex-fill py-3 px-4 me-4 rounded-1 cursor-pointer border border-dashed border-gray-5">
                    <i class="bi bi-person-workspace"></i>
                    <p class="fs-12 text-muted mb-0">Yönetici Aidat Ödeme</p>
                </a>


                <a href="#" class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5 mail-gonder">
                    <i class="bi bi-envelope"></i>
                    <p class="fs-12 text-muted mb-0">Email Gönder</p>
                </a>
                <a href="#" class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5 sms-gonder">
                    <i class="bi bi-send-plus"></i>
                    <p class="fs-12 text-muted mb-0">Sms Gönder</p>
                </a>




            </div>

        </div>
    </div>

    <div class="row row-cards">
        <!-- [Mini Card] start -->
        <div class="col-xxl-3 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="hstack justify-content-between">
                        <div>
                            <h4 class="text-success"><?php echo Helper::formattedMoney($toplam_aidat_geliri); ?></h4>
                            <div class="text-muted">Toplam Aidat Geliri</div>
                        </div>
                        <div class="text-end">
                            <i class="feather-credit-card fs-2"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-success py-3">
                    <div class="hstack justify-content-between">
                        <p class="text-white mb-0">+5% artış</p>
                        <div class="text-end">
                            <i class="feather-trending-up text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="hstack justify-content-between">
                        <div>
                            <h4 class="text-danger"><?php echo Helper::formattedMoney($geciken_odeme_tutari); ?></h4>
                            <div class="text-muted">Gecikmiş Ödemeler</div>
                        </div>
                        <div class="text-end">
                            <i class="feather-alert-triangle fs-2"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-danger py-3">
                    <div class="hstack justify-content-between">
                        <p class="text-white mb-0">+2.5% artış</p>
                        <div class="text-end">
                            <i class="feather-trending-up text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="hstack justify-content-between">
                        <div>
                            <h4 class="text-warning"><?php echo Helper::formattedMoney($toplam_gider); ?></h4>
                            <div class="text-muted">Toplam Giderler</div>
                        </div>
                        <div class="text-end">
                            <i class="feather-dollar-sign fs-2"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-warning py-3">
                    <div class="hstack justify-content-between">
                        <p class="text-white mb-0">-1.2% azalma</p>
                        <div class="text-end">
                            <i class="feather-trending-down text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="hstack justify-content-between">
                        <div>
                            <h4 class="text-danger"><?php echo Helper::formattedMoney($geciken_tahsilat_sayisi); ?></h4>
                            <div class="text-muted">Gecikmiş Aidat Sayısı</div>
                        </div>
                        <div class="text-end">
                            <i class="feather-alert-circle fs-2"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-info py-3">
                    <div class="hstack justify-content-between">
                        <p class="text-white mb-0">Sabit</p>
                        <div class="text-end">
                            <i class="feather-minus text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- [Mini Card] end -->
    </div>
    <!-- Takvim -->
    <div class="card stretch stretch-full">
        <div class="apps-container apps-calendar">
            <div class="nxl-content without-header nxl-full-content">
                <!-- [ Main Content ] start -->
                <div class="main-content d-flex">
                    <!-- [ Content Sidebar ] start -->
                    <div class="content-sidebar content-sidebar-md" data-scrollbar-target="#psScrollbarInit">
                        <div class="content-sidebar-header bg-white sticky-top hstack justify-content-between">
                            <h4 class="fw-bolder mb-0">Etkinlik Takvimi</h4>
                            <a href="javascript:void(0);" class="app-sidebar-close-trigger d-flex">
                                <i class="feather-x"></i>
                            </a>
                        </div>
                        <div class="content-sidebar-header">
                            <a href="javascript:void(0);" id="btn-new-schedule" class="btn btn-primary w-100"
                                data-toggle="modal">
                                <i class="feather-calendar me-2"></i>
                                <span>Yeni Etkinlik</span>
                            </a>
                        </div>

                        <div class="content-sidebar-body">
                            <div id="lnb-calendars" class="lnb-calendars">
                                <div class="lnb-calendars-item">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="viewAllSchedules"
                                            value="all" checked="checked">
                                        <label class="custom-control-label c-pointer" for="viewAllSchedules">
                                            <span class="fs-13 fw-semibold lh-lg" style="margin-top: -2px">Tüm
                                                Etkinlikleri Göster</span>
                                        </label>
                                    </div>
                                </div>
                                <div id="calendarList" class="lnb-calendars-d1">
                                    <div class="lnb-calendars-item">
                                        <label><input type="checkbox" class="tui-full-calendar-checkbox-round" value="1"
                                                checked=""><span
                                                style="border-color: #5485e4; background-color: #5485e4"></span><span>Ofis</span></label>
                                    </div>
                                    <div class="lnb-calendars-item">
                                        <label><input type="checkbox" class="tui-full-calendar-checkbox-round" value="2"
                                                checked=""><span
                                                style="border-color: #25b865; background-color: #25b865"></span><span>Aile</span></label>
                                    </div>
                                    <div class="lnb-calendars-item">
                                        <label><input type="checkbox" class="tui-full-calendar-checkbox-round" value="3"
                                                checked=""><span
                                                style="border-color: rgb(209, 59, 76); background-color: rgb(209, 59, 76)"></span><span>Arkadaş</span></label>
                                    </div>
                                    <div class="lnb-calendars-item">
                                        <label><input type="checkbox" class="tui-full-calendar-checkbox-round" value="4"
                                                checked=""><span
                                                style="border-color: #17a2b8; background-color: #17a2b8"></span><span>Seyahat</span></label>
                                    </div>
                                    <div class="lnb-calendars-item">
                                        <label><input type="checkbox" class="tui-full-calendar-checkbox-round" value="5"
                                                checked=""><span
                                                style="border-color: #e49e3d; background-color: #e49e3d"></span><span>Özel</span></label>
                                    </div>
                                    <div class="lnb-calendars-item">
                                        <label><input type="checkbox" class="tui-full-calendar-checkbox-round" value="6"
                                                checked=""><span
                                                style="border-color: #5856d6; background-color: #5856d6"></span><span>Tatil</span></label>
                                    </div>
                                    <div class="lnb-calendars-item">
                                        <label><input type="checkbox" class="tui-full-calendar-checkbox-round" value="7"
                                                checked=""><span
                                                style="border-color: #3dc7be; background-color: #3dc7be"></span><span>Şirket</span></label>
                                    </div>
                                    <div class="lnb-calendars-item">
                                        <label><input type="checkbox" class="tui-full-calendar-checkbox-round" value="8"
                                                checked=""><span
                                                style="border-color: #475e77; background-color: #475e77"></span><span>Doğum
                                                Günleri</span></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ Content Sidebar  ] end -->
                    <!-- [ Main Area  ] start -->
                    <div class="content-area" data-scrollbar-target="#psScrollbarInit">
                        <div class="content-area-header sticky-top">
                            <div class="page-header-left d-flex align-items-center gap-2">

                                <div id="menu" class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex calendar-action-btn">
                                        <div class="dropdown me-1">
                                            <button id="dropdownMenu-calendarType"
                                                class="dropdown-toggle calendar-dropdown-btn" type="button"
                                                data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                                data-bs-offset="0,17">
                                                <i id="calendarTypeIcon"
                                                    class="feather-grid calendar-icon fs-12 me-1"></i>
                                                <span id="calendarTypeName">Görünüm</span>
                                            </button>
                                        </div>
                                        <div class="menu-navi d-none d-sm-flex">
                                            <button type="button" class="move-today" data-action="move-today">
                                                <i class="feather-clock calendar-icon me-1 fs-12"
                                                    data-action="move-today"></i>
                                                <span>Bugün</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="page-header-right ms-auto">
                                <div class="hstack gap-2">
                                    <div id="renderRange" class="render-range d-none d-sm-flex"></div>
                                    <div class="btn-group gap-1 menu-navi" role="group">
                                        <button type="button" class="avatar-text avatar-md move-day"
                                            data-action="move-prev">
                                            <i class="feather-chevron-left fs-12" data-action="move-prev"></i>
                                        </button>
                                        <button type="button" class="avatar-text avatar-md move-day"
                                            data-action="move-next">
                                            <i class="feather-chevron-right fs-12" data-action="move-next"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-area-body p-0">
                            <div id="tui-calendar-init"></div>
                        </div>

                        <!-- [ Footer ] end -->
                    </div>
                    <!-- [ Content Area ] end -->
                </div>
                <!-- [ Main Content ] end -->
            </div>
        </div>
    </div>

    <!-- [Takvim] end -->
    <!-- [Aylık Gelir Gider Tablosu] -->
    <div class="col-xxl-12">
        <div class="card stretch stretch-full">
            <div class="card-header">
                <h5 class="card-title">Yıllık Gelir-Gider Grafiği</h5>
                <div class="card-header-action">
                    <div class="card-header-btn">
                        <div data-bs-toggle="tooltip" title="Delete">
                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-danger"
                                data-bs-toggle="remove"> </a>
                        </div>
                        <div data-bs-toggle="tooltip" title="Refresh">
                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-warning"
                                data-bs-toggle="refresh"> </a>
                        </div>
                        <div data-bs-toggle="tooltip" title="Maximize/Minimize">
                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success"
                                data-bs-toggle="expand"> </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body custom-card-action">
                <div id="payment-records-chart"></div>
                <div class="d-none d-md-flex flex-wrap pt-4 border-top">
                    <div class="flex-fill">
                        <p class="fs-11 fw-medium text-uppercase text-muted mb-2">Toplam Gelir</p>
                        <h2 class="fs-20 fw-bold mb-0">$65,658 USD</h2>
                    </div>
                    <div class="vr mx-4 text-gray-600"></div>
                    <div class="flex-fill">
                        <p class="fs-11 fw-medium text-uppercase text-muted mb-2">Toplam Gider</p>
                        <h2 class="fs-20 fw-bold mb-0">$34,54 USD</h2>
                    </div>
                    <div class="vr mx-4 text-gray-600"></div>
                    <div class="flex-fill">
                        <p class="fs-11 fw-medium text-uppercase text-muted mb-2">Kar / Zarar</p>
                        <h2 class="fs-20 fw-bold mb-0">$20,478 USD</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- [Payment Records] end -->
    <!-- [Inquiry Channel] start -->
    <div class="col-xxl-12 mb-5">
        <div class="card stretch stretch-full">
            <div class="card-header">
                <h5 class="card-title">Yıllık Aidat Ödeme Grafiği</h5>
                <div class="card-header-action">
                    <div class="card-header-btn">
                        <div data-bs-toggle="tooltip" title="Delete">
                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-danger"
                                data-bs-toggle="remove"> </a>
                        </div>
                        <div data-bs-toggle="tooltip" title="Refresh">
                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-warning"
                                data-bs-toggle="refresh"> </a>
                        </div>
                        <div data-bs-toggle="tooltip" title="Maximize/Minimize">
                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success"
                                data-bs-toggle="expand"> </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body custom-card-action">
                <div id="leads-inquiry-channel"></div>
            </div>
        </div>
    </div>
    <hr>
    <!-- [Inquiry Channel] end -->
</div>


<div class="modal fade-scale" id="composeMail" tabindex="-1" aria-labelledby="composeMail" aria-hidden="true" data-bs-dismiss="ou">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">

        </div>
    </div>
</div>


<div class="modal fade" id="SendMessage" tabindex="-1" role="dialog" aria-labelledby="modalTitleId"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content sms-gonder-modal">
            <!-- İçerik AJAX ile yüklenecek -->
        </div>
    </div>
</div>


<!-- Gelir Gider Modal -->
<div class="modal fade" id="gelirGiderModal" tabindex="-1" aria-labelledby="gelirGiderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- İçerik AJAX ile yüklenecek -->
        </div>
    </div>
</div>

<!-- list.php'nin en altına ekle -->
<script src="/pages/email-sms/js/sms.js"></script>

<?php include './partials/calender-scripts.php' ?>

<script>
    /** Gelir Ekle Modalini açar */
    $(document).on('click', '.gelir-ekle, .gider-ekle', function() {
        var islem_tipi = $(this).hasClass('gelir-ekle') ? 'gelir' : 'gider';
        $.get("pages/home/modal/gelir_gider_modal.php", {
            islem_tipi: islem_tipi,
            includeFile: ''
        }, function(data) {
            // Gelen yanıtı işleyin (örneğin, bir modal açarak)
            $('#gelirGiderModal .modal-content').html(data);
            $('#gelirGiderModal').modal('show');

            $(".select2").select2({
                dropdownParent: $('#gelirGiderModal')
            });
        });


    });
   
    /** Mail Gönder Modalini açar */
    $(document).on('click', '.mail-gonder', function() {
        var kisi_id = $(this).data('kisi-id');

        $.get("pages/home/modal/mail_gonder_modal.php", {
            kisi_id,
            includeFile: ''
        }, function(html) {
            $('#composeMail .modal-content').html(html);
            const $modal = $('#composeMail').modal('show');

            // // Modal gösterildikten sonra yükle/çalıştır
            //  $modal.on('shown.bs.modal', function() {
            //     // apps-email-init dosyasını sadece bir kez yükle
            //     if (!window.__appsEmailInitLoaded) {
            // assets\vendors\js\tagify-data.min.js
            $.getScript('/pages/home/js/tagify-data.js')
                .done(function() {
                    console.log('tagify-data.min.js yüklendi.');
                    window.quillMailEditor = new Quill("#mailEditorModal", {
                        placeholder: "Compose an epic...@mention, #tag",
                        theme: "snow"
                    });

                })
            $.getScript('/assets/js/apps-email-init.min.js')
                .done(function() {
                    console.log('apps-email-init.min.js yüklendi.');

                })

            document.querySelectorAll('#composeMail [data-bs-toggle="tooltip"]').forEach(function(el) {
                bootstrap.Tooltip.getOrCreateInstance(el);
            });
            document.querySelectorAll('#composeMail [data-bs-toggle="dropdown"]').forEach(function(el) {
                bootstrap.Dropdown.getOrCreateInstance(el, {
                    popperConfig: {
                        strategy: 'fixed',
                        modifiers: [{
                                name: 'preventOverflow',
                                options: {
                                    boundary: 'viewport',
                                    altAxis: true
                                }
                            },
                            {
                                name: 'offset',
                                options: {
                                    offset: [0, 6]
                                }
                            }
                        ]
                    }
                });
            });

            // });
            // Modal içindeki select2’ler
            $(".select2").select2({
                dropdownParent: $('#composeMail')
            });
        });
    });

    /**Mail Göönder butonuna basınca */
    $(document).on('click', '#SendMail', function() {
        const toList = window.pickEmails?.(window.mailTagify?.to) || [];
        const ccList = window.pickEmails?.(window.mailTagify?.cc) || [];
        const bccList = window.pickEmails?.(window.mailTagify?.bcc) || [];

        // Tekrarları temizle ve çakışmaları ayıkla
        const dedup = arr => [...new Set(arr.map(e => e.toLowerCase()))];
        const to = dedup(toList);
        const cc = dedup(ccList).filter(e => !to.includes(e));
        const bcc = dedup(bccList).filter(e => !to.includes(e) && !cc.includes(e));

        // En az bir alıcı kontrolü
        if (!to.length && !cc.length && !bcc.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Alıcı yok',
                text: 'En az bir alıcı ekleyin.'
            });
            return;
        }

        const formData = new FormData();
        formData.append('action', 'email_gonder');
        formData.append('to', JSON.stringify(to));
        formData.append('cc', JSON.stringify(cc));
        formData.append('bcc', JSON.stringify(bcc));
        formData.append('subject', $('#composeMail input[placeholder="Subject"]').val());
        formData.append('message', window.quillMailEditor ? window.quillMailEditor.root.innerHTML : '');

        // //formdata ieriğini console'a yazdır
        // for(let pair of formData.entries()){
        //     console.log(`${pair[0]}: ${pair[1]}`);
        // }
        // return;



        let UrlEmail = "/pages/email-sms/api/APIEmail.php";
        fetch(UrlEmail, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log(data);
                let title = data.status === 'success' ? 'Başarılı!' : 'Hata!';
                Swal.fire({
                    title: title,
                    text: data.message,
                    icon: data.status === 'success' ? 'success' : 'error',
                    confirmButtonText: 'Tamam'
                });

            })
            .catch(error => {
                console.log(error);

                console.error('Error:', error);
                Swal.fire({
                    title: 'Hata!',
                    text: 'E-posta gönderilirken bir hata oluştu.',
                    icon: 'error',
                    confirmButtonText: 'Tamam'
                });
            });
        //console.log('E-postalar:', emailList);
        // ... gönderme işlemi


    });

    /**Sms Gönderme Modalini açar */
    $(document).on('click', '.sms-gonder', function() {
        var kisi_id = $(this).data('kisi-id');
        // SMS gönderme işlemini burada gerçekleştirin
        $.get("pages/email-sms/sms_gonder_modal.php", {
            kisi_id: kisi_id,
            includeFile: ''

        }, function(data) {
            // Gelen yanıtı işleyin (örneğin, bir modal açarak)
            $('#SendMessage .modal-content').html(data);
            $('#SendMessage').modal('show');


            setTimeout(function() {
                if (typeof window.initSmsModal === 'function') {
                    //$('#message').text('Merhaba, olarak size hatırlatmak isteriz ki, toplam borcunuz dir. Lütfen en kısa sürede ödeme yapınız.\n\nTeşekkürler.\nSite Yönetimi');
                    window.initSmsModal();
                    // Modal içindeki select2’ler
                    $(".select2").select2({
                        dropdownParent: $('#SendMessage')
                    });
                }
            }, 100);
        });
    });
</script>