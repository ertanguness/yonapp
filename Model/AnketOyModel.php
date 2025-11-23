<?php

namespace Model;

class AnketOyModel extends Model
{
    protected $table = 'anket_oy';

    public function __construct($table = null)
    {
        parent::__construct($table ?? $this->table);
        $this->ensureTable();
    }

    private function ensureTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `survey_id` INT UNSIGNED NOT NULL,
            `option_text` VARCHAR(255) NOT NULL,
            `user_id` INT UNSIGNED NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX (`survey_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $this->db->exec($sql);
    }

    public function addVote(int $surveyId, string $optionText, ?int $userId = null)
    {
        $this->attributes = [
            'survey_id' => $surveyId,
            'option_text' => $optionText,
            'user_id' => $userId,
        ];
        return $this->insert();
    }

    public function getResults(int $surveyId): array
    {
        $stmt = $this->db->prepare("SELECT option_text, COUNT(*) as votes FROM {$this->table} WHERE survey_id = ? GROUP BY option_text");
        $stmt->execute([$surveyId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $total = 0;
        foreach ($rows as $r) { $total += (int)$r['votes']; }
        return ['total' => $total, 'rows' => $rows];
    }
}