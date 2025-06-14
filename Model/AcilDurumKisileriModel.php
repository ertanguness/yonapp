<?php

namespace Model;

use Model\BloklarModel;
use Model\KisilerModel;
use Model\DairelerModel;
use Model\Model;
use PDO;



class AcilDurumKisileriModel extends Model
{
    protected $table = 'acil_durum_kisileri';

    public function __construct()
    {
        parent::__construct($this->table);
    }
    
    public function acilDurumKisiEkleTableRow($id)
    {
        $Bloklar = new BloklarModel();
        $Kisiler= new KisilerModel();
        $Daireler = new DairelerModel();

        $data = $this->find($id);
        $enc_id = \App\Helper\Security::encrypt($data->id);
        
        $kisi = $Kisiler->getPersonById($data->kisi_id);
        $daire = $Daireler->DaireAdi($kisi->daire_id ?? null);
        $blok = $Bloklar->Blok($kisi->blok_id ?? null);

        return '<tr id="islem_' . $data->id . '" data-id="' . $enc_id . '">
            <td>' . $data->id . '</td>
            <td>' . ($blok->blok_adi ?: '-') . '</td>        
            <td>' . (is_object($daire) && isset($daire->daire_no) ? $daire->daire_no : '-') . '</td>
            <td>' . ($kisi->adi_soyadi ?: '-') . '</td>
            <td>' . ($data->adi_soyadi ?: '-') . '</td>
            <td>' . ($data->telefon ?: '-') . '</td>
            <td>' . ($data->yakinlik ?: '-') . '</td>
            <td>
                <div class="hstack gap-2">
                    <a href="index?p=management/peoples/manage&id=<?php echo $enc_id; ?>" class="avatar-text avatar-md" title="Görüntüle">
                        <i class="feather-eye"></i>
                    </a>
                    <a href="index?p=management/peoples/manage&id=<?php echo $enc_id; ?>" class="avatar-text avatar-md" title="Düzenle">
                        <i class="feather-edit"></i>
                    </a>
                    <a href="javascript:void(0);" data-name="<?php echo $data->adi_soyadi; ?>" data-id="<?php echo $enc_id; ?>" class="avatar-text avatar-md delete-acilDurumKisi" data-id="<?php echo $enc_id; ?>" data-name="<?php echo $data->adi_soyadi; ?>">
                        <i class="feather-trash-2"></i>
                    </a>
                </div>
            </td>
        </tr>';
    }
    public function AcilDurumKisiVarmi($telefon)
    {
        $query = $this->db->prepare("SELECT COUNT(*) FROM $this->table WHERE telefon = ?");
        $query->execute([$telefon]);
        return $query->fetchColumn() > 0;
    }
   
    
}
