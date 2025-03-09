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