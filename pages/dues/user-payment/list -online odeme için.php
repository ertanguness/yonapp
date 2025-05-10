<?php
$currentYear = date("Y");
$currentMonth = date("n"); // 1-12 (Aktif ay)

$aidatlar = [
    ["ay" => "Ocak", "tutar" => 100, "ceza" => 5, "durum" => "Ödenmiş", "yıl" => 2024],
    ["ay" => "Şubat", "tutar" => 100, "ceza" => 10, "durum" => "Ödenmemiş", "yıl" => 2024],
    ["ay" => "Mart", "tutar" => 100, "ceza" => 15, "durum" => "Ödenmemiş", "yıl" => 2024],
    ["ay" => "Nisan", "tutar" => 100, "ceza" => 20, "durum" => "Ödenmemiş", "yıl" => 2024],
];
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title text-center w-100" id="paymentTableTitle"></h5>
        <div class="d-flex align-items-center">
            <label for="yearSelect" class="form-label me-2">İşlem Yapmak İstediğiniz Yılı Seçiniz:</label>
            <select id="yearSelect" class="form-control w-auto">
            <?php for ($year = $currentYear; $year >= $currentYear - 5; $year--) { ?>
            <option value="<?= $year ?>" <?= $year == $currentYear ? "selected" : "" ?>><?= $year ?></option>
            <?php } ?>
            </select>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Ay</th>
                        <th>Aidat Tutarı</th>
                        <th>Ceza Borcu</th>
                        <th>Seç</th>
                        <th>Ödeme Durumu</th>
                    </tr>
                </thead>
                <tbody id="aidatTableBody">
                    <?php
                    $i = 1;
                    foreach ($aidatlar as $index => $aidat) {
                        $disabled = ($index + 1 < $currentMonth) ? "disabled" : ""; // Geçmiş aylar kilitli
                        $checked = ($index + 1 <= $currentMonth) ? "checked" : ""; // Şimdiki ve geçmiş aylar seçili
                        $statusIcon = $aidat["durum"] == "Ödenmiş" ? "<i class='text-success fas fa-check-circle'></i> Ödenmiş" : "<i class='text-danger fas fa-times-circle'></i> Ödenmemiş";
                    ?>
                        <tr data-year="<?= $aidat['yıl'] ?>">
                            <td><?= $i++ ?></td>
                            <td><?= $aidat["ay"] ?></td>
                            <td class="aidat-tutar"><?= $aidat["tutar"] ?>₺</td>
                            <td class="ceza-tutar"><?= $aidat["ceza"] ?>₺</td>
                            <td>
                                <input type="checkbox" class="form-check-input aidat-checkbox" <?= $checked ?> <?= $disabled ?>>
                            </td>
                            <td><?= $statusIcon ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3 text-end">
            <h5>Toplam Ceza Borcu: <span id="totalPenalty">0₺</span></h5>
            <h5>Birikmiş Aidat Borcu: <span id="totalDebt">0₺</span></h5>
            <h4>Toplam Borç: <span id="totalAmount">0₺</span></h4>
            <button class="btn btn-primary float-end" id="payButton">Ödeme Yap</button>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    function updateTableTitle() {
        var selectedYear = $("#yearSelect").val();
        $("#paymentTableTitle").text(selectedYear + " Yılı Aidat Ödeme Tablosu");

        // Tabloyu güncelle
        $("#aidatTableBody tr").each(function() {
            var rowYear = $(this).data("year");
            $(this).toggle(rowYear == selectedYear);
        });
    }

    function calculateTotal() {
        var totalAidat = 0;
        var totalPenalty = 0;

        $(".aidat-checkbox:checked").each(function() {
            totalAidat += parseInt($(this).closest("tr").find(".aidat-tutar").text());
            totalPenalty += parseInt($(this).closest("tr").find(".ceza-tutar").text());
        });

        $("#totalDebt").text(totalAidat + "₺");
        $("#totalPenalty").text(totalPenalty + "₺");
        $("#totalAmount").text((totalAidat + totalPenalty) + "₺");
    }

    // Sayfa yüklendiğinde başlığı ve tabloyu güncelle
    updateTableTitle();
    calculateTotal();

    // Yıl değiştiğinde tabloyu güncelle
    $("#yearSelect").change(function() {
        updateTableTitle();
    });

    // Seçim değiştiğinde toplamı hesapla
    $(document).on("change", ".aidat-checkbox", function() {
        calculateTotal();
    });

    // Ödeme butonuna basıldığında uyarı
    $("#payButton").click(function() {
        var total = $("#totalAmount").text();
        if (parseInt(total) === 0) {
            Swal.fire("Hata", "Lütfen en az bir aidat seçiniz!", "error");
            return;
        }

        Swal.fire({
            title: "Ödeme Onayı",
            text: "Toplam " + total + " ödeme yapmak istiyor musunuz?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Evet, öde",
            cancelButtonText: "Hayır, iptal"
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire("Başarılı", "Ödeme işleminiz tamamlandı!", "success");
            }
        });
    });
});
</script>
