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
}