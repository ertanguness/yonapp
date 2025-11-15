<?php

namespace Model;

use Model\Model;
use PDO;
use App\Helper\Security;

class IsletmeProjesiModel extends Model
{
    protected $table = 'isletme_projeleri';

    public function __construct()
    {
        parent::__construct($this->table);
        $this->ensureTables();
    }

    protected function ensureTables(): void
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS isletme_projeleri (
            id INT AUTO_INCREMENT PRIMARY KEY,
            site_id INT NOT NULL,
            proje_adi VARCHAR(255) NOT NULL,
            apartman_site_adi VARCHAR(255) NOT NULL,
            adres TEXT,
            donem_baslangic DATE NOT NULL,
            donem_bitis DATE NOT NULL,
            kanuni_dayanak TEXT,
            varsayimlar TEXT,
            metodoloji TEXT,
            enflasyon_oran DECIMAL(5,2) DEFAULT 0.00,
            rezerv_tutar DECIMAL(14,2) DEFAULT 0.00,
            odeme_plani TEXT,
            takvim TEXT,
            guncelleme_mekanizmasi TEXT,
            durum VARCHAR(30) DEFAULT 'aktif',
            toplam_gelir DECIMAL(14,2) DEFAULT 0.00,
            toplam_gider DECIMAL(14,2) DEFAULT 0.00,
            net_yillik_gider DECIMAL(14,2) DEFAULT 0.00,
            aylik_avans_toplam DECIMAL(14,2) DEFAULT 0.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_site (site_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        $this->db->exec("ALTER TABLE isletme_projeleri ADD COLUMN IF NOT EXISTS kurul_onay_durumu VARCHAR(20) DEFAULT 'beklemede';");
        $this->db->exec("ALTER TABLE isletme_projeleri ADD COLUMN IF NOT EXISTS genel_kurul_turu ENUM('olagan','olaganustu') NULL;");
        $this->db->exec("ALTER TABLE isletme_projeleri ADD COLUMN IF NOT EXISTS genel_kurul_tarihi DATE NULL;");
        $this->db->exec("ALTER TABLE isletme_projeleri ADD COLUMN IF NOT EXISTS kurul_onay_tarihi DATE NULL;");
        $this->db->exec("ALTER TABLE isletme_projeleri ADD COLUMN IF NOT EXISTS divan_tutanak_no VARCHAR(100) NULL;");
        $this->db->exec("ALTER TABLE isletme_projeleri ADD COLUMN IF NOT EXISTS bildirim_yontemi ENUM('elden','taahhutlu','diger') NULL;");
        $this->db->exec("ALTER TABLE isletme_projeleri ADD COLUMN IF NOT EXISTS bildirim_tarihi DATE NULL;");
        $this->db->exec("ALTER TABLE isletme_projeleri ADD COLUMN IF NOT EXISTS itiraz_var_mi TINYINT(1) DEFAULT 0;");
        $this->db->exec("ALTER TABLE isletme_projeleri ADD COLUMN IF NOT EXISTS itiraz_tarihi DATE NULL;");
        $this->db->exec("ALTER TABLE isletme_projeleri ADD COLUMN IF NOT EXISTS itiraz_karar_tarihi DATE NULL;");
        $this->db->exec("ALTER TABLE isletme_projeleri ADD COLUMN IF NOT EXISTS itiraz_sonucu TEXT NULL;");
        $this->db->exec("ALTER TABLE isletme_projeleri ADD COLUMN IF NOT EXISTS kesinlesme_tarihi DATE NULL;");
        $this->db->exec("ALTER TABLE isletme_projeleri ADD COLUMN IF NOT EXISTS paylandirma_esasi ENUM('arsa_payi','metrekare') NULL;");
        $this->db->exec("ALTER TABLE isletme_projeleri ADD COLUMN IF NOT EXISTS yonetim_plani_referans TEXT NULL;");
        $this->db->exec("ALTER TABLE isletme_projeleri ADD COLUMN IF NOT EXISTS imza_orani DECIMAL(5,2) NULL;");
        $this->db->exec("ALTER TABLE isletme_projeleri ADD COLUMN IF NOT EXISTS iik_belge_mi TINYINT(1) DEFAULT 0;");

        $this->db->exec("CREATE TABLE IF NOT EXISTS isletme_projesi_kalemleri (
            id INT AUTO_INCREMENT PRIMARY KEY,
            proje_id INT NOT NULL,
            tip ENUM('gelir','gider') NOT NULL,
            kategori VARCHAR(100) NOT NULL,
            tutar DECIMAL(14,2) NOT NULL,
            aciklama TEXT,
            FOREIGN KEY (proje_id) REFERENCES isletme_projeleri(id) ON DELETE CASCADE,
            INDEX idx_proje (proje_id),
            INDEX idx_tip (tip)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        $this->db->exec("CREATE TABLE IF NOT EXISTS isletme_projesi_paylasim (
            id INT AUTO_INCREMENT PRIMARY KEY,
            proje_id INT NOT NULL,
            daire_id INT NOT NULL,
            pay_oran DECIMAL(10,6) NOT NULL,
            yillik_gider_pay DECIMAL(14,2) NOT NULL,
            aylik_avans DECIMAL(14,2) NOT NULL,
            FOREIGN KEY (proje_id) REFERENCES isletme_projeleri(id) ON DELETE CASCADE,
            INDEX idx_proje (proje_id),
            INDEX idx_daire (daire_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function saveProject(array $project, array $gelirKalemleri = [], array $giderKalemleri = []): int
    {
        $this->db->beginTransaction();
        try {
            $insert = $this->db->prepare("INSERT INTO {$this->table} (
                site_id, proje_adi, apartman_site_adi, adres, donem_baslangic, donem_bitis, kanuni_dayanak,
                varsayimlar, metodoloji, enflasyon_oran, rezerv_tutar, odeme_plani, takvim, guncelleme_mekanizmasi, durum
            ) VALUES (
                :site_id, :proje_adi, :apartman_site_adi, :adres, :donem_baslangic, :donem_bitis, :kanuni_dayanak,
                :varsayimlar, :metodoloji, :enflasyon_oran, :rezerv_tutar, :odeme_plani, :takvim, :guncelleme_mekanizmasi, :durum
            )");

            $insert->execute([
                ':site_id' => $project['site_id'],
                ':proje_adi' => $project['proje_adi'],
                ':apartman_site_adi' => $project['apartman_site_adi'],
                ':adres' => $project['adres'] ?? null,
                ':donem_baslangic' => $project['donem_baslangic'],
                ':donem_bitis' => $project['donem_bitis'],
                ':kanuni_dayanak' => $project['kanuni_dayanak'] ?? null,
                ':varsayimlar' => $project['varsayimlar'] ?? null,
                ':metodoloji' => $project['metodoloji'] ?? null,
                ':enflasyon_oran' => $project['enflasyon_oran'] ?? 0,
                ':rezerv_tutar' => $project['rezerv_tutar'] ?? 0,
                ':odeme_plani' => $project['odeme_plani'] ?? null,
                ':takvim' => $project['takvim'] ?? null,
                ':guncelleme_mekanizmasi' => $project['guncelleme_mekanizmasi'] ?? null,
                ':durum' => $project['durum'] ?? 'aktif',
            ]);

            $projeId = (int) $this->db->lastInsertId();

            if (!empty($gelirKalemleri)) {
                $stmtGelir = $this->db->prepare("INSERT INTO isletme_projesi_kalemleri (proje_id, tip, kategori, tutar, aciklama) VALUES (:proje_id, 'gelir', :kategori, :tutar, :aciklama)");
                foreach ($gelirKalemleri as $k) {
                    $stmtGelir->execute([
                        ':proje_id' => $projeId,
                        ':kategori' => $k['kategori'],
                        ':tutar' => $this->toDecimal($k['tutar']),
                        ':aciklama' => $k['aciklama'] ?? null,
                    ]);
                }
            }

            if (!empty($giderKalemleri)) {
                $stmtGider = $this->db->prepare("INSERT INTO isletme_projesi_kalemleri (proje_id, tip, kategori, tutar, aciklama) VALUES (:proje_id, 'gider', :kategori, :tutar, :aciklama)");
                foreach ($giderKalemleri as $k) {
                    $stmtGider->execute([
                        ':proje_id' => $projeId,
                        ':kategori' => $k['kategori'],
                        ':tutar' => $this->toDecimal($k['tutar']),
                        ':aciklama' => $k['aciklama'] ?? null,
                    ]);
                }
            }

            $this->updateTotals($projeId);
            $this->calculateShares($projeId);

            $this->db->commit();
            return $projeId;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function updateProject(int $projeId, array $project, array $gelirKalemleri = [], array $giderKalemleri = []): void
    {
        $this->db->beginTransaction();
        try {
            $upd = $this->db->prepare("UPDATE {$this->table} SET 
                proje_adi = :proje_adi,
                apartman_site_adi = :apartman_site_adi,
                adres = :adres,
                donem_baslangic = :donem_baslangic,
                donem_bitis = :donem_bitis,
                kanuni_dayanak = :kanuni_dayanak,
                varsayimlar = :varsayimlar,
                metodoloji = :metodoloji,
                enflasyon_oran = :enflasyon_oran,
                rezerv_tutar = :rezerv_tutar,
                odeme_plani = :odeme_plani,
                takvim = :takvim,
                guncelleme_mekanizmasi = :guncelleme_mekanizmasi,
                durum = :durum
            WHERE id = :id");

            $upd->execute([
                ':proje_adi' => $project['proje_adi'],
                ':apartman_site_adi' => $project['apartman_site_adi'],
                ':adres' => $project['adres'] ?? null,
                ':donem_baslangic' => $project['donem_baslangic'],
                ':donem_bitis' => $project['donem_bitis'],
                ':kanuni_dayanak' => $project['kanuni_dayanak'] ?? null,
                ':varsayimlar' => $project['varsayimlar'] ?? null,
                ':metodoloji' => $project['metodoloji'] ?? null,
                ':enflasyon_oran' => $project['enflasyon_oran'] ?? 0,
                ':rezerv_tutar' => $project['rezerv_tutar'] ?? 0,
                ':odeme_plani' => $project['odeme_plani'] ?? null,
                ':takvim' => $project['takvim'] ?? null,
                ':guncelleme_mekanizmasi' => $project['guncelleme_mekanizmasi'] ?? null,
                ':durum' => $project['durum'] ?? 'aktif',
                ':id' => $projeId,
            ]);

            $this->db->prepare("DELETE FROM isletme_projesi_kalemleri WHERE proje_id = ?")->execute([$projeId]);
            $this->db->prepare("DELETE FROM isletme_projesi_paylasim WHERE proje_id = ?")->execute([$projeId]);

            if (!empty($gelirKalemleri)) {
                $stmtGelir = $this->db->prepare("INSERT INTO isletme_projesi_kalemleri (proje_id, tip, kategori, tutar, aciklama) VALUES (:proje_id, 'gelir', :kategori, :tutar, :aciklama)");
                foreach ($gelirKalemleri as $k) {
                    $stmtGelir->execute([
                        ':proje_id' => $projeId,
                        ':kategori' => $k['kategori'],
                        ':tutar' => $this->toDecimal($k['tutar']),
                        ':aciklama' => $k['aciklama'] ?? null,
                    ]);
                }
            }

            if (!empty($giderKalemleri)) {
                $stmtGider = $this->db->prepare("INSERT INTO isletme_projesi_kalemleri (proje_id, tip, kategori, tutar, aciklama) VALUES (:proje_id, 'gider', :kategori, :tutar, :aciklama)");
                foreach ($giderKalemleri as $k) {
                    $stmtGider->execute([
                        ':proje_id' => $projeId,
                        ':kategori' => $k['kategori'],
                        ':tutar' => $this->toDecimal($k['tutar']),
                        ':aciklama' => $k['aciklama'] ?? null,
                    ]);
                }
            }

            $this->updateTotals($projeId);
            $this->calculateShares($projeId);

            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function updateTotals(int $projeId): void
    {
        $gelir = $this->sumKalem($projeId, 'gelir');
        $gider = $this->sumKalem($projeId, 'gider');

        $proj = $this->find($projeId);
        $rezerv = (float)($proj->rezerv_tutar ?? 0);
        $enflasyonOran = (float)($proj->enflasyon_oran ?? 0);
        $giderEnflasyonlu = $gider * (1 + ($enflasyonOran / 100));

        $netYillikGider = max(($giderEnflasyonlu - $gelir) + $rezerv, 0);
        $aylikAvansToplam = round($netYillikGider / 12, 2);

        $stmt = $this->db->prepare("UPDATE {$this->table} SET toplam_gelir = :gelir, toplam_gider = :gider, net_yillik_gider = :net, aylik_avans_toplam = :avans WHERE id = :id");
        $stmt->execute([
            ':gelir' => $gelir,
            ':gider' => $giderEnflasyonlu,
            ':net' => $netYillikGider,
            ':avans' => $aylikAvansToplam,
            ':id' => $projeId,
        ]);
    }

    protected function sumKalem(int $projeId, string $tip): float
    {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(tutar),0) AS toplam FROM isletme_projesi_kalemleri WHERE proje_id = ? AND tip = ?");
        $stmt->execute([$projeId, $tip]);
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return (float)($row->toplam ?? 0);
    }

    protected function toDecimal($value): float
    {
        if (is_string($value)) {
            $value = str_replace(['.', ','], ['', '.'], $value);
        }
        return (float)$value;
    }

    public function saveWithDetails(array $data, array $gelirKalemleri = [], array $giderKalemleri = []): int
    {
        $this->db->beginTransaction();
        try {
            $rowEncId = $this->saveWithAttr($data);
            $projeId = isset($data['id']) && (int)$data['id'] > 0 ? (int)$data['id'] : (int)Security::decrypt($rowEncId);

            $this->db->prepare("DELETE FROM isletme_projesi_kalemleri WHERE proje_id = ?")->execute([$projeId]);

            if (!empty($gelirKalemleri)) {
                $stmtGelir = $this->db->prepare("INSERT INTO isletme_projesi_kalemleri (proje_id, tip, kategori, tutar, aciklama) VALUES (:proje_id, 'gelir', :kategori, :tutar, :aciklama)");
                foreach ($gelirKalemleri as $k) {
                    $stmtGelir->execute([
                        ':proje_id' => $projeId,
                        ':kategori' => $k['kategori'],
                        ':tutar' => $this->toDecimal($k['tutar']),
                        ':aciklama' => $k['aciklama'] ?? null,
                    ]);
                }
            }

            if (!empty($giderKalemleri)) {
                $stmtGider = $this->db->prepare("INSERT INTO isletme_projesi_kalemleri (proje_id, tip, kategori, tutar, aciklama) VALUES (:proje_id, 'gider', :kategori, :tutar, :aciklama)");
                foreach ($giderKalemleri as $k) {
                    $stmtGider->execute([
                        ':proje_id' => $projeId,
                        ':kategori' => $k['kategori'],
                        ':tutar' => $this->toDecimal($k['tutar']),
                        ':aciklama' => $k['aciklama'] ?? null,
                    ]);
                }
            }

            $this->updateTotals($projeId);
            $this->calculateShares($projeId);

            $this->db->commit();
            return $projeId;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function calculateShares(int $projeId): void
    {
        $proj = $this->find($projeId);
        $siteId = (int)$proj->site_id;

        $netYillik = (float)$proj->net_yillik_gider;
        if ($netYillik <= 0) {
            $this->db->prepare("DELETE FROM isletme_projesi_paylasim WHERE proje_id = ?")->execute([$projeId]);
            return;
        }

        $Daireler = new DairelerModel();
        $daireler = $Daireler->SitedekiDaireler($siteId);

        $toplamArsaPayi = 0.0;
        foreach ($daireler as $d) {
            $toplamArsaPayi += (float)($d->arsa_payi ?? 0);
        }
        if ($toplamArsaPayi <= 0) {
            $this->db->prepare("DELETE FROM isletme_projesi_paylasim WHERE proje_id = ?")->execute([$projeId]);
            return;
        }

        $this->db->prepare("DELETE FROM isletme_projesi_paylasim WHERE proje_id = ?")->execute([$projeId]);
        $stmt = $this->db->prepare("INSERT INTO isletme_projesi_paylasim (proje_id, daire_id, pay_oran, yillik_gider_pay, aylik_avans) VALUES (:proje_id, :daire_id, :pay_oran, :yillik_gider_pay, :aylik_avans)");

        foreach ($daireler as $d) {
            $payOran = ((float)($d->arsa_payi ?? 0)) / $toplamArsaPayi;
            $yillikPay = round($netYillik * $payOran, 2);
            $aylikAvans = round($yillikPay / 12, 2);

            $stmt->execute([
                ':proje_id' => $projeId,
                ':daire_id' => (int)$d->id,
                ':pay_oran' => $payOran,
                ':yillik_gider_pay' => $yillikPay,
                ':aylik_avans' => $aylikAvans,
            ]);
        }
    }

    public function getProjectsBySite(int $siteId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE site_id = ? ORDER BY created_at DESC");
        $stmt->execute([$siteId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getProjectSummary(int $projeId): object
    {
        $proj = $this->find($projeId);
        $gelir = $this->sumKalem($projeId, 'gelir');
        $gider = $this->sumKalem($projeId, 'gider');
        $paylasimSayisi = $this->countPaylasim($projeId);
        return (object) [
            'proje' => $proj,
            'toplam_gelir' => $gelir,
            'toplam_gider' => $gider,
            'net_yillik_gider' => (float)$proj->net_yillik_gider,
            'aylik_avans_toplam' => (float)$proj->aylik_avans_toplam,
            'paylasim_sayisi' => $paylasimSayisi,
        ];
    }

    protected function countPaylasim(int $projeId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as c FROM isletme_projesi_paylasim WHERE proje_id = ?");
        $stmt->execute([$projeId]);
        return (int)($stmt->fetch(PDO::FETCH_OBJ)->c ?? 0);
    }

    public function getKalemleri(int $projeId, string $tip): array
    {
        $stmt = $this->db->prepare("SELECT kategori, tutar, aciklama FROM isletme_projesi_kalemleri WHERE proje_id = ? AND tip = ? ORDER BY id ASC");
        $stmt->execute([$projeId, $tip]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getPaylasimWithDetails(int $projeId): array
    {
        $sql = "SELECT p.daire_id, p.pay_oran, p.yillik_gider_pay, p.aylik_avans, d.daire_no, d.daire_kodu, b.blok_adi
                FROM isletme_projesi_paylasim p
                JOIN daireler d ON p.daire_id = d.id
                JOIN bloklar b ON d.blok_id = b.id
                WHERE p.proje_id = ?
                ORDER BY b.blok_adi, CAST(d.daire_no AS UNSIGNED) ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$projeId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}