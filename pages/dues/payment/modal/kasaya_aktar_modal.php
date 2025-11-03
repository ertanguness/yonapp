 <?php

    require_once dirname(__DIR__, levels: 4) . '/configs/bootstrap.php';

    use App\Helper\Security;
    use App\Helper\Helper;
    use Model\DefinesModel;
    use Model\TahsilatHavuzuModel;

    $TahsilatHavuzu = new TahsilatHavuzuModel();
    $Tanimlamalar = new DefinesModel();

    $enc_id = $_GET['id'];
    $id = Security::decrypt($_GET['id'] ?? 0);

    $tahsilat = $TahsilatHavuzu->find($id);


    $type = ($tahsilat->tahsilat_tutari >= 0) ? 'Gelir' : 'Gider';
    $type_code = ($tahsilat->tahsilat_tutari >= 0) ? 6 : 7;

    /* Gelir/Gider Tipi Seçimi */







    ?>


 <div class="modal-header">
     <h5 class="modal-title" id="modalTitleId">Kasaya <?php echo $type; ?> Olarak İşle</h5>
     <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
 </div>
 <div class="modal-body">
<form action="" id="kasayaAktarForm">
    <input type="hidden" name="id" class="form-control" value="<?php echo $enc_id; ?>">

    <div class="row mb-4">
        <div class="col-lg-2 fw-medium">İşlem Tarihi</div>
         <div class="col-lg-10 hstack gap-1">
             <a href="javascript:void(0);" class="hstack gap-2">
                 <div class="avatar-text avatar-sm">
                     <i class="feather-calendar"></i>
                 </div>
                 <span><?php echo $tahsilat->islem_tarihi; ?></span>
             </a>
         </div>
     </div>

     <div class="row mb-4">
         <div class="col-lg-2 fw-medium">Açıklama</div>
         <div class="col-lg-10 hstack gap-1">
             <a href="javascript:void(0);" class="hstack gap-2">
                 <div class="avatar-text avatar-sm">
                     <i class="feather-file"></i>
                 </div>
                 <span><?php echo $tahsilat->ham_aciklama; ?></span>
             </a>
         </div>
     </div>

     <div class="row mb-4">
         <div class="col-lg-2 fw-medium">Tutar</div>
         <div class="col-lg-10 hstack gap-1">
             <a href="javascript:void(0);" class="hstack gap-2">
                 <div class="avatar-text avatar-sm">
                     <i class="feather-dollar-sign"></i>
                 </div>
                 <span><?php echo Helper::formattedMoney($tahsilat->tahsilat_tutari); ?></span>
             </a>
         </div>
     </div>
     <div class="row mb-4">
         <div class="col-lg-2 fw-medium">Kategori</div>
         <div class="col-lg-6 hstack gap-1">
             <a href="javascript:void(0);" class="hstack gap-2">
                 <div class="avatar-text avatar-sm">
                     <i class="feather-briefcase"></i>
                 </div>
             </a>
             <?php echo $Tanimlamalar->getGelirGiderTipiSelect("gelir_gider_tipi", $type_code,2); ?>
         </div>
     </div>

    </form>

 </div>
 <div class="modal-footer">
     <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
     <button type="button" class="btn btn-primary" id="kasayaKaydetBtn">Kaydet</button>
 </div>