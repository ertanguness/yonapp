<?php

namespace Model;

use Model\Model;
use PDO;

class UserAccessLockModel extends Model
{
    protected $table = 'user_access_locks';

    public function __construct()
    {
        parent::__construct($this->table);
    }

    public function getLockStatusByUser(int $userId): int
    {
        $stmt = $this->db->prepare("SELECT locked FROM {$this->table} WHERE user_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return $row ? (int)$row->locked : 0;
    }

    public function setLock(int $userId, int $locked, ?string $reason = null): bool
    {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (user_id, locked, reason, created_at) VALUES (?, ?, ?, NOW())");
        return $stmt->execute([$userId, $locked ? 1 : 0, $reason]);
    }
}
