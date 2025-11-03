<?php
// AJAX endpoint - Layout'suz çalışmalı
// Bootstrap'i dahil et (session, autoloader vs.)

// İlk test: Bootstrap yüklenmeden önce
ob_start();

try {
    require_once __DIR__ . '/../configs/bootstrap.php';
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Bootstrap failed: ' . $e->getMessage()]);
    exit();
}

use App\Helper\Security;
use App\Helper\Helper;
use App\Services\Gate;
use Model\KasaModel;
use Model\KasaHareketModel;

// JSON response için header (en başta set et)
header('Content-Type: application/json');

// Hata ayıklama modu (geçici - production'da kaldır)
$debug = true;

// Authentication ve authorization kontrolü
try {
    Gate::authorizeOrDie("income_expense_add_update");
} catch (Exception $e) {
    echo json_encode([
        'draw' => 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'Authorization failed: ' . $e->getMessage()
    ]);
    exit();
}

try {
    $KasaHareket = new KasaHareketModel();
    
    // Kasa ID'yi al
    $kasa_id = $_SESSION["kasa_id"] ?? 1;
    
    // DataTables parametrelerini al
    $draw = isset($_POST['draw']) ? (int)$_POST['draw'] : 1;
    $start = isset($_POST['start']) ? (int)$_POST['start'] : 0;
    $length = isset($_POST['length']) ? (int)$_POST['length'] : 50;
    $searchValue = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
    
    // Sıralama parametreleri
    $orderColumnIndex = isset($_POST['order'][0]['column']) ? (int)$_POST['order'][0]['column'] : 0;
    $orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'desc';
    
    // Kolon isimleri (DataTables kolon sırasına göre)
    $columns = [
        0 => 'islem_tarihi',
        1 => 'islem_tipi',
        2 => 'adi_soyadi',
        3 => 'tutar',
        4 => 'kategori',
        5 => 'aciklama'
    ];
    
    $orderColumn = $columns[$orderColumnIndex] ?? 'islem_tarihi';
    
    // Toplam kayıt sayısı (filtresiz)
    $recordsTotal = $KasaHareket->getKasaHareketleriCount($kasa_id);
    
    // Filtrelenmiş kayıt sayısı (arama varsa)
    $recordsFiltered = $KasaHareket->getKasaHareketleriCount($kasa_id, $searchValue);
    
    // Verileri getir
    $kasaHareketleri = $KasaHareket->getKasaHareketleriPaginated(
        $kasa_id,
        $start,
        $length,
        $searchValue,
        $orderColumn,
        $orderDir
    );
    
    // DataTables formatına dönüştür
    $data = [];
    foreach ($kasaHareketleri as $hareket) {
        // İşlem tipi badge'i
        $islemTipi = $hareket->islem_tipi === 'Gelir' 
            ? '<span class="badge bg-success">Gelir</span>' 
            : '<span class="badge bg-danger">Gider</span>';
        
        // Kişi bilgisi
        $kisiBilgi = $hareket->adi_soyadi 
            ? $hareket->adi_soyadi . ($hareket->daire_kodu ? ' (' . $hareket->daire_kodu . ')' : '')
            : '-';
        
        // Tutar formatı
        $tutar = $hareket->islem_tipi === 'Gelir'
            ? '<span class="text-success">+' . Helper::formattedMoney($hareket->tutar) . '</span>'
            : '<span class="text-danger">-' . Helper::formattedMoney($hareket->tutar) . '</span>';
        
        // Kategori
        $kategori = $hareket->kategori ?? '-';
        
        // Açıklama
        $aciklama = $hareket->aciklama ? Helper::short($hareket->aciklama, 50) : '-';
        
        // Tarih formatı
        $tarih = date('d.m.Y H:i', strtotime($hareket->islem_tarihi));
        
        // İşlem butonları
        $encrypted_id = Security::encrypt($hareket->id);
        $actions = '
            <div class="btn-group" role="group">
                <a href="/gelir-gider-islemleri/' . $encrypted_id . '/detay" 
                   class="btn btn-sm btn-info" title="Detay">
                    <i class="fa fa-eye"></i>
                </a>
                <a href="/gelir-gider-islemleri/' . $encrypted_id . '/duzenle" 
                   class="btn btn-sm btn-warning" title="Düzenle">
                    <i class="fa fa-edit"></i>
                </a>
                <button type="button" class="btn btn-sm btn-danger delete-btn" 
                        data-id="' . $encrypted_id . '" title="Sil">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        ';
        
        $data[] = [
            $tarih,
            $islemTipi,
            $kisiBilgi,
            $tutar,
            $kategori,
            $aciklama,
            $actions
        ];
    }
    
    // DataTables response
    $response = [
        'draw' => $draw,
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'data' => $data
    ];
    
    // Debug modu
    if (isset($debug) && $debug) {
        $response['debug'] = [
            'kasa_id' => $kasa_id,
            'data_count' => count($data),
            'request' => [
                'draw' => $draw,
                'start' => $start,
                'length' => $length,
                'search' => $searchValue
            ]
        ];
    }
    
    // Output buffer'ı temizle (JSON'dan ÖNCE)
    $bufferContent = ob_get_clean();
    
    // Eğer buffer'da beklenmeyen içerik varsa logla
    if (!empty($bufferContent) && isset($debug) && $debug) {
        error_log("Unexpected output: " . $bufferContent);
    }
    
    echo json_encode($response);
    exit(); // Layout render edilmesini engelle
    
} catch (Exception $e) {
    // Output buffer'ı temizle
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Hata durumunda boş response
    http_response_code(500);
    echo json_encode([
        'draw' => isset($draw) ? $draw : 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    exit(); // Layout render edilmesini engelle
}
