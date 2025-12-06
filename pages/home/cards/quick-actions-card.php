<?php 
use App\Services\Gate;
?>

<div class="col-xxl-12 quick-actions-card card-wrapper" data-card="quick-actions-card">
    <div class="card stretch stretch-full">
        <div class="card-header">
            <h5 class="card-title">Hızlı İşlemler</h5>
        </div>
        <div class="card-body">

            <?php if(Gate::allows("site_ekle_guncelle_sil")): ?>
                <a href="site-ekle"
                    class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5">
                    <i class="bi bi-diagram-3"></i>
                    <p class="fs-12 text-muted mb-0">Site Ekle  </p>
                </a>
            <?php endif; ?>
            <?php if(Gate::allows("blok_ekle_guncelle_sil")): ?>

            <a href="blok-ekle"
                class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5">
                <i class="bi bi-building"></i>
                <p class="fs-12 text-muted mb-0">Blok Ekle</p>
            </a>
            <?php endif; ?>
            <?php if(Gate::allows("daire_ekle_guncelle_sil")): ?>
            <a href="daire-ekle"
                class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5">
                <i class="bi bi-textarea"></i>
                <p class="fs-12 text-muted mb-0">Daire Ekle</p>
            </a>
            <?php endif; ?>

            <?php if(Gate::allows("site_sakini_ekle_guncelle_sil")): ?>

            <a href="/site-sakini-ekle"
                class="flex-fill py-3 px-4 me-4 rounded-1 cursor-pointer border border-dashed border-gray-5">
                <i class="feather-user-plus"></i>
                <p class="fs-12 text-muted mb-0">Kişi Ekle</p>
            </a>
            <?php endif; ?>

            <?php if(Gate::allows("gelir_gider_ekle_guncelle_sil")): ?>

            <a href="#"
                class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5 gelir-ekle">
                <i class="bi bi-credit-card"></i>
                <p class="fs-12 text-muted mb-0">Gelir Ekle</p>
            </a>
            <?php endif; ?>

            <?php if(Gate::allows("gelir_gider_ekle_guncelle_sil")): ?>

            <a href="#" class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5 gider-ekle">
                <i class="bi bi-credit-card-2-back"></i>
                <p class="fs-12 text-muted mb-0">Gider Ekle</p>
            </a>
            <?php endif; ?>
            <?php if(Gate::allows("finansal_islemler_goruntule")): ?>

            <a href="/gelir-gider-islemleri"
                class="flex-fill py-3 px-4 me-4 rounded-1 cursor-pointer border border-dashed border-gray-5">
                <i class="bi bi-wallet2"></i>
                <p class="fs-12 text-muted mb-0">Finansal İşlemler</p>
            </a>
            <?php endif; ?>
            <?php if(Gate::allows("aidat_turu_tanimlama")): ?>


            <a href="/aidat-turu-tanimlama" class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5">
                <i class="bi bi-folder-plus"></i>
                <p class="fs-12 text-muted mb-0">Aidat Tanımla</p>
            </a>
            <?php endif; ?>
            <?php if(Gate::allows("borclandirma_yap")): ?>

            <a href="/borclandirma-yap" class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5">
                <i class="bi bi-clipboard-plus"></i>
                <p class="fs-12 text-muted mb-0">Borçlandırma Yap</p>
            </a>
            <?php endif; ?>
            <?php if(Gate::allows("yonetici_aidat_odeme")): ?>

            <a href="/yonetici-aidat-odeme"
                class="flex-fill py-3 px-4 me-4 rounded-1 cursor-pointer border border-dashed border-gray-5">
                <i class="bi bi-person-workspace"></i>
                <p class="fs-12 text-muted mb-0">Yönetici Aidat Ödeme</p>
            </a>
            <?php endif; ?>
            <?php if(Gate::allows("email_sms_gonder")): ?>
            <a href="#" class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5 mail-gonder">
                <i class="bi bi-envelope"></i>
                <p class="fs-12 text-muted mb-0">Email Gönder</p>
            </a>
            <a href="#" class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5 sms-gonder">
                <i class="bi bi-send-plus"></i>
                <p class="fs-12 text-muted mb-0">Sms Gönder</p>
            </a>
            <?php endif; ?>

        </div>

    </div>
</div>
