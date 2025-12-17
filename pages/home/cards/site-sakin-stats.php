
<?php
// Bu kart, aktif sitedeki sakin istatistiklerini gösterir.
// Not: Burada HTTP ile endpoint çağırmak (file_get_contents) sunucu yapılandırmasına göre
// devre dışı olabiliyor veya path farklı olabiliyor. Bu yüzden doğrudan modele gidiyoruz.

use Model\KisilerModel;

$stats = [
	'sakin' => ['total' => 0, 'active' => 0, 'passive' => 0],
	'owner' => ['total' => 0, 'active' => 0, 'passive' => 0],
	'tenant' => ['total' => 0, 'active' => 0, 'passive' => 0],
];

try {
	$site_id = (int)($_SESSION['site_id'] ?? 0);
	if ($site_id > 0) {
		$m = new KisilerModel();
		$stats = array_merge($stats, $m->getSiteSakinStats($site_id));
	}
} catch (Throwable $e) {
	// Sessiz geç: dashboard kırılmasın.
}
?>

<div class="row g-3 card-wrapper" data-card="site-sakin-stats">
	<div class="d-flex justify-content-end px-2 pt-2">
		<span class="drag-handle" title="Taşı"><i class="bi bi-arrows-move"></i></span>
	</div>
	<div class="col-xxl-4 col-md-6">
		<div class="card stretch stretch-full">
			<div class="card-body">
				<div class="d-flex align-items-center justify-content-between mb-3">
					<div class="d-flex align-items-center gap-2">
						<span class="fw-semibold text-muted">Sakin</span>
						<span class="badge bg-soft-success text-success">Genel</span>
					</div>
					<span class="text-muted small">Güncel</span>
				</div>

				<div class="row g-3 align-items-center">
					<div class="col-auto">
						<div class="avatar-text avatar-xl rounded bg-soft-primary text-primary border-soft-primary">
							<i class="feather-users"></i>
						</div>
					</div>
					<div class="col">
						<div class="fs-2 fw-bolder lh-1"><?php echo number_format((int)$stats['sakin']['total'], 0, ',', '.'); ?></div>
						<div class="d-grid gap-2 mt-3">
							<span class="badge bg-soft-success text-success px-3 py-2 text-start">Aktif: <span class="fw-bold float-end"><?php echo number_format((int)$stats['sakin']['active'], 0, ',', '.'); ?></span></span>
							<span class="badge bg-soft-danger text-danger px-3 py-2 text-start">Pasif: <span class="fw-bold float-end"><?php echo number_format((int)$stats['sakin']['passive'], 0, ',', '.'); ?></span></span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-xxl-4 col-md-6">
		<div class="card stretch stretch-full">
			<div class="card-body">
				<div class="d-flex align-items-center justify-content-between mb-3">
					<div class="d-flex align-items-center gap-2">
						<span class="fw-semibold text-muted">Ev Sahibi</span>
						<span class="badge bg-soft-warning text-warning">Mülk</span>
					</div>
					<span class="text-muted small">Güncel</span>
				</div>

				<div class="row g-3 align-items-center">
					<div class="col-auto">
						<div class="avatar-text avatar-xl rounded bg-soft-warning text-warning border-soft-warning">
							<i class="feather-home"></i>
						</div>
					</div>
					<div class="col">
						<div class="fs-2 fw-bolder lh-1"><?php echo number_format((int)$stats['owner']['total'], 0, ',', '.'); ?></div>
						<div class="d-grid gap-2 mt-3">
							<span class="badge bg-soft-success text-success px-3 py-2 text-start">Aktif: <span class="fw-bold float-end"><?php echo number_format((int)$stats['owner']['active'], 0, ',', '.'); ?></span></span>
							<span class="badge bg-soft-danger text-danger px-3 py-2 text-start">Pasif: <span class="fw-bold float-end"><?php echo number_format((int)$stats['owner']['passive'], 0, ',', '.'); ?></span></span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-xxl-4 col-md-6">
		<div class="card stretch stretch-full">
			<div class="card-body">
				<div class="d-flex align-items-center justify-content-between mb-3">
					<div class="d-flex align-items-center gap-2">
						<span class="fw-semibold text-muted">Kiracı</span>
						<span class="badge bg-soft-info text-info">Kiralık</span>
					</div>
					<span class="text-muted small">Güncel</span>
				</div>

				<div class="row g-3 align-items-center">
					<div class="col-auto">
						<div class="avatar-text avatar-xl rounded bg-soft-info text-info border-soft-info">
							<i class="feather-user"></i>
						</div>
					</div>
					<div class="col">
						<div class="fs-2 fw-bolder lh-1"><?php echo number_format((int)$stats['tenant']['total'], 0, ',', '.'); ?></div>
						<div class="d-grid gap-2 mt-3">
							<span class="badge bg-soft-success text-success px-3 py-2 text-start">Aktif: <span class="fw-bold float-end"><?php echo number_format((int)$stats['tenant']['active'], 0, ',', '.'); ?></span></span>
							<span class="badge bg-soft-danger text-danger px-3 py-2 text-start">Pasif: <span class="fw-bold float-end"><?php echo number_format((int)$stats['tenant']['passive'], 0, ',', '.'); ?></span></span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
