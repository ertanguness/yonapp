<?php
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use Model\SSPModel;
use Database\Db;
use App\Helper\Helper;
use App\Helper\Date;
use App\Helper\Security;

$pdo = Db::getInstance()->connect();
$siteId = $_SESSION['site_id'] ?? null;

$table = "tahsilatlar t 
LEFT JOIN kisiler kisi ON t.kisi_id = kisi.id 
LEFT JOIN daireler d ON kisi.daire_id = d.id 
LEFT JOIN bloklar bl ON d.blok_id = bl.id 
LEFT JOIN kasa kasa ON t.kasa_id = kasa.id 
LEFT JOIN (SELECT tahsilat_id, SUM(kullanilan_tutar) AS kullanilan_kredi FROM kisi_kredi_kullanimlari GROUP BY tahsilat_id) kkk ON t.id = kkk.tahsilat_id";

$primaryKey = 't.id';

$columns = [
    [ 'db' => 't.makbuz_no', 'dt' => 0 ],
    [ 'db' => 't.islem_tarihi', 'dt' => 1, 'formatter' => function($d){ return Date::dmy($d); } ],
    [ 'db' => '', 'dt' => 2, 'formatter' => function($row){
        $name = htmlspecialchars($row['kisi_adi_soyadi'] ?? '');
        $apt = htmlspecialchars($row['d_daire_kodu'] ?? '');
        return '<div class="fw-bold">'.$name.'</div><small class="text-muted">'.$apt.'</small>';
    }],
    [ 'db' => '', 'dt' => 3, 'formatter' => function($row){
        $desc = htmlspecialchars($row['t_aciklama'] ?? 'Genel Tahsilat');
        $kasa = htmlspecialchars($row['kasa_kasa_adi'] ?? '');
        $encKasa = Security::encrypt($row['kasa_id'] ?? 0);
        return '<div>'.$desc.'</div><small class="text-muted"><a href="kasa-hareketleri/'.$encKasa.'"><i class="bi bi-safe me-1"></i>'.$kasa.'</a></small>';
    }],
    [ 'db' => '', 'dt' => 4, 'formatter' => function($row){
        $tutar = Helper::formattedMoney($row['t_tutar'] ?? 0);
        $kredi = (float)($row['kkk_kullanilan_kredi'] ?? 0);
        $extra = $kredi > 0 ? '<div>'.Helper::formattedMoney($kredi).'</div>' : '';
        return '<div class="text-end"><div class="fw-bold">'.$tutar.'</div>'.$extra.'</div>';
    }],
    [ 'db' => '', 'dt' => 5, 'formatter' => function($row){
        $encId = Security::encrypt($row['t_id'] ?? 0);
        return '<div class="text-center d-flex justify-content-center align-items-center gap-1">'
            .'<button class="avatar-text avatar-md tahsilat-detay-goster" data-id="'.$encId.'"><i class="feather-chevron-down"></i></button>'
            .'<a href="#" id="delete-tahsilat" data-id="'.$encId.'" class="avatar-text avatar-md"><i class="feather-trash-2"></i></a>'
            .'</div>';
    }],
    [ 'db' => 'kasa.id', 'dt' => 6 ],
    [ 'db' => 't.id', 'dt' => 7 ],
    [ 'db' => 't.aciklama', 'dt' => 8 ],
    [ 'db' => 't.tutar', 'dt' => 9 ],
    [ 'db' => 'kisi.adi_soyadi', 'dt' => 10 ],
    [ 'db' => 'd.daire_kodu', 'dt' => 11 ],
    [ 'db' => 'kasa.kasa_adi', 'dt' => 12 ],
    [ 'db' => 'kkk.kullanilan_kredi', 'dt' => 13 ],
];

$whereAll = [
    'condition' => 'bl.site_id = :site_id AND t.silinme_tarihi IS NULL AND t.tutar >= 0',
    'bindings' => [ ':site_id' => $siteId ]
];

echo json_encode(SSPModel::complex($_GET, $pdo, $table, $primaryKey, $columns, null, $whereAll));
