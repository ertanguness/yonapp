<?php 

namespace Model;

use App\Helper\Security;
use Model\Model;
use PDO;


class KisiKrediKullanimModel extends Model
{
    protected $table = "kisi_kredi_kullanimlari";

    public function __construct()
    {
        parent::__construct($this->table);
    }

}