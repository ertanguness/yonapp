<?php

namespace Model;

use PDO;

class MessageTemplateModel extends Model
{
    protected $table = 'message_templates';

    public function __construct()
    {
        parent::__construct($this->table);
        $this->ensureTable();
    }

    private function ensureTable(): void
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS `message_templates` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `site_id` INT UNSIGNED NULL,
                `type` VARCHAR(10) NOT NULL DEFAULT 'sms',
                `name` VARCHAR(255) NOT NULL,
                `subject` VARCHAR(255) NULL,
                `body` TEXT NOT NULL,
                `variables` TEXT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_site_type` (`site_id`, `type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            $this->db->exec($sql);
        } catch (\Throwable $e) {
        }
    }

    public function listTemplates(?int $siteId, string $type = 'sms'): array
    {
        $sql = "SELECT id, site_id, type, name, subject, body, variables, created_at, updated_at
                FROM {$this->table}
                WHERE type = :type" . ($siteId ? " AND (site_id = :sid OR site_id IS NULL)" : "");
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':type', $type);
        if ($siteId) { $stmt->bindValue(':sid', $siteId, PDO::PARAM_INT); }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function createTemplate(array $data): int
    {
        $this->saveWithAttr($data);
        return (int)$this->attributes['id'];
    }
}

