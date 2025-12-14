<?php

namespace Model;

use Model\Model;
use PDO;

class UserSiteLockModel extends Model
{
    protected $table = 'user_site_locks';

    public function __construct()
    {
        parent::__construct($this->table);
    }

    public function isLocked(int $userId, int $siteId): bool
    {
        $stmt = $this->db->prepare("SELECT locked FROM {$this->table} WHERE user_id = ? AND site_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$userId, $siteId]);
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return $row ? (int)$row->locked === 1 : false;
    }

    public function setLock(int $userId, int $siteId, int $locked, ?string $reason = null): bool
    {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (user_id, site_id, locked, reason, created_at) VALUES (?, ?, ?, ?, NOW())");
        return $stmt->execute([$userId, $siteId, $locked ? 1 : 0, $reason]);
    }
}
