<div class="card-body blocks-info">
    <div class="row mb-4 align-items-center">
    <input type="hidden" name="site_id" id="site_id" value="<?php echo $site->id ?? ''; ?>">

        <div class="col-lg-2 d-flex align-items-center ">
            <label class="fw-semibold text-center">Blok Adı:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="feather-trello"></i></div>
                <input type="text" class="form-control block-name" name="block_names[]" placeholder="Blok Adı Giriniz" value="<?php echo $blok->blok_adi ?? ''; ?>"> 
            </div>
        </div>
        <div class="col-lg-2 d-flex align-items-center ">
            <label class="fw-semibold ">Bağımsız Bölüm Sayısı:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="feather-layers"></i></div>
                <input type="text" class="form-control apartment-count" name="apartment_counts[]" placeholder="Bağımsız Bölüm Sayısı Giriniz" min="1" required onkeypress="return event.charCode >= 48 && event.charCode <= 57" value="<?php echo $blok->daire_sayisi ?? ''; ?>">
            </div>
        </div>
    </div>

</div>

