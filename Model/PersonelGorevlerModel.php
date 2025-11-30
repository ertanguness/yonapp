<?php
namespace Model;
class PersonelGorevlerModel extends Model
{
    protected $table = 'personel_gorevler';
    public function listByPerson(int $personId): array
    {
        return $this->findWhere(['person_id' => $personId], 'id DESC');
    }
}