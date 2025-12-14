<?php

namespace Model;

use Model\Model;
use PDO;

class UserSitePricingModel extends Model
{
    protected $table = 'user_site_pricing';

    public function __construct()
    {
        parent::__construct($this->table);
    }

    public function getByUserAndSite(int $userId, int $siteId): ?object
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = ? AND site_id = ? LIMIT 1");
        $stmt->execute([$userId, $siteId]);
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return $row ?: null;
    }

    public function upsert(int $userId, int $siteId, float $unitFee, ?string $startDate, ?int $dueDay = null, ?int $graceDays = null): bool
    {
        try {
            $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN due_day INT NULL");
        } catch (\Throwable $e) {}
        try {
            $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN grace_days INT NULL");
        } catch (\Throwable $e) {}
        $existing = $this->getByUserAndSite($userId, $siteId);
        if ($existing) {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET unit_fee = ?, start_date = ?, due_day = ?, grace_days = ? WHERE id = ?");
            return $stmt->execute([$unitFee, $startDate, $dueDay, $graceDays, $existing->id]);
        }
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (user_id, site_id, unit_fee, start_date, due_day, grace_days, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, 1, NOW())");
        return $stmt->execute([$userId, $siteId, $unitFee, $startDate, $dueDay, $graceDays]);
    }

    public function getAllForUser(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = ? AND is_active = 1");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ) ?: [];
    }
}
