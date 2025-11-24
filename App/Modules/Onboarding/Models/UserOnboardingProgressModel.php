<?php
namespace App\Modules\Onboarding\Models;

use Model\Model;
use PDO;

class UserOnboardingProgressModel extends Model
{
    protected $table = 'user_onboarding_progress';

    public function __construct()
    {
        parent::__construct($this->table);
    }

    public function getProgress(int $userId, ?int $siteId = null): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :uid" . ($siteId ? " AND site_id = :sid" : "");
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        if ($siteId) { $stmt->bindValue(':sid', $siteId, PDO::PARAM_INT); }
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_OBJ) ?: [];
        $map = [];
        foreach ($rows as $r) { $map[$r->task_key] = $r; }
        return $map;
    }

    public function markFirstShown(int $userId, ?int $siteId = null): void
    {
        $sql = "UPDATE {$this->table} SET first_shown_at = NOW() WHERE user_id = :uid" . ($siteId ? " AND site_id = :sid" : "");
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        if ($siteId) { $stmt->bindValue(':sid', $siteId, PDO::PARAM_INT); }
        $stmt->execute();
    }

    public function upsert(int $userId, ?int $siteId, string $taskKey, array $data): void
    {
        $existing = $this->findByComposite($userId, $siteId, $taskKey);
        if ($existing) {
            $data['id'] = $existing->id;
        } else {
            $data['id'] = 0;
        }
        $this->saveWithAttr($data);
    }

    public function findByComposite(int $userId, ?int $siteId, string $taskKey)
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :uid AND task_key = :tkey" . ($siteId ? " AND site_id = :sid" : " AND site_id IS NULL");
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':tkey', $taskKey);
        if ($siteId) { $stmt->bindValue(':sid', $siteId, PDO::PARAM_INT); }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }
}