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
    
    public function acilDurumKisiEkleTableRow($id, $sira = null)
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
    
        // Yakınlık derecesi gösterimi
        $relationshipOptions = \App\Helper\Helper::RELATIONSHIP;
        $yakinlik = $data->yakinlik ?? null;
        $yakinlikText = isset($relationshipOptions[$yakinlik]) ? $relationshipOptions[$yakinlik] : '-';
    
        return '<tr data-id="' . $enc_id . '" class="text-center">
            <td class="sira-no">' . $siraNumarasi . '</td>
            <td>' . ($blok->blok_adi ?: '-') . '</td>        
            <td>' . (is_object($daire) && isset($daire->daire_no) ? $daire->daire_no : '-') . '</td>
            <td>' . ($kisi->adi_soyadi ?: '-') . '</td>
            <td>' . ($data->adi_soyadi ?: '-') . '</td>
            <td>' . ($data->telefon ?: '-') . '</td>
            <td>' . $yakinlikText . '</td>
            <td>
                <div class="hstack gap-2">
                    <a href="javascript:void(0);" class="avatar-text avatar-md edit-car" title="Düzenle" data-id="' . $enc_id . '">
                        <i class="feather-edit"></i>
                    </a>
                    <a href="javascript:void(0);" class="avatar-text avatar-md delete-acilDurumKisi" data-id="' . $enc_id . '" data-name="' . htmlspecialchars($data->adi_soyadi) . '">
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
   
    public function AcilDurumKisiBilgileri($id)
    {
        $query = $this->db->prepare("SELECT * FROM $this->table WHERE id = ?");
        $query->execute([$id]);
        return $query->fetch(PDO::FETCH_OBJ);
    }
}
