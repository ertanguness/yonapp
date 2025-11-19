<?php

namespace Model;

use PDO;

class AnketApprovalModel extends Model
{
    protected $table = 'anket_approvals';

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
            `decision` ENUM('onay','red') NOT NULL,
            `comment` VARCHAR(500) NULL,
            `decided_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_survey_user` (`survey_id`,`user_id`),
            KEY `idx_survey` (`survey_id`),
            KEY `idx_user` (`user_id`),
            CONSTRAINT `fk_anket_approvals_survey` FOREIGN KEY (`survey_id`) REFERENCES `anket`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $this->db->exec($sql);
    }

    public function upsertDecision(int $surveyId, int $userId, string $decision, ?string $comment = null)
    {
        $decision = $decision === 'onay' ? 'onay' : 'red';
        $sql = $this->db->prepare("INSERT INTO {$this->table} (survey_id, user_id, decision, comment)
                                   VALUES (:survey_id, :user_id, :decision, :comment)
                                   ON DUPLICATE KEY UPDATE decision = VALUES(decision), comment = VALUES(comment), decided_at = CURRENT_TIMESTAMP");
        $sql->bindValue(':survey_id', $surveyId, PDO::PARAM_INT);
        $sql->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $sql->bindValue(':decision', $decision);
        $sql->bindValue(':comment', $comment);
        return $sql->execute();
    }

    public function getCounts(int $surveyId): array
    {
        $stmt = $this->db->prepare("SELECT decision, COUNT(*) as c FROM {$this->table} WHERE survey_id = ? GROUP BY decision");
        $stmt->execute([$surveyId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $approved = 0; $rejected = 0;
        foreach ($rows as $r) {
            if ($r['decision'] === 'onay') { $approved = (int)$r['c']; }
            if ($r['decision'] === 'red') { $rejected = (int)$r['c']; }
        }
        return [ 'approved' => $approved, 'rejected' => $rejected ];
    }
}