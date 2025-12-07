<?php

namespace Model;

//Model klasoru altında bulunan BaseModel sınıfını dahil ediyoruz
use Model\Model;
use PDO;

class KisiNotModel extends Model
{
    protected $table = "kisi_notlar";

    public function __construct()
    {
        parent::__construct($this->table);
        $this->ensureTable();
    }

    private function ensureTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `kisi_id` INT UNSIGNED NOT NULL,
            `site_id` INT UNSIGNED NULL,
            `icerik` TEXT NOT NULL,
            `kayit_yapan` INT UNSIGNED NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `silinme_tarihi` DATETIME NULL,
            `silen_kullanici` INT UNSIGNED NULL,
            PRIMARY KEY (`id`),
            KEY `idx_kisi_id` (`kisi_id`),
            KEY `idx_site_id` (`site_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $this->db->exec($sql);
    }

    public function NotBilgileri($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function KisiNotBilgileri($kisi_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE kisi_id = ? ORDER BY id DESC");
        $stmt->execute([$kisi_id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function notEkleTableRow($id, $sira = null)
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
        $icerik = htmlspecialchars($data->icerik ?? '-');

        return '<tr data-id="' . $enc_id . '" class="text-center">'
            . '<td class="sira-no">' . $siraNumarasi . '</td>'
            . '<td>' . htmlspecialchars($blok->blok_adi ?? '-') . '</td>'
            . '<td>' . (is_object($daire) && isset($daire->daire_no) ? htmlspecialchars($daire->daire_no) : '-') . '</td>'
            . '<td>' . htmlspecialchars($kisi->adi_soyadi ?? '-') . '</td>'
            . '<td>' . htmlspecialchars($kisi->telefon ?? '-') . '</td>'
            . '<td class="text-start">' . $icerik . '</td>'
            . '<td>'
                . '<div class="hstack gap-2">'
                    . '<a href="javascript:void(0);" class="avatar-text avatar-md edit-note" title="Düzenle" data-id="' . $enc_id . '">' . '<i class="feather-edit"></i>' . '</a>'
                    . '<a href="javascript:void(0);" class="avatar-text avatar-md delete-note" data-id="' . $enc_id . '" data-name="' . substr($icerik, 0, 30) . '">' . '<i class="feather-trash-2"></i>' . '</a>'
                . '</div>'
            . '</td>'
        . '</tr>';
    }
}
