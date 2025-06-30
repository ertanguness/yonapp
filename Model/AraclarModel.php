<?php

namespace Model;

use Model\BloklarModel;
use Model\KisilerModel;
use Model\DairelerModel;
use Model\Model;
use PDO;



class AraclarModel extends Model
{
    protected $table = 'araclar';

    public function __construct()
    {
        parent::__construct($this->table);
    }

    public function aracEkleTableRow($id, $sira = null)
    {
        $Bloklar = new BloklarModel();
        $Kisiler = new KisilerModel();
        $Daireler = new DairelerModel();
    
        $data = $this->find($id);
        $enc_id = \App\Helper\Security::encrypt($data->id);
    
        $kisi = $Kisiler->getPersonById($data->kisi_id);
        $daire = $Daireler->DaireAdi($kisi->daire_id ?? null);
        $blok = $Bloklar->Blok($kisi->blok_id ?? null);
    
        $siraNumarasi = $sira ?? '#';
    
        return '<tr data-id="' . $enc_id . '" class="text-center">
            <td class="sira-no">' . $siraNumarasi . '</td>
            <td>' . htmlspecialchars($blok->blok_adi ?? '-') . '</td>
            <td>' . (is_object($daire) && isset($daire->daire_no) ? htmlspecialchars($daire->daire_no) : '-') . '</td>
            <td>' . htmlspecialchars($kisi->adi_soyadi ?? '-') . '</td>
            <td>' . htmlspecialchars($kisi->telefon ?? '-') . '</td>
            <td>' . htmlspecialchars($data->plaka ?? '-') . '</td>
            <td>' . htmlspecialchars($data->marka_model ?? '-') . '</td>
            <td>
                <div class="hstack gap-2">
                    <a href="javascript:void(0);" class="avatar-text avatar-md edit-car" title="Düzenle" data-id="' . $enc_id . '">
                        <i class="feather-edit"></i>
                    </a>
                    <a href="javascript:void(0);" class="avatar-text avatar-md delete-car" data-id="' . $enc_id . '" data-name="' . htmlspecialchars($data->plaka) . '">
                        <i class="feather-trash-2"></i>
                    </a>
                </div>
            </td>
        </tr>';
    }
    

    public function AracVarmi($plaka)
    {
        $query = $this->db->prepare("SELECT COUNT(*) FROM $this->table WHERE plaka = ?");
        $query->execute([$plaka]);
        return $query->fetchColumn() > 0;
    }
    public function AracBilgileri($id)
    {
        $query = $this->db->prepare("SELECT * FROM $this->table WHERE id = ?");
        $query->execute([$id]);
        return $query->fetch(PDO::FETCH_OBJ);
    }
    public function KisiAracBilgileri($kisi_id)
    {
        $query = $this->db->prepare("SELECT * FROM $this->table WHERE kisi_id = ?");
        $query->execute([$kisi_id]);
        return $query->fetchAll(PDO::FETCH_OBJ);
    }
}
