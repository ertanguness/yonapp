<?php
// Örnek veriler
$block = $_GET['block'] ?? 'A';
$flat_no = $_GET['flat_no'] ?? 1;

// Aidat ve ceza bilgileri (veritabanından çekilecek)
$monthlyDebts = [
    [
        'month' => 'Ocak',
        'aidat' => 100,
        'ceza' => 20,
        'paid' => false,
        'paid_amount' => 50,
        'details' => [
            'Aidat' => 100,
            'Asansör Bakım' => 20,
            'Temizlik' => 10,
            'Güvenlik' => 15,
            'Ortak Elektrik' => 5,
        ]
    ],
    [
        'month' => 'Şubat',
        'aidat' => 100,
        'ceza' => 0,
        'paid' => true,
        'paid_amount' => 100,
        'details' => [
            'Aidat' => 100,
            'Asansör Bakım' => 0,
            'Temizlik' => 0,
            'Güvenlik' => 0,
            'Ortak Elektrik' => 0,
        ]
    ],
    [
        'month' => 'Mart',
        'aidat' => 100,
        'ceza' => 10,
        'paid' => false,
        'paid_amount' => 0,
        'details' => [
            'Aidat' => 100,
            'Asansör Bakım' => 25,
            'Temizlik' => 5,
            'Güvenlik' => 10,
            'Ortak Elektrik' => 10,
        ]
    ],
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
                <button type="button" class="btn btn-outline-secondary route-link" data-page="dues/payment/list">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </button>
                <button class="btn btn-success" id="pay-button"><i class="feather-check-circle me-2"></i>Ödeme Yap</button>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
        <div class="row row-deck row-cards">
            <div class="card">
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Ay</th>
                                <th>Ana Borç (₺)</th>
                                <th>Ceza (₺)</th>
                                <th>Toplam Borç (₺)</th>
                                <th>Ödenen (₺)</th>
                                <th>Kalan (₺)</th>
                                <th>Durum</th>
                                <th>Seç</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($monthlyDebts as $index => $debt): ?>
                                <?php $totalDebt = $debt['aidat'] + $debt['ceza']; ?>
                                <tr>
                                    <td>
                                        <a data-bs-toggle="collapse" href="#detail-<?= $index; ?>" role="button" aria-expanded="false" aria-controls="detail-<?= $index; ?>" class="text-decoration-none d-flex align-items-center gap-1">
                                            <i class="feather-plus-circle text-primary"></i> <?= $debt['month']; ?>
                                        </a>
                                    </td>
                                    <td><?= $debt['aidat']; ?></td>
                                    <td><?= $debt['ceza']; ?></td>
                                    <td><?= $totalDebt; ?></td>
                                    <td><?= $debt['paid_amount']; ?></td>
                                    <td><?= $totalDebt - $debt['paid_amount']; ?></td>
                                    <td>
                                        <?= $debt['paid'] ? '<span class="badge bg-success">Ödendi</span>' : '<span class="badge bg-danger">Ödenmedi</span>'; ?>
                                    </td>
                                    <td>
                                        <?php if (!$debt['paid']): ?>
                                            <input type="checkbox" name="selected_months[]" value="<?= $index; ?>" class="form-check-input month-checkbox">
                                        <?php else: ?>
                                            <input type="checkbox" class="form-check-input" disabled>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="8" class="p-0 border-0">
                                        <div class="collapse" id="detail-<?= $index; ?>">
                                            <div class="p-3 bg-light border rounded shadow-sm mt-2">
                                                <h6 class="mb-2"><i class="feather-info text-secondary me-1"></i><?= $debt['month']; ?> Ayı Borç Detayları</h6>
                                                <ul class="list-unstyled mb-0">
                                                    <?php foreach ($debt['details'] as $key => $value): ?>
                                                        <li><i class="feather-chevron-right text-muted me-1"></i> <?= $key ?>: <strong><?= $value ?> ₺</strong></li>
                                                    <?php endforeach; ?>
                                                    <?php if ($debt['ceza'] > 0): ?>
                                                        <li><i class="feather-alert-circle text-warning me-1"></i> <strong>Ceza:</strong> <?= $debt['ceza']; ?> ₺</li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>


                    </table>
                    <div class="mt-3 p-3">
                        <div class="row align-items-center">
                            <div class="col-md-12 text-end"></div>
                            <h5 class="mb-2">Seçilen Ayların Toplam Borç: <span id="selected-aidat" class="text-primary">0 ₺</span></h5>
                            <h5 class="mb-2">Seçilen Ayların Toplam Ceza: <span id="selected-ceza" class="text-warning">0 ₺</span></h5>
                            <h5 class="mb-2">Seçilen Ayların Toplam Borç: <span id="selected-total" class="text-danger">0 ₺</span></h5>
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
            icon : 'question',
            html: `
                <div>
                    <p>Şu ayların borcunu ödemek üzeresiniz:</p>
                    <strong>${selectedMonths.join(', ')}</strong>
                    <p>Toplam Tutar: <strong>${totalAmount}</strong></p>
                    <p>Ödeme Yöntemi Seçiniz:</p>
                    <select id="payment-method" class="swal2-input">
                        <option value="">Nakit</option>
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
<script>
    // Detay göster/gizle
    document.querySelectorAll('.toggle-detail').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const detailRow = document.getElementById(targetId);
            if (detailRow.style.display === 'none') {
                detailRow.style.display = 'table-row';
                this.textContent = '➖';
            } else {
                detailRow.style.display = 'none';
                this.textContent = '➕';
            }
        });
    });
</script>