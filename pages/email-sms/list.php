<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Gönderilen Bildirimler</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Bildirimler</li>
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
                // require_once 'pages/components/search.php';
                require_once 'pages/components/download.php';
                ?>

                <a href="javascript:void(0)" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#composeMail">
                    <i class="feather-mail me-2"></i>
                    <span>Yeni Email</span>
                </a>
                <a href="#" class="btn btn-simple btn-secondary sms-gonder">
                    <i class="feather-smartphone me-2"></i>
                    <span>Yeni Sms</span>
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
    $title = "Gönderilen Bildirimler";
    $text = "Gönderilen SMS ve e-posta bildirimlerinizi görüntüleyebilir, detaylarına ulaşabilir ve filtreleme işlemleri yapabilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover datatables" id="notificationsList">
                                    <thead>
                                        <tr class="text-center">
                                            <th>Sıra</th>
                                            <th>Bildirim Türü</th>
                                            <th>Kime Gönderildi</th>
                                            <th>Konu Başlığı</th>
                                            <th>Mesaj</th>
                                            <th>Gönderim Tarihi</th>
                                            <th>Durum</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Veritabanından dinamik olarak doldurulacak -->
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


<!-- list.php'nin en altına ekle -->
<script src="/pages/email-sms/js/sms.js"></script>
<!--! BEGIN: Compose Mail Modal !-->
<!--! ================================================================ !-->
<div class="modal fade-scale" id="composeMail" tabindex="-1" aria-labelledby="composeMail" aria-hidden="true" data-bs-dismiss="ou">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <!--! BEGIN: [modal-header] !-->
            <div class="modal-header">
                <h2 class="d-flex flex-column mb-0">
                    <span class="fs-18 fw-bold mb-1">Compose Mail</span>
                    <small class="d-block fs-11 fw-normal text-muted">Compose Your Message</small>
                </h2>
                <a href="javascript:void(0)" class="avatar-text avatar-md bg-soft-danger close-icon" data-bs-dismiss="modal">
                    <i class="feather-x text-danger"></i>
                </a>
            </div>
            <!--! BEGIN: [modal-body] !-->
            <div class="modal-body p-0">
                <div class="position-relative border-bottom">
                    <div class="px-2 d-flex align-items-center">
                        <div class="p-0 w-100">
                            <input class="form-control border-0 text-dark" name="tomailmodal" placeholder="TO">
                        </div>
                    </div>
                    <a href="javascript:void(0)" class="position-absolute top-50 end-0 translate-middle badge bg-gray-100 border border-gray-3 fs-10 fw-semibold text-uppercase text-dark rounded-pill c-pointer z-index-100" id="ccbccToggleModal"><span data-bs-toggle="tooltip" data-bs-trigger="hover" title="CC / BCC" style="font-size: 9px !important">CC / BCC</span></a>
                </div>
                <div class="border-bottom mail-cc-bcc-fields" id="ccbccToggleModalFileds" style="display: none">
                    <div class="px-2 w-100 d-flex align-items-center border-bottom">
                        <input class="form-control border-0 text-dark" name="ccmailmodal" placeholder="CC">
                    </div>
                    <div class="px-2 w-100 d-flex align-items-center">
                        <input class="form-control border-0 text-dark" name="bccmailmodal" placeholder="BCC">
                    </div>
                </div>
                <div class="px-3 w-100 d-flex align-items-center">
                    <input class="form-control border-0 my-1 w-100 shadow-none" type="email" placeholder="Subject">
                </div>
                <div class="editor w-100 m-0">
                    <div class="ht-300 border-bottom-0" id="mailEditorModal"></div>
                </div>
            </div>
            <!--! BEGIN: [modal-footer] !-->
            <div class="modal-footer d-flex align-items-center justify-content-between">
                <!--! BEGIN: [mail-editor-action-left] !-->
                <div class="d-flex align-items-center">
                    <div class="dropdown me-2">
                        <a href="javascript:void(0)" data-bs-toggle="dropdown" data-bs-offset="0, 0">
                            <span class="btn btn-primary dropdown-toggle" data-bs-toggle="tooltip" data-bs-trigger="hover" title="Send Message"> Send </span>
                        </a>
                        <div class="dropdown-menu">
                            <a href="javascript:void(0)" class="dropdown-item" data-action-target="#mailActionMessage">
                                <i class="feather-send me-3"></i>
                                <span>Instant Send</span>
                            </a>
                            <a href="javascript:void(0);" class="dropdown-item successAlertMessage">
                                <i class="feather-clock me-3"></i>
                                <span>Schedule Send</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="javascript:void(0)" class="dropdown-item successAlertMessage">
                                <i class="feather-x me-3"></i>
                                <span>Discard Now</span>
                            </a>
                            <a href="javascript:void(0)" class="dropdown-item successAlertMessage">
                                <i class="feather-edit-3 me-3"></i>
                                <span>Save as Draft</span>
                            </a>
                        </div>
                    </div>
                    <div class="dropdown me-2 d-none d-sm-block">
                        <a href="javascript:void(0)" data-bs-toggle="dropdown" data-bs-offset="0, 0">
                            <span class="btn btn-icon" data-bs-toggle="tooltip" data-bs-trigger="hover" title="Pick Template">
                                <i class="feather-hash"></i>
                            </span>
                        </a>
                        <div class="dropdown-menu wd-300">
                            <a href="javascript:void(0)" class="dropdown-item">
                                <i class="feather-file-text me-3"></i>
                                <span>Welcome you message</span>
                            </a>
                            <a href="javascript:void(0)" class="dropdown-item">
                                <i class="feather-file-text me-3"></i>
                                <span>Your issues solved</span>
                            </a>
                            <a href="javascript:void(0)" class="dropdown-item">
                                <i class="feather-file-text me-3"></i>
                                <span>Thank you message</span>
                            </a>
                            <a href="javascript:void(0)" class="dropdown-item">
                                <i class="feather-file-text me-3"></i>
                                <span>Make a offer message</span>
                            </a>
                            <a href="javascript:void(0)" class="dropdown-item">
                                <i class="feather-file-text me-3"></i>
                                <span>Add the Unsubscribe option</span>
                            </a>
                            <a href="javascript:void(0)" class="dropdown-item">
                                <i class="feather-file-text me-3"></i>
                                <span>Thank your customer for joining</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="javascript:void(0)" class="dropdown-item">
                                <i class="feather-save me-3"></i>
                                <span>Save as Template</span>
                            </a>
                            <a href="javascript:void(0)" class="dropdown-item">
                                <i class="feather-sun me-3"></i>
                                <span>Manage Template</span>
                            </a>
                        </div>
                    </div>
                    <div class="dropdown">
                        <a href="javascript:void(0)" data-bs-toggle="dropdown" data-bs-offset="0, 0">
                            <span class="btn btn-icon" data-bs-toggle="tooltip" data-bs-trigger="hover" title="Upload Attachments">
                                <i class="feather-upload"></i>
                            </span>
                        </a>
                        <div class="dropdown-menu">
                            <a href="javascript:void(0)" class="dropdown-item">
                                <i class="feather-image me-3"></i>
                                <span>Upload Images</span>
                            </a>
                            <a href="javascript:void(0)" class="dropdown-item">
                                <i class="feather-video me-3"></i>
                                <span>Upload Videos</span>
                            </a>
                            <a href="javascript:void(0)" class="dropdown-item">
                                <i class="feather-mic me-3"></i>
                                <span>Upload Musics</span>
                            </a>
                            <a href="javascript:void(0)" class="dropdown-item">
                                <i class="feather-file-text me-3"></i>
                                <span>Upload Documents</span>
                            </a>
                        </div>
                    </div>
                </div>
                <!--! BEGIN: [mail-editor-action-right] !-->
                <div class="d-flex align-items-center">
                    <div class="dropdown me-2">
                        <a href="javascript:void(0)" data-bs-toggle="dropdown" data-bs-offset="0, 0">
                            <span class="btn btn-icon" data-bs-toggle="tooltip" data-bs-trigger="hover" title="Editing Actions">
                                <i class="feather-more-horizontal"></i>
                            </span>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="javascript:void(0)" class="dropdown-item">
                                    <i class="feather-type me-3"></i>
                                    <span>Plain Text Mode</span>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" class="dropdown-item">
                                    <i class="feather-check me-3"></i>
                                    <span>Check Spelling</span>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" class="dropdown-item">
                                    <i class="feather-compass me-3"></i>
                                    <span>Smart Compose</span>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" class="dropdown-item">
                                    <i class="feather-feather me-3"></i>
                                    <span>Manage Signature</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <a href="javascript:void(0);" data-bs-dismiss="modal">
                        <span class="btn btn-icon" data-bs-toggle="tooltip" data-bs-trigger="hover" title="Delete Message">
                            <i class="feather-x"></i>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<!--! ================================================================ !-->
<!--! END: Compose Mail Modal !-->

<script>
    
    /** SMS Gönderme Modalini Aç */
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
                }
            }, 100);
        });
    });
</script>