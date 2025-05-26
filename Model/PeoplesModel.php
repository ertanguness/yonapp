<?php

namespace Model;

use Model\BlockModel;
use Model\Model;
use PDO;

class PeoplesModel extends Model
{
    protected $table = 'peoples';

    public function __construct()
    {
        parent::__construct($this->table);
    }

    // aidat tablosundaki verileri alır
    public function getPeoples()
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    // Bloğun kişilerini getir
    public function getPeopleByBlock($block_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE block_id = ?");
        $sql->execute([$block_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Siteye ait blokları ve bu bloklara ait kişileri getirir.
     *
     * @param int $site_id Parametre olarak gelen site ID'si.
     * @return array Kişileri içeren bir dizi döner.
     */
    public function getPeopleBySite($site_id)
    {
        $blockModel = new BlockModel();
        $blocks = $blockModel->getBlocksBySite($site_id);
        $people = [];

        foreach ($blocks as $block) {
            $block_people = $this->getPeopleByBlock($block->id);
            if (!empty($block_people)) {
                $people = array_merge($people, $block_people);
            }
        }

        return $people;
    }

    /**
     * Belirli bir kişinin bilgilerini getirir.
     * @param int $id Kişinin ID'si.
     * @return object|null Kişi bilgilerini içeren nesne veya bulunamazsa null döner.
     */
    public function getPersonById($id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE id = ?");
        $sql->execute([$id]);
        return $sql->fetch(PDO::FETCH_OBJ);
    }

    /***Kişinin Adının getiri
     * @param int $id Kişinin ID'si.
     * @return string|null Kişinin adı veya bulunamazsa null döner.
     */
    public function getPersonNameById($id)
    {
        $sql = $this->db->prepare("SELECT fullname FROM $this->table WHERE id = ?");
        $sql->execute([$id]);
        $result = $sql->fetch(PDO::FETCH_OBJ);
        return $result ? $result->fullname : null;
    }

}
