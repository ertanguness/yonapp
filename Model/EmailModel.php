<?php

namespace Model;

use PDO;

class EmailModel extends Model
{
    protected $table = 'notifications';

    public function __construct()
    {
        parent::__construct($this->table);
    }

    public function listAll(int $limit = null): array
    {
        $sql = "SELECT id, type, recipients, subject, message, status, created_at FROM {$this->table} WHERE type = 'email' ORDER BY id DESC";
        if ($limit) {
            $sql .= " LIMIT :limit";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $this->db->query($sql);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}