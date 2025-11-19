<?php

namespace Model;

class AnketModel extends Model
{
    protected $table = 'anket';

    public function __construct($table = null)
    {
        parent::__construct($table ?? $this->table);
        $this->ensureTable();
    }

    private function ensureTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT NULL,
            `start_date` DATETIME NULL,
            `end_date` DATETIME NULL,
            `status` VARCHAR(20) NOT NULL DEFAULT 'Taslak',
            `options_json` TEXT NOT NULL,
            `total_votes` INT UNSIGNED NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $this->db->exec($sql);
    }

    public function create(array $data)
    {
        $this->attributes = [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'status' => $data['status'] ?? 'Taslak',
            'options_json' => json_encode($data['options'] ?? [], JSON_UNESCAPED_UNICODE),
        ];
        return $this->insert();
    }

    public function updateById($id, array $data)
    {
        $attrs = [];
        foreach (['title','description','start_date','end_date','status'] as $k) {
            if (array_key_exists($k, $data)) { $attrs[$k] = $data[$k]; }
        }
        if (isset($data['options'])) {
            $attrs['options_json'] = json_encode($data['options'], JSON_UNESCAPED_UNICODE);
        }
        return $this->updateSingle($id, $attrs);
    }
}