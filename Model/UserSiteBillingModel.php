<?php

namespace Model;

use Model\Model;
use PDO;

class UserSiteBillingModel extends Model
{
    protected $table = 'user_site_billing';

    public function __construct()
    {
        parent::__construct($this->table);
    }

    public function isPaid(int $userId, int $siteId, string $period): bool
    {
        $stmt = $this->db->prepare("SELECT paid FROM {$this->table} WHERE user_id = ? AND site_id = ? AND period = ? LIMIT 1");
        $stmt->execute([$userId, $siteId, $period]);
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return $row ? (int)$row->paid === 1 : false;
    }

    public function mark(int $userId, int $siteId, string $period, int $paid, float $amount): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE user_id = ? AND site_id = ? AND period = ? LIMIT 1");
        $stmt->execute([$userId, $siteId, $period]);
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        if ($row) {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET paid = ?, amount = ?, paid_at = CASE WHEN ?=1 THEN NOW() ELSE NULL END WHERE id = ?");
            return $stmt->execute([$paid, $amount, $paid, $row->id]);
        }
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (user_id, site_id, period, amount, paid, paid_at, created_at) VALUES (?, ?, ?, ?, ?, CASE WHEN ?=1 THEN NOW() ELSE NULL END, NOW())");
        return $stmt->execute([$userId, $siteId, $period, $amount, $paid, $paid]);
    }
}
