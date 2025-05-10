<!-- Modal -->
<div class="modal fade" id="detailModal<?= $index ?>" tabindex="-1" aria-labelledby="detailModalLabel<?= $index ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border shadow-sm rounded">
            
            <!-- Başlık -->
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-semibold" id="detailModalLabel<?= $index ?>">
                    <?= $debt['month']; ?> Ayı Borç Detayları
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>

            <!-- İçerik -->
            <div class="modal-body">
                <div class="row g-3">

                    <?php foreach ($debt['details'] as $key => $value): ?>
                        <div class="col-md-6">
                            <div class="border rounded p-3 d-flex justify-content-between align-items-center">
                                <span class="text-muted"><?= $key ?></span>
                                <strong><?= $value ?> ₺</strong>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if ($debt['ceza'] > 0): ?>
                        <div class="col-md-6">
                            <div class="border rounded p-3 d-flex justify-content-between align-items-center">
                                <span class="text-warning">Ceza</span>
                                <strong class="text-warning"><?= $debt['ceza'] ?> ₺</strong>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Borçlandırma Tarihi -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Borçlandırma Tarihi</span>
                            <span><?= $debt['date'] ?? '01.' . str_pad(($index + 1), 2, '0', STR_PAD_LEFT) . '.2025' ?></span>
                        </div>
                    </div>

                    <!-- Yönetici Notu -->
                    <?php if (!empty($debt['note'])): ?>
                        <div class="col-12">
                            <div class="border rounded p-3">
                                <strong class="d-block mb-2">Yönetici Notu:</strong>
                                <p class="mb-0"><?= $debt['note']; ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer border-top">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>
