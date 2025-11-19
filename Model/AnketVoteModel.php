<?php

namespace Model;

use PDO;

class AnketVoteModel extends Model
{
    protected $table = 'anket_votes';

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
            `user_id` INT UNSIGNED NOT NULL,
            `option_text` VARCHAR(255) NOT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_vote` (`survey_id`,`user_id`),
            KEY `idx_survey` (`survey_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $this->db->exec($sql);
    }

    public function upsertVote(int $surveyId, int $userId, string $optionText)
    {
        $sql = $this->db->prepare("INSERT INTO {$this->table} (survey_id, user_id, option_text)
                                   VALUES (:survey_id, :user_id, :option_text)
                                   ON DUPLICATE KEY UPDATE option_text = VALUES(option_text), created_at = CURRENT_TIMESTAMP");
        $sql->bindValue(':survey_id', $surveyId, PDO::PARAM_INT);
        $sql->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $sql->bindValue(':option_text', $optionText);
        return $sql->execute();
    }

    public function getCountsByOption(int $surveyId): array
    {
        $stmt = $this->db->prepare("SELECT option_text, COUNT(*) as c FROM {$this->table} WHERE survey_id = ? GROUP BY option_text");
        $stmt->execute([$surveyId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserVote(int $surveyId, int $userId): ?string
    {
        $stmt = $this->db->prepare("SELECT option_text FROM {$this->table} WHERE survey_id = ? AND user_id = ? LIMIT 1");
        $stmt->execute([$surveyId, $userId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r['option_text'] ?? null;
    }
}