<?php
namespace Model;
class PersonelOdemelerModel extends Model
{
    protected $table = 'personel_odemeler';
    public function listByPerson(int $personId): array
    {
        return $this->findWhere(['person_id' => $personId], 'id DESC');
    }
}