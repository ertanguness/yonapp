<?php
// Örnek veriler
$block = $_GET['block'] ?? 'A';
$flat_no = $_GET['flat_no'] ?? 1;

// Aidat ve ceza bilgileri (veritabanından çekilecek)
$monthlyDebts = [
    ['month' => 'Ocak', 'aidat' => 100, 'ceza' => 20, 'paid' => false],
    ['month' => 'Şubat', 'aidat' => 100, 'ceza' => 0, 'paid' => true],
    ['month' => 'Mart', 'aidat' => 100, 'ceza' => 10, 'paid' => false],
];
?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10"><?= $block ?> Blok - <?= $flat_no ?> Numaralı Daire Borç Detayı</h5>
        </div>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-header-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Geri</span>
                </a>
            </div>
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="dues/payment/list">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </button>

            </div>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="container-xl">
        <div class="row row-deck row-cards">
            <div class="card">
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Ay</th>
                                <th>Aidat (₺)</th>
                                <th>Ceza (₺)</th>
                                <th>Toplam Borç (₺)</th>
                                <th>Durum</th>
                                <th>Seç</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($monthlyDebts as $index => $debt): ?>
                                <tr>
                                    <td><?= $debt['month']; ?></td>
                                    <td><?= $debt['aidat']; ?></td>
                                    <td><?= $debt['ceza']; ?></td>
                                    <td><?= $debt['aidat'] + $debt['ceza']; ?></td>
                                    <td id="status-<?= $index; ?>">
                                        <?= $debt['paid'] ? '<span class="text-success">✅ Ödendi </span>' : '<span class="text-danger">❌ Ödenmedi </span>'; ?>
                                    </td>
                                    <td>
                                        <?php if (!$debt['paid']): ?>
                                            <input type="checkbox" name="selected_months[]" value="<?= $index; ?>" id="checkbox-<?= $index; ?>" class="month-checkbox">
                                        <?php else: ?>
                                            <input type="checkbox" disabled>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="mt-3 p-3">
                        <div class="row align-items-center">
                            <div class="col-md-12 text-end">
                                <h5 class="mb-2">Seçilen Ayların Toplam Aidat: <span id="selected-aidat" class="text-primary">0 ₺</span></h5>
                                <h5 class="mb-2">Seçilen Ayların Toplam Ceza: <span id="selected-ceza" class="text-warning">0 ₺</span></h5>
                                <h5 class="mb-2">Seçilen Ayların Toplam Borç: <span id="selected-total" class="text-danger">0 ₺</span></h5>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <button class="btn btn-primary" id="pay-button">Ödeme Yap</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Dinamik hesaplama
        document.querySelectorAll('.month-checkbox').forEach((checkbox) => {
            checkbox.addEventListener('change', calculateTotals);
        });

        function calculateTotals() {
            let totalAidat = 0;
            let totalCeza = 0;

            document.querySelectorAll('.month-checkbox:checked').forEach((checkbox) => {
                const row = checkbox.closest('tr');
                totalAidat += parseInt(row.children[1].textContent);
                totalCeza += parseInt(row.children[2].textContent);
            });

            document.getElementById('selected-aidat').textContent = totalAidat + ' ₺';
            document.getElementById('selected-ceza').textContent = totalCeza + ' ₺';
            document.getElementById('selected-total').textContent = (totalAidat + totalCeza) + ' ₺';
        }

        // Ödeme yapma işlemi
        document.getElementById('pay-button').addEventListener('click', function() {
            const selectedMonths = [];
            document.querySelectorAll('.month-checkbox:checked').forEach((checkbox) => {
                const row = checkbox.closest('tr');
                selectedMonths.push(row.children[0].textContent.trim());
            });

            if (selectedMonths.length === 0) {
                Swal.fire('Hata', 'Lütfen ödeme yapmak için en az bir ay seçiniz.', 'error');
                return;
            }

            const totalAmount = document.getElementById('selected-total').textContent;

            Swal.fire({
                title: 'Ödeme Onayı',
                html: `
                <div>
                    <p>Şu ayların borcunu ödemek üzeresiniz:</p>
                    <strong>${selectedMonths.join(', ')}</strong>
                    <p>Toplam Tutar: <strong>${totalAmount}</strong></p>
                    <p>Ödeme Yöntemi Seçiniz:</p>
                    <select id="payment-method" class="swal2-input">
                        <option value="nakit">Nakit</option>
                        <option value="havale">Havale/EFT</option>
                    </select>
                </div>
            `,
                showCancelButton: true,
                confirmButtonText: 'Onayla',
                cancelButtonText: 'İptal',
                preConfirm: () => {
                    const paymentMethod = document.getElementById('payment-method').value;
                    if (!paymentMethod) {
                        Swal.showValidationMessage('Lütfen bir ödeme yöntemi seçiniz.');
                    }
                    return {
                        paymentMethod
                    };
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Başarılı', 'Ödemeniz başarıyla tamamlandı.', 'success');
                }
            });
        });
    </script>