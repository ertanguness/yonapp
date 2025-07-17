<div class="card-body blocks-info">
    <div class="row mb-4 align-items-center">
        <div class="col-lg-2">
            <label for="sitesId" class="fw-semibold">Site Adı:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="feather-home"></i></div>
                <input type="text" class="form-control" name="siteId" id="siteId" placeholder="Site ismi çekilecek" readonly value="<?php echo $site->site_adi ?? ''; ?>">
                <input type="hidden" name="site_id" id="site_id" value="<?php echo $site->id ?? ''; ?>">
            </div>
        </div>

        <div class="col-lg-2">
            <label for="blocksNumber" class="fw-semibold">Blok Sayısı: </label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="feather-trello"></i></div>
                <input type="number" class="form-control" name="blocksNumber" id="blocksNumber" placeholder="Blok Sayısı Giriniz" min="1" required step="1" required onkeypress="return event.charCode >= 48 && event.charCode <= 57">
            </div>
        </div>
    </div>
    <div id="blocksContainer" class="mt-3"></div>

    
</div>


<!-- Zorunlu alan kontro ve diğer sekmeyi aktif etme başlangıç
<script>
    document.getElementById("blocksTabButton").addEventListener("click", function(event) {
        event.preventDefault(); // Formun post edilmesini engelle

        // Tüm required alanlarını seç
        var requiredFields = document.querySelectorAll(".card-body.blocks-info [required]");
        var allFilled = true;

        // Boş alan var mı kontrol et
        requiredFields.forEach(function(field) {
            if (field.tagName === "TEXTAREA") {
                if (!field.value.trim()) { // textarea'lar için de boşluk kontrolü
                    allFilled = false;
                    field.classList.add("is-invalid"); // Boşsa kırmızı çerçeve ekle
                } else {
                    field.classList.remove("is-invalid"); // Doluysa kırmızı çerçeveyi kaldır
                }
            } else if (field.tagName === "INPUT") {
                if (!field.value.trim()) { // inputlar için boşluk kontrolü
                    allFilled = false;
                    field.classList.add("is-invalid"); // Boşsa kırmızı çerçeve ekle
                } else {
                    field.classList.remove("is-invalid"); // Doluysa kırmızı çerçeveyi kaldır
                }
            }
        });

        if (allFilled) {
            var daireBilgileriTab = document.querySelector('[data-bs-target="#dairebilgileriTab"]');
            if (daireBilgileriTab) {
                new bootstrap.Tab(daireBilgileriTab).show(); // Daire Bilgileri sekmesini aktif hale getir
            }
        } else {
            var toast = new bootstrap.Toast(document.getElementById('warningToast'));
            toast.show();
        }
    });


</script> -->

<!-- Zorunlu alan kontro ve diğer sekmeyi aktif etme bitiş -->

<!-- Girilen blok Sayısına göre blok ve daire oluşturma -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let blocksNumberInput = document.getElementById('blocksNumber');
        let blocksContainer = document.getElementById('blocksContainer');

        blocksNumberInput.addEventListener('input', function() {
            let count = parseInt(this.value) || 0;
            blocksContainer.innerHTML = ''; // Önceki içerikleri temizle

            for (let i = 1; i <= count; i++) {
                let div = document.createElement('div');
                div.classList.add('row', 'mb-2');
                div.innerHTML = `
                    <div class="col-lg-2 d-flex align-items-center ">
                        <label class="fw-semibold text-center">Blok Adı ${i}:</label>
                    </div>
                    <div class="col-lg-4">
                        <div class="input-group">
                            <div class="input-group-text"><i class="feather-trello"></i></div>
                            <input type="text" class="form-control block-name" name="block_names[]" placeholder="Blok Adı Giriniz" required >
                        </div>
                    </div>
                    <div class="col-lg-2 d-flex align-items-center ">
                        <label class="fw-semibold ">Bağımsız Bölüm Sayısı:</label>
                    </div>
                    <div class="col-lg-4">
                        <div class="input-group">
                            <div class="input-group-text"><i class="feather-layers"></i></div>
                            <input type="text" class="form-control apartment-count" name="apartment_counts[]" placeholder="Bağımsız Bölüm Sayısı Giriniz" min="1" required onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                        </div>
                    </div>
                `;
                blocksContainer.appendChild(div);
            }
        });
    });
</script>
<!-- Girilen blok Sayısına göre blok ve daire oluşturma -->