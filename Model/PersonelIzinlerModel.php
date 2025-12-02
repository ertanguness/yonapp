<?php
namespace Model;
class PersonelIzinlerModel extends Model
{
    protected $table = 'personel_izinler';
    public function listByPerson(int $personId): array
    {
        return $this->findWhere(['person_id' => $personId], 'id DESC');
    }

    /** Personelin izin kaydı var mı kontrol et */
    public function hasIzin(int $personId): bool
    {
        $result = $this->findWhere(['person_id' => $personId]);
        return !empty($result);
    }
}