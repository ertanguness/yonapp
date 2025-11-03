    <!--! BEGIN: [modal-header] !-->
    <div class="modal-header">
        <h2 class="d-flex flex-column mb-0">
            <span class="fs-18 fw-bold mb-1">Mail Gönder</span>
            <small class="d-block fs-11 fw-normal text-muted">Mesajınızı Oluşturun</small>
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
                <a href="javascript:void(0)" id="SendMail" data-bs-toggle="dropdown" data-bs-offset="0, 0">
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