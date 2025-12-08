<?php
namespace Model;
class PersonelOdemelerModel extends Model
{
    protected $table = 'personel_odemeler';
    public function listByPerson(int $personId): array
    {
        return $this->findWhere(['person_id' => $personId], 'id DESC');
    }

    /** Personele ait ödeme kaydı var mı kontrol et */
    public function hasOdeme(int $personId): bool
    {
        $result = $this->findWhere(['person_id' => $personId]);
        return !empty($result);
    }

    public function sumByPersonYear(int $personId, ?int $year = null): float
    {
        $year = $year ?: (int)date('Y');
        $start = sprintf('%04d-01-01', $year);
        $end = sprintf('%04d-12-31', $year);
        $sql = $this->db->prepare("SELECT SUM(amount) AS total FROM {$this->table} WHERE person_id = :pid AND `date` BETWEEN :start AND :end");
        $sql->bindValue(':pid', $personId, \PDO::PARAM_INT);
        $sql->bindValue(':start', $start);
        $sql->bindValue(':end', $end);
        $sql->execute();
        $row = $sql->fetch(\PDO::FETCH_ASSOC);
        return (float)($row['total'] ?? 0);
    }
}
