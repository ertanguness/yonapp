<?php

namespace Model;

class SikayetOneriModel extends Model
{
    protected $table = 'sikayet_oneri';

    public function __construct($table = null)
    {
        parent::__construct($table ?? $this->table);
        $this->ensureTable();
    }

    private function ensureTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `kisi_id` INT UNSIGNED NOT NULL,
            `site_id` INT UNSIGNED NULL,
            `type` VARCHAR(20) NOT NULL DEFAULT 'Şikayet',
            `title` VARCHAR(255) NOT NULL,
            `message` TEXT NOT NULL,
            `status` VARCHAR(50) NOT NULL DEFAULT 'Yeni',
            `reply_message` TEXT NULL,
            `reply_at` DATETIME NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `deleted_at` DATETIME NULL,
            PRIMARY KEY (`id`),
            KEY `idx_kisi_id` (`kisi_id`),
            KEY `idx_site_id` (`site_id`),
            KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $this->db->exec($sql);
    }

    public function createForUser(int $kisiId, ?int $siteId, array $data): string
    {
        $this->attributes = [
            'kisi_id' => $kisiId,
            'site_id' => $siteId,
            'type' => $data['type'] ?? 'Şikayet',
            'title' => $data['title'],
            'message' => $data['content'] ?? ($data['message'] ?? ''),
            'status' => 'Yeni'
        ];
        return $this->insert();
    }

    public function listAll(?int $siteId = null): array
    {
        $conds = [];
        if ($siteId) { $conds['site_id'] = $siteId; }
        return $this->findAll($conds, 'id DESC');
    }

    public function listByUser(int $userId, ?int $siteId = null): array
    {
        $conds = ['kisi_id' => $userId];
        if ($siteId) { $conds['site_id'] = $siteId; }
        return $this->findAll($conds, 'id DESC');
    }

    public function getLatestWithUser(?int $siteId = null, int $limit = 5, ?string $status = null): array
    {
        $sql = "SELECT so.*, k.adi_soyadi, d.daire_kodu 
                FROM {$this->table} so
                LEFT JOIN kisiler k ON so.kisi_id = k.id
                LEFT JOIN daireler d ON k.daire_id = d.id
                WHERE 1=1";
        
        $params = [];
        if ($siteId) {
            $sql .= " AND so.site_id = :site_id";
            $params[':site_id'] = $siteId;
        }

        if ($status) {
            $sql .= " AND so.status = :status";
            $params[':status'] = $status;
        }
        
        $sql .= " ORDER BY so.id DESC LIMIT " . (int)$limit;
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
}