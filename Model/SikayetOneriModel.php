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
            `user_id` INT UNSIGNED NOT NULL,
            `site_id` INT UNSIGNED NULL,
            `type` VARCHAR(20) NOT NULL DEFAULT 'Şikayet',
            `title` VARCHAR(255) NOT NULL,
            `content` TEXT NOT NULL,
            `status` VARCHAR(50) NOT NULL DEFAULT 'Yeni',
            `reply_message` TEXT NULL,
            `reply_at` DATETIME NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `deleted_at` DATETIME NULL,
            PRIMARY KEY (`id`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_site_id` (`site_id`),
            KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $this->db->exec($sql);
    }

    public function createForUser(int $userId, ?int $siteId, array $data): string
    {
        $this->attributes = [
            'user_id' => $userId,
            'site_id' => $siteId,
            'type' => $data['type'] ?? 'Şikayet',
            'title' => $data['title'],
            'content' => $data['content'],
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
        $conds = ['user_id' => $userId];
        if ($siteId) { $conds['site_id'] = $siteId; }
        return $this->findAll($conds, 'id DESC');
    }
}