<?php
$type = isset($type) ? $type : 'info';
?>

<div class="alert alert-<?php echo $type; ?> bg-white alert-dismissible mt-0" role="alert">
    <div class="d-flex">
        <div>
            <i class="feather feather-alert-octagon fs-1"></i>
        </div>
        <div class="ms-2">
            <h4 class="alert-title">
                <?php echo $title; ?>
            </h4>
            <div class="text-secondary">
                <?php echo $text; ?>
            </div>
        </div>
    </div>
    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
</div>

<div class="position-fixed top-0 end-0 p-3" style="z-index: 1050">
    <div id="warningToast" class="toast bg-danger text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-danger text-white">
            <strong class="me-auto"><i class="feather-alert-circle"></i> Uyarı</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            Lütfen tüm zorunlu alanları doldurun.
        </div>
    </div>
</div>