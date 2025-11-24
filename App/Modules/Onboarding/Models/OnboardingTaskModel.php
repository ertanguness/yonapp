<?php
namespace App\Modules\Onboarding\Models;

use Model\Model;
use PDO;

class OnboardingTaskModel extends Model
{
    protected $table = 'onboarding_tasks';

    public function __construct()
    {
        parent::__construct($this->table);
    }

    public function findByKey(string $key)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE task_key = ? LIMIT 1");
        $stmt->execute([$key]);
        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public function allActiveOrdered(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE active = 1 ORDER BY order_no ASC, id ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}