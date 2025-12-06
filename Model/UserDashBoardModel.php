<?php
namespace Model;

use PDO;
use App\Helper\Helper;;
use Model\Model;
use App\InterFaces\LoggerInterface;

class UserDashBoardModel extends Model
{
    protected $order_table = 'user_dashboard_order';
    protected $has_column;

    public function __construct()
    {
        parent::__construct($this->table);
    }

    /** Dashboard sıralamasını kaydet */
    public function saveUserDashboardOrder(int $userId, array $order): void
    {
        // Mevcut sıralamayı sil
        $stmt = $this->db->prepare("DELETE FROM {$this->order_table} WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);


        // Yeni sıralamayı ekle
        $stmt = $this->db->prepare("INSERT INTO {$this->order_table} (user_id, widget_key, position) VALUES (:user_id, :widget_key, :position)");
        foreach ($order as $position => $widgetKey) {
            $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
            $stmt->bindValue(':widget_key', (string)$widgetKey, PDO::PARAM_STR);
            $stmt->bindValue(':position', (int)$position, PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    /** Kullanıcının dashboard kart sırasını getirir */
    public function getUserDashboardOrder(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT widget_key FROM {$this->order_table} WHERE user_id = :user_id ORDER BY position ASC");
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_values(array_filter(array_map(function($r){ return $r['widget_key'] ?? null; }, $rows), function($v){ return is_string($v) && $v !== ''; }));
    }

    protected function ensureColumnField(): bool
    {
        if ($this->has_column === null) {
            $q = $this->db->prepare("SHOW COLUMNS FROM {$this->order_table} LIKE 'col'");
            $q->execute();
            $this->has_column = (bool)$q->fetch(PDO::FETCH_ASSOC);
        }
        return $this->has_column;
    }

    public function saveUserDashboardLayout(int $userId, array $items): void
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->order_table} WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $has = $this->ensureColumnField();
        if ($has) {
            $ins = $this->db->prepare("INSERT INTO {$this->order_table} (user_id, widget_key, position, col) VALUES (:user_id, :widget_key, :position, :col)");
            foreach ($items as $it) {
                $ins->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
                $ins->bindValue(':widget_key', (string)($it['widget_key'] ?? ''), PDO::PARAM_STR);
                $ins->bindValue(':position', (int)($it['position'] ?? 0), PDO::PARAM_INT);
                $ins->bindValue(':col', (int)($it['column'] ?? 1), PDO::PARAM_INT);
                $ins->execute();
            }
        } else {
            $ins = $this->db->prepare("INSERT INTO {$this->order_table} (user_id, widget_key, position) VALUES (:user_id, :widget_key, :position)");
            foreach ($items as $it) {
                $ins->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
                $ins->bindValue(':widget_key', (string)($it['widget_key'] ?? ''), PDO::PARAM_STR);
                $ins->bindValue(':position', (int)($it['position'] ?? 0), PDO::PARAM_INT);
                $ins->execute();
            }
        }
    }

    public function getUserDashboardLayout(int $userId): array
    {
        $has = $this->ensureColumnField();
        if ($has) {
            $stmt = $this->db->prepare("SELECT widget_key, position, col FROM {$this->order_table} WHERE user_id = :user_id ORDER BY col ASC, position ASC");
        } else {
            $stmt = $this->db->prepare("SELECT widget_key, position FROM {$this->order_table} WHERE user_id = :user_id ORDER BY position ASC");
        }
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = [];
        foreach ($rows as $r) {
            $key = $r['widget_key'] ?? '';
            if (is_string($key) && $key !== '') {
                $out[] = [
                    'widget_key' => $key,
                    'position' => (int)($r['position'] ?? 0),
                    'column' => $has ? (int)($r['col'] ?? 1) : 1,
                ];
            }
        }
        return $out;
    }
}
