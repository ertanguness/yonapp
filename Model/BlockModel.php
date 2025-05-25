<?php

namespace Model;

use Model\Model;
use PDO;

class BlockModel extends Model
{
    protected $table = "blocks";

    public function __construct()
    {
        parent::__construct($this->table);
    }

    public function getBlocksBySite()
    {
        $site_id = $_SESSION["firm_id"];
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE site_id = ?");
        $sql->execute([$site_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }
    public function isBlockNameExists($site_id, $block_name)
    {
        $query = $this->db->prepare("SELECT COUNT(*) FROM blocks WHERE site_id = ? AND block_name = ?");
        $query->execute([$site_id, $block_name]);
        return $query->fetchColumn() > 0;
    }
}
