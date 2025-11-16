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

    /**Sitenin ail durum kisilerini getirir */
    
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

    public function hasColumn(string $column): bool
    {
        try {
            $stmt = $this->db->prepare("SHOW COLUMNS FROM {$this->table} LIKE ?");
            $stmt->execute([$column]);
            return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function ensureIndexes()
    {
        try {
            $this->db->exec("CREATE INDEX idx_ad ON acil_durum_kisileri(adi_soyadi)");
        } catch (\Throwable $e) {}
        try {
            $this->db->exec("CREATE INDEX idx_tel ON acil_durum_kisileri(telefon)");
        } catch (\Throwable $e) {}
        try {
            $this->db->exec("CREATE INDEX idx_rel ON acil_durum_kisileri(yakinlik)");
        } catch (\Throwable $e) {}
        try {
            $this->db->exec("CREATE INDEX idx_kayit ON acil_durum_kisileri(kayit_tarihi)");
        } catch (\Throwable $e) {}
    }

    public function listOrdered(bool $onlyActive = true, ?string $orderCol = null, string $orderDir = 'DESC'): array
    {
        $orderCol = $orderCol ?: ($this->hasColumn('kayit_tarihi') ? 'kayit_tarihi' : 'id');
        $sql = "SELECT * FROM {$this->table}";
        if ($onlyActive && $this->hasColumn('silinme_tarihi')) {
            $sql .= " WHERE silinme_tarihi IS NULL";
        }
        $sql .= " ORDER BY {$orderCol} " . ($orderDir === 'ASC' ? 'ASC' : 'DESC');
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ) ?: [];
    }
}
