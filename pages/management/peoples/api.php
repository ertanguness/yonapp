<?php
require_once dirname(__DIR__ ,levels: 3). '/configs/bootstrap.php';


$site_id = $_SESSION["site_id"];

use Model\KisilerModel;
use Model\SitelerModel;
use Model\BloklarModel;
use Model\DairelerModel;
use Model\DefinesModel;
use App\Helper\Security;
use App\Services\ExcelHelper;
use App\Services\FlashMessageService;
use App\Helper\Date as Date;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Helper\DefinesHelper;


$kisilerModel = new KisilerModel();




//Excelden Yükleme işlemi (Onboarding çözümleyici)
if ($_POST["action"] == "excel_upload_peoples_resolve") {
    $file = $_FILES['excelFile'] ?? null;
    $fileType = $file ? strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) : '';

    if (!$file || ($fileType !== 'xlsx' && $fileType !== 'xls')) {
        echo json_encode([
            "status" => "error",
            "message" => "Lütfen geçerli bir Excel dosyası yükleyin."
        ]);
        exit;
    }

    $sitelerModel = new SitelerModel();
    $bloklarModel = new BloklarModel();
    $dairelerModel = new DairelerModel();

    $ownerId = $_SESSION['user']->owner_id > 0 ? $_SESSION['user']->owner_id : ($_SESSION['user']->id ?? null);

    $processedCount = 0;
    $skippedCount = 0;
    $createdSites = 0;
    $createdBlocks = 0;
    $createdApartments = 0;
    $errorRows = [];

    try {
        $spreadsheet = IOFactory::load($file['tmp_name']);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->getRowIterator();

        if (!$rows->valid()) {
            echo json_encode([
                "status" => "error",
                "message" => "Yüklenen Excel dosyası boş veya okunamıyor."
            ]);
            exit;
        }

        $header = [];
        foreach ($rows->current()->getCellIterator() as $cell) {
            $header[$cell->getColumn()] = trim($cell->getValue() ?? '');
        }
        $rows->next();

        $db = \getDbConnection();
        $db->beginTransaction();

        while ($rows->valid()) {
            $row = $rows->current();
            $rowData = [];
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            foreach ($cellIterator as $cell) {
                $columnHeader = $header[$cell->getColumn()] ?? null;
                if ($columnHeader) {
                    $rowData[$columnHeader] = $cell->getValue();
                }
            }

            if (count(array_filter($rowData)) === 0) {
                $rows->next();
                continue;
            }

            $siteAdi    = trim($rowData['Site Adı*'] ?? $rowData['Site Adı'] ?? '');
            $blokAdi    = trim($rowData['Blok Adı*'] ?? $rowData['Blok Adı'] ?? '');
            $daireKodu  = trim($rowData['Daire Kodu*'] ?? $rowData['Daire Kodu'] ?? '');
            $daireNo    = trim($rowData['Daire No*'] ?? $rowData['Daire No'] ?? '');
            $adiSoyadi  = trim($rowData['Adı Soyadı*'] ?? $rowData['Adı Soyadı'] ?? '');
            $telefon    = trim($rowData['Telefon*'] ?? $rowData['Telefon'] ?? '');
            $kimlikNo   = trim($rowData['Kimlik No*'] ?? $rowData['Kimlik No'] ?? '');
            $dogumTarihi= trim(Date::convertExcelDate($rowData['Doğum Tarihi (gg.aa.yyyy)'] ?? $rowData['Doğum Tarihi'] ?? null) ?? '');
            $cinsiyetRaw= trim($rowData['Cinsiyet (Erkek/Kadın)'] ?? $rowData['Cinsiyet (E/K)'] ?? $rowData['Cinsiyet'] ?? '');
            $uyelikTipi = trim($rowData['Uyeliği (Kat Maliki/Kiracı)'] ?? $rowData['Uyelik Turu'] ?? $rowData['Uyelik Türü'] ?? 'Kat Maliki');
            $eposta     = $rowData['Eposta'] ?? $rowData['E-posta'] ?? null;
            $adres      = trim($rowData['Adres'] ?? '');
            $notlar     = trim($rowData['Notlar'] ?? '');
            $satinalma  = trim(Date::convertExcelDate($rowData['Satin Alma Tarihi'] ?? $rowData['Satın Alma Tarihi'] ?? null) ?? '');
            $girisTarihi= trim(Date::convertExcelDate($rowData['Giriş Tarihi'] ?? null) ?? '');
            $cikisTarihi= trim(Date::convertExcelDate($rowData['Çıkış Tarihi'] ?? $rowData['Cikis Tarihi'] ?? null) ?? '');
            $aktifMiRaw = trim($rowData['Aktiflik Durumu'] ?? '1');
            $mulkTipiName = trim(($rowData['Mülk Tipi'] ?? $rowData['Mülk Tipi*'] ?? $rowData['MulkTipi'] ?? $rowData['Mülk tipi'] ?? $rowData['Mülk Tipi (Konut/İşyeri)'] ?? 'Konut'));
            $daireTipiName = trim(($rowData['Daire Tipi'] ?? $rowData['Daire Tipi*'] ?? $rowData['DaireTipi'] ?? $rowData['Daire tipi'] ?? $rowData['Daire Tipi (Konut/İşyeri)'] ?? '3+1'));

            if (empty($siteAdi) || empty($blokAdi) || (empty($daireKodu) && empty($daireNo)) || empty($adiSoyadi)) {
                $errorRows[] = [
                    'row_index' => $row->getRowIndex(),
                    'error_message' => "Satır {$row->getRowIndex()}: 'Site Adı', 'Blok Adı', 'Daire Kodu/Daire No' ve 'Adı Soyadı' zorunludur.",
                    'data' => $rowData
                ];
                $rows->next();
                continue;
            }

            $cinsiyet = '';
            if ($cinsiyetRaw !== '') {
                $lc = mb_strtolower($cinsiyetRaw, 'UTF-8');
                if (in_array($lc, ['erkek','e','male'])) $cinsiyet = 'E';
                else if (in_array($lc, ['kadın','k','female','kadin'])) $cinsiyet = 'K';
                else $cinsiyet = $cinsiyetRaw;
            }

            $aktifMi = 1;
            $lcAktif = mb_strtolower($aktifMiRaw, 'UTF-8');
            if (in_array($lcAktif, ['0','hayır','pasif','false'])) $aktifMi = 0;
            if (in_array($lcAktif, ['1','evet','aktif','true'])) $aktifMi = 1;

            $siteRow = null;
            $stmtSite = $db->prepare("SELECT id FROM siteler WHERE user_id = ? AND site_adi = ? AND silinme_tarihi IS NULL LIMIT 1");
            $stmtSite->execute([$ownerId, $siteAdi]);
            $siteRow = $stmtSite->fetch(\PDO::FETCH_OBJ);
            if (!$siteRow) {
                $sitelerModel->saveWithAttr(['site_adi' => $siteAdi, 'user_id' => $ownerId, 'aktif_mi' => 1]);
                $createdSites++;
                $siteIdResolved = (int)$db->lastInsertId();
            } else {
                $siteIdResolved = (int)$siteRow->id;
            }

            $blok = $bloklarModel->findBlokBySiteAndName($siteIdResolved, $blokAdi);
            $blokIdResolved = $blok ? (int)$blok->id : 0;
            if (!$blok) {
                $bloklarModel->saveWithAttr(['site_id' => $siteIdResolved, 'blok_adi' => $blokAdi, 'aktif_mi' => 1]);
                $createdBlocks++;
                $blokIdResolved = (int)$db->lastInsertId();
            }

            if ($daireKodu !== '' && preg_match('/^\d+$/', $daireKodu)) {
                $daireKodu = '';
            }
            $finalDaireNo = $daireNo;
            if (empty($finalDaireNo) && !empty($daireKodu)) {
                if (preg_match('/\d+/', $daireKodu, $m)) {
                    $finalDaireNo = $m[0];
                } else {
                    $finalDaireNo = $daireKodu;
                }
            }
            $blockShort = trim(preg_replace('/\s*blok\s*/iu', '', $blokAdi));
            $blockShort = mb_strtoupper(mb_substr($blockShort, 0, 1, 'UTF-8'), 'UTF-8');
            $generatedCode = $blockShort . 'D' . $finalDaireNo;
            $codeToUse = !empty($daireKodu) ? $daireKodu : $generatedCode;

            $stmtApt = $db->prepare("SELECT id FROM daireler WHERE site_id = ? AND blok_id = ? AND daire_kodu = ? LIMIT 1");
            $stmtApt->execute([$siteIdResolved, $blokIdResolved, $codeToUse]);
            $aptRow = $stmtApt->fetch(\PDO::FETCH_OBJ);

            $daireTipiId = null;
            $definesModel = new DefinesModel();
            $resolvedDaireTipiName = $daireTipiName !== '' ? $daireTipiName : 'Konut';
            $nameLc = mb_strtolower($resolvedDaireTipiName, 'UTF-8');
            if (in_array($nameLc, ['konut','daire'])) {
                $resolvedDaireTipiName = 'Konut';
            } elseif (in_array($nameLc, ['isyeri','işyeri'])) {
                $resolvedDaireTipiName = 'İşyeri';
            }

            /**Daire Tipi ID'sini alır. Eğer yoksa oluşturur. */
            $daireTipiId = $definesModel->getApartmentTypeIdByName(
                                $siteIdResolved,
                                   DefinesHelper::TYPE_APARTMENT, 
                                   $resolvedDaireTipiName,
                              $mulkTipiName);
            if (!$daireTipiId) {
                $definesModel->saveWithAttr([
                    'site_id' => $siteIdResolved,
                    'mulk_tipi' => $mulkTipiName,
                    'type' => DefinesModel::TYPE_DAIRE_TIPI,
                    'define_name' => $resolvedDaireTipiName
                ]);
                $daireTipiId = (int)$db->lastInsertId();
            }

            if (!$aptRow) {
                $dairelerModel->saveWithAttr([
                    'site_id' => $siteIdResolved,
                    'blok_id' => $blokIdResolved,
                    'daire_no' => $finalDaireNo,
                    'daire_kodu' => $codeToUse,
                    'daire_tipi' => $daireTipiId,
                    'aktif_mi' => 1
                ]);
                $createdApartments++;
                $daireIdResolved = (int)$db->lastInsertId();
            } else {
                $daireIdResolved = (int)$aptRow->id;
                $stmtCurrCode = $db->prepare("SELECT daire_kodu, daire_tipi FROM daireler WHERE id = ? LIMIT 1");
                $stmtCurrCode->execute([$daireIdResolved]);
                $currRow = $stmtCurrCode->fetch(\PDO::FETCH_ASSOC);
                $currCode = $currRow['daire_kodu'] ?? null;
                $currType = $currRow['daire_tipi'] ?? null;
                if ($currCode && preg_match('/^\d+$/', $currCode)) {
                    $dairelerModel->updateWhere('id', $daireIdResolved, ['daire_kodu' => $codeToUse]);
                }
                if ($daireTipiId && (empty($currType) || (int)$currType === 0)) {
                    $dairelerModel->updateWhere('id', $daireIdResolved, ['daire_tipi' => $daireTipiId]);
                }
            }

            if (!isset($rowsToInsert)) {
                $rowsToInsert = [];
                $compositeSeen = [];
            }
            $compositeKey = implode('|', [
                $siteIdResolved,
                $blokIdResolved,
                $daireIdResolved,
                mb_strtolower($adiSoyadi, 'UTF-8'),
                $kimlikNo,
                $telefon,
                $girisTarihi
            ]);
            if (isset($compositeSeen[$compositeKey])) {
                $skippedCount++;
            } else {
                $stmtExist = $db->prepare("SELECT id FROM kisiler WHERE site_id = ? AND blok_id = ? AND daire_id = ? AND adi_soyadi = ? AND COALESCE(kimlik_no,'') = COALESCE(?, '') AND COALESCE(telefon,'') = COALESCE(?, '') AND COALESCE(giris_tarihi,'') = COALESCE(?, '') AND silinme_tarihi IS NULL LIMIT 1");
                $stmtExist->execute([$siteIdResolved, $blokIdResolved, $daireIdResolved, $adiSoyadi, $kimlikNo, $telefon, $girisTarihi]);
                $existsRow = $stmtExist->fetch(\PDO::FETCH_OBJ);
                if ($existsRow) {
                    $skippedCount++;
                } else {
                    $rowsToInsert[] = [
                        'site_id' => $siteIdResolved,
                        'blok_id' => $blokIdResolved,
                        'daire_id' => $daireIdResolved,
                        'kimlik_no' => $kimlikNo,
                        'adi_soyadi' => $adiSoyadi,
                        'dogum_tarihi' => $dogumTarihi,
                        'cinsiyet' => $cinsiyet,
                        'uyelik_tipi' => $uyelikTipi,
                        'telefon' => $telefon,
                        'sms_izni' => 0,
                        'eposta' => $eposta,
                        'adres' => $adres,
                        'notlar' => $notlar,
                        'satin_alma_tarihi' => $satinalma,
                        'giris_tarihi' => $girisTarihi,
                        'cikis_tarihi' => $cikisTarihi,
                        'aktif_mi' => $aktifMi
                    ];
                    $compositeSeen[$compositeKey] = true;
                }
            }

            $rows->next();
        }

        if (!empty($rowsToInsert)) {
            $kisilerModel->bulkInsert($rowsToInsert);
            $processedCount += count($rowsToInsert);
        }
        $db->commit();

        $result = [
            'status' => 'success',
            'message' => "İşlem tamamlandı: {$processedCount} kişi eklendi, {$skippedCount} kayıt atlandı. {$createdSites} site, {$createdBlocks} blok, {$createdApartments} daire oluşturuldu.",
            'data' => [
                'success_count' => $processedCount,
                'skipped_count' => $skippedCount,
                'error_rows' => $errorRows
            ]
        ];

        if (!empty($result['data']['error_rows'])) {
            try {
                $excelHelper = new ExcelHelper();
                $originalHeader = $excelHelper->getHeaders($file['tmp_name']);
                $errorFileUrl = $excelHelper->createErrorFile($result['data']['error_rows'], $originalHeader);
                FlashMessageService::add("error", "Bilgi", "Hatalı kayıtlar için bir Excel dosyası oluşturuldu. <a href='{$errorFileUrl}' target='_blank'>Dosyayı İndir</a>");
            } catch (\Exception $e) {
                error_log("Controller: Hata Excel'i oluşturulamadı: " . $e->getMessage());
            }
        }

        echo json_encode($result);
    } catch (\Throwable $e) {
        if (isset($db) && $db->inTransaction()) {
            $db->rollBack();
        }
        echo json_encode([
            "status" => "error",
            "message" => "İşlem sırasında bir hata oluştu: " . $e->getMessage()
        ]);
    }
}
//Excelden Yükleme işlemi (Önceki davranış)
if ($_POST["action"] == "excel_upload_peoples") {
    $file = $_FILES['excelFile'];
    $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($fileType !== 'xlsx' && $fileType !== 'xls') {
        echo json_encode([
            "status" => "error",
            "message" => "Lütfen geçerli bir Excel dosyası yükleyin."
        ]);
        exit;
    }

    $result = $kisilerModel->excelUpload($file['tmp_name'], $site_id);
  

    $errorFileUrl = null;
    
    if (!empty($result['data']['error_rows'])) {
        try {
            $excelHelper = new ExcelHelper();
            $originalHeader = $excelHelper->getHeaders($file['tmp_name']);
            $errorFileUrl = $excelHelper->createErrorFile($result['data']['error_rows'], $originalHeader);
            FlashMessageService::add("error","Bilgi","Hatalı kayıtlar için bir Excel dosyası oluşturuldu. <a href='{$errorFileUrl}' target='_blank'>Dosyayı İndir</a>");
        } catch (\Exception $e) {
             error_log("Controller: Hata Excel'i işlenirken bir sorun oluştu: " . $e->getMessage());
        }
    }

    if ($result['status'] === 'success') {
        echo json_encode([
            "status" => "success",
            "message" => $result['message'],
            "data" => $result['data']
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => $result['message']
        ]);
    }
}
