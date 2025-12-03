<?php

namespace Model;

use Model\Model;
use PDO;

class PersonelGorevlerModel extends Model
{


    protected $table = 'personel_gorevler';

    public function __construct($table = null)
    {
        return parent::__construct($table);
    }
    public function listByPerson(int $personId): array
    {
        return $this->findWhere(['person_id' => $personId], 'id DESC');
    }

    /** Personele ait görev kaydı var mı kontrol et */
    public function hasGorev(int $personId): bool
    {
        $result = $this->findWhere(['person_id' => $personId]);
        return !empty($result);
    }
}