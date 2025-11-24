<?php
require_once dirname(__DIR__, 2) . '/configs/bootstrap.php';

use App\Modules\Onboarding\Controllers\OnboardingController;

if (!isset($_SESSION['user'])) { header('Location: sign-in'); exit; }
$user = $_SESSION['user'];
$siteId = isset($_SESSION['site_id']) ? (int)$_SESSION['site_id'] : null;

$controller = new OnboardingController();
$status = $controller->status((int)$user->id, $siteId);
?>
<div class="container py-4">
    <div class="card">
        <div class="card-header">
            İlk Kurulum Durumu
        </div>
        <div class="card-body">
            <div class="mb-3">
                <div class="progress" style="height: 20px;">
                    <div class="progress-bar" role="progressbar" style="width: <?= (int)$status['progress'] ?>%" aria-valuenow="<?= (int)$status['progress'] ?>" aria-valuemin="0" aria-valuemax="100">
                        <?= (int)$status['progress'] ?>%
                    </div>
                </div>
                <small class="text-muted">Tamamlanan: <?= (int)$status['completed_count'] ?>/<?= (int)$status['total_count'] ?></small>
            </div>
            <ul class="list-group">
                <?php foreach ($status['tasks'] as $t): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><?= htmlspecialchars($t['title']) ?></span>
                        <?php if ($t['is_completed']): ?>
                            <span class="badge bg-success">Tamamlandı</span>
                        <?php else: ?>
                            <form method="post" action="api/onboarding.php" class="d-inline">
                                <input type="hidden" name="action" value="complete" />
                                <input type="hidden" name="task_key" value="<?= htmlspecialchars($t['key']) ?>" />
                                <button class="btn btn-sm btn-primary" type="submit">Tamamla</button>
                            </form>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>