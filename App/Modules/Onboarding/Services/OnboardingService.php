<?php
namespace App\Modules\Onboarding\Services;

use App\Modules\Onboarding\Models\OnboardingTaskModel;
use App\Modules\Onboarding\Models\UserOnboardingProgressModel;
use PDO;

class OnboardingService
{
    private PDO $db;
    private OnboardingTaskModel $taskModel;
    private UserOnboardingProgressModel $progressModel;

    public function __construct()
    {
        $this->db = \getDbConnection();
        $this->taskModel = new OnboardingTaskModel();
        $this->progressModel = new UserOnboardingProgressModel();
    }

    public function ensureMigrations(): void
    {
        $this->db->exec(
            "CREATE TABLE IF NOT EXISTS onboarding_tasks (
                id INT AUTO_INCREMENT PRIMARY KEY,
                task_key VARCHAR(64) NOT NULL UNIQUE,
                title VARCHAR(255) NOT NULL,
                description TEXT NULL,
                order_no INT DEFAULT 0,
                active TINYINT(1) DEFAULT 1
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
        );

        try {
            $chkTaskKey = $this->db->query("SHOW COLUMNS FROM onboarding_tasks LIKE 'task_key'");
            if (!$chkTaskKey || $chkTaskKey->rowCount() === 0) {
                $chkKey = $this->db->query("SHOW COLUMNS FROM onboarding_tasks LIKE 'key'");
                if ($chkKey && $chkKey->rowCount() > 0) {
                    $this->db->exec("ALTER TABLE onboarding_tasks CHANGE `key` task_key VARCHAR(64) NOT NULL");
                }
            }
        } catch (\Throwable $e) { }

        $this->db->exec(
            "CREATE TABLE IF NOT EXISTS user_onboarding_progress (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                site_id INT NULL,
                task_key VARCHAR(64) NOT NULL,
                is_completed TINYINT(1) DEFAULT 0,
                completed_at DATETIME NULL,
                source ENUM('manual','auto') DEFAULT 'manual',
                is_dismissed TINYINT(1) DEFAULT 0,
                dismissed_at DATETIME NULL,
                first_shown_at DATETIME NULL,
                UNIQUE KEY uniq_user_site_task (user_id, site_id, task_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
        );
    }

    public function seedDefaults(): void
    {
        $count = $this->taskModel->count();
        if ($count > 0) { return; }
        $tasks = [
            ['task_key' => 'create_default_cash_account', 'title' => 'Kasa oluştur', 'order_no' => 10],
            ['task_key' => 'add_flat_types', 'title' => 'Daire türleri ekle', 'order_no' => 20],
            ['task_key' => 'create_site', 'title' => 'Site oluştur', 'order_no' => 30],
            ['task_key' => 'add_blocks', 'title' => 'Blok ekle', 'order_no' => 40],
            ['task_key' => 'add_apartments', 'title' => 'Daire ekle', 'order_no' => 50],
            ['task_key' => 'add_people', 'title' => 'Kişiler ekle', 'order_no' => 60],
            ['task_key' => 'set_default_cash_account', 'title' => 'Varsayılan kasa ayarla', 'order_no' => 70],
            ['task_key' => 'add_dues_types', 'title' => 'Aidat türleri ekle', 'order_no' => 80],
        ];
        foreach ($tasks as $t) {
            $this->taskModel->saveWithAttr([
                'id' => 0,
                'task_key' => $t['task_key'],
                'title' => $t['title'],
                'order_no' => $t['order_no'],
                'active' => 1,
            ]);
        }
    }

    public function getTasks(): array
    {
        return $this->taskModel->allActiveOrdered();
    }

    public function getUserProgress(int $userId, ?int $siteId): array
    {
        return $this->progressModel->getProgress($userId, $siteId);
    }

    public function shouldShowChecklist(int $userId, ?int $siteId): bool
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (!empty($_SESSION['onboarding_shown_this_login'])) { return false; }
        if (!empty($_SESSION['onboarding_dismissed'])) { return false; }
        $tasks = $this->getTasks();
        if (!$tasks) { return false; }
        $map = $this->getUserProgress($userId, $siteId);
        foreach ($tasks as $t) {
            $p = $map[$t->task_key] ?? null;
            if (!$p || (int)($p->is_completed ?? 0) !== 1) {
                return true;
            }
        }
        return false;
    }

    public function dismiss(): void
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $_SESSION['onboarding_dismissed'] = true;
    }

    public function completeTask(int $userId, string $taskKey, ?int $siteId, string $source = 'manual'): void
    {
        $this->progressModel->upsert($userId, $siteId, $taskKey, [
            'user_id' => $userId,
            'site_id' => $siteId,
            'task_key' => $taskKey,
            'is_completed' => 1,
            'completed_at' => date('Y-m-d H:i:s'),
            'source' => $source,
            'is_dismissed' => 0,
        ]);
    }

    public function getStatus(int $userId, ?int $siteId): array
    {
        $tasks = $this->getTasks();
        $map = $this->getUserProgress($userId, $siteId);
        $list = [];
        $completed = 0;
        foreach ($tasks as $t) {
            $p = $map[$t->task_key] ?? null;
            $isCompleted = $p ? (int)$p->is_completed === 1 : 0;
            if ($isCompleted) { $completed++; }
            $list[] = [
                'key' => $t->task_key,
                'title' => $t->title,
                'description' => $t->description ?? '',
                'order_no' => (int)($t->order_no ?? 0),
                'is_completed' => $isCompleted,
            ];
        }
        $total = max(count($tasks), 1);
        $progress = (int)floor(($completed / $total) * 100);
        return [
            'tasks' => $list,
            'progress' => $progress,
            'completed_count' => $completed,
            'total_count' => $total,
            'should_show' => $this->shouldShowChecklist($userId, $siteId),
        ];
    }
}