<?php
namespace Model;
class PersonelIzinlerModel extends Model
{
    protected $table = 'personel_izinler';
    public function listByPerson(int $personId): array
    {
        return $this->findWhere(['person_id' => $personId], 'id DESC');
    }
}