<!-- Site Bilgileri -->
<div class="card-body personal-info mb-4">
    <h6 class="mb-3"><i class="fas fa-info-circle"></i> Site Bilgileri</h6>
    <div class="row mb-3 align-items-center">
        <div class="col-lg-2">
            <label for="siteAdi" class="fw-semibold">Site Adı:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text">
                    <i class="fas fa-home"></i>
                </div>
                <input type="text" class="form-control" id="siteAdi" name="siteAdi" value="<?php echo $SiteBilgileri->site_adi ?? ''; ?>"  readonly>
            </div>
        </div>

        <div class="col-lg-2">
            <label for="blokSayisi" class="fw-semibold">Blok Sayısı:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text">
                    <i class="fas fa-th-large"></i>
                </div>
                <input type="number" class="form-control" id="blokSayisi" name="blokSayisi" value="<?php echo $BlokSayisi['blok_sayisi'] ?? 0; ?>" readonly>
            </div>
        </div>
    </div>

    <div class="row mb-3 align-items-center">
        <div class="col-lg-2">
            <label for="daireSayisi" class="fw-semibold">Daire Sayısı:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text">
                    <i class="fas fa-building"></i>
                </div>
                <input type="number" class="form-control" id="daireSayisi" name="daireSayisi" value="<?php echo $BlokSayisi['toplam_daire'] ?? 0; ?>" readonly>
            </div>
        </div>

        <div class="col-lg-2">
            <label for="adres" class="fw-semibold">Adres:</label>
        </div>
        <div class="col-lg-4">
            <textarea class="form-control" id="adres" name="adres" rows="3" placeholder="Site adresini yazınız" readonly><?php echo $SiteBilgileri->tam_adres ?? ''; ?></textarea>
        </div>
    </div>
</div>

<!-- İletişim Bilgileri -->
<div class="card-body personal-info mb-4">
    <h6 class="mb-3"><i class="fas fa-phone-alt"></i> İletişim Bilgileri</h6>
    <div class="row mb-3 align-items-center">
        <div class="col-lg-2">
            <label for="eposta" class="fw-semibold">E-posta:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text">
                    <i class="fas fa-envelope"></i>
                </div>
                <input type="email" class="form-control" id="eposta" name="eposta" placeholder="E-posta adresi yazınız" value="<?php echo $AyarlarBilgileri->eposta ?? ''; ?>">
            </div>
        </div>

        <div class="col-lg-2">
            <label for="telefon" class="fw-semibold">Telefon:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text">
                    <i class="fas fa-phone"></i>
                </div>
                <input type="tel" class="form-control" id="telefon" name="telefon" placeholder="Telefon numarası yazınız" value="<?php echo $AyarlarBilgileri->telefon ?? ''; ?>">
            </div>
        </div>
    </div>

    <div class="row mb-3 align-items-center">
        <div class="col-lg-2">
            <label for="acilIletisim" class="fw-semibold">Acil İletişim:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <input type="tel" class="form-control" id="acilIletisim" name="acilIletisim" placeholder="Acil iletişim numarası" value="<?php echo $AyarlarBilgileri->acil_iletisim ?? ''; ?>">
            </div>
        </div>       
    </div>
</div>
