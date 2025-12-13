<?php

use App\Helper\FinansalHelper;
use App\Helper\KisiHelper;
use App\Helper\Security;
use App\Helper\Helper;
use App\Services\Gate;
use Model\KasaModel;
use Model\KasaHareketModel;
use App\Services\FlashMessageService;


Gate::authorizeOrDie("gelir_gider_ekle_guncelle_sil");

$Kasa = new KasaModel();
$KasaHareket = new KasaHareketModel();
$KisiHelper = new KisiHelper();

// Kasa ID'yi belirle
$kasa_id = null;
$kasa_hareketleri = [];

/** Sitenin herhangi bir kasası yoksa kasa ekleme sayfasına gönder */
if (!$Kasa->varsayilanKasa()) {
    FlashMessageService::add("warning", "Uyarı!", "Sitenizde herhangi bir kasa yok. Lütfen önce kasa ekleyin.");
    header("Location: /kasa-ekle");
    exit;
}

//1. Önce session'dan kontrol et
if (isset($_SESSION["kasa_id"]) && !empty($_SESSION["kasa_id"])) {
    $kasa_id = $_SESSION["kasa_id"];
    //  Helper::dd(["1-kasa_id" => $kasa_id]);

}

// 2. POST ile yeni seçim geldi mi?
if (isset($_POST['kasalar'])) {
    $kasa_id = Security::decrypt($_POST['kasalar']) ?? 0;
    $_SESSION["kasa_id"] = $kasa_id;
    //Helper::dd(($kasa_id));

    echo "<script>history.replaceState({}, '', '/gelir-gider-islemleri');</script>";
    $id = null;
}

// 3. URL parametresi var mı?
if (isset($id) && !empty($id)) {
    $kasa_id = Security::decrypt($id);
    $_SESSION["kasa_id"] = $kasa_id;
}


// 4. Hiçbiri yoksa varsayılan kasayı al
if (!$kasa_id) {
    try {
        $varsayilanKasa = $Kasa->varsayilanKasa();
        $kasa_id = $varsayilanKasa->id ?? null;
        $_SESSION["kasa_id"] = $kasa_id;
        //Helper::dd(["4-kasa_id" => $kasa_id]);
    } catch (Exception $e) {
        // Hata durumunda fallback
    }
}

// echo "id " . ($_SESSION['kasa_id'] ?? 0);

// Eğer şifreli token geldiyse çöz ve $_GET'e uygula
if (isset($_GET['token']) && $_GET['token'] !== '') {
    try {
        $decoded = Security::decrypt($_GET['token']);
        $arr = json_decode($decoded, true);
        if (is_array($arr)) {
            foreach ($arr as $k => $v) {
                $_GET[$k] = $v;
            }
        }
    } catch (\Throwable $e) {
        // token hatalıysa sessizce yok say
    }
}

// Filtre parametrelerini oku (GET – form submit)
$startDateIn = $_GET['startDate'] ?? null; // d.m.Y
$endDateIn   = $_GET['endDate'] ?? null;   // d.m.Y
$incExpType  = $_GET['incExpType'] ?? 'all'; // all|income|expense

// d.m.Y -> Y-m-d dönüşümü
$toYmd = function ($val) {
    if (!$val) return null;
    $dt = DateTime::createFromFormat('d.m.Y', $val);
    return $dt ? $dt->format('Y-m-d') : null;
};

$startYmd = $toYmd($startDateIn);
$endYmd   = $toYmd($endDateIn);

// Eski yapıya dön: hareketleri modelden al ve sunucu tarafında render et
if ($startYmd || $endYmd || ($incExpType && strtolower($incExpType) !== 'all')) {
    $yon = '';
    if ($incExpType === 'income') {
        $yon = 'Gelir';
    } elseif ($incExpType === 'expense') {
        $yon = 'Gider';
    }
    // Tarihler yoksa ay için mantıklı varsayılan atayalım
    $startYmd = $startYmd ?: date('Y-m-01');
    $endYmd   = $endYmd   ?: date('Y-m-t');
    $kasa_hareketleri = $KasaHareket->getKasaHareketleriByDateRange($kasa_id, $startYmd, $endYmd, $yon);
    // Finansal özet de aynı filtrelerle
    $KasaFinansalDurum = $Kasa->KasaFinansalDurumByDateRange($kasa_id, $startYmd, $endYmd, $yon);
} else {
    $kasa_hareketleri = $KasaHareket->getKasaHareketleri($kasa_id);
    $KasaFinansalDurum = $Kasa->KasaFinansalDurum($kasa_id);
}



?>




<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Finans Yönetimi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Gelir Gider İşlemleri</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items d-flex align-items-center gap-2">
            <div>
                <form method="post" id="kasalar">
                    <?php echo FinansalHelper::KasaSelect("kasalar", $kasa_id) ?>
                </form>
            </div>

            <div class="dropdown" data-bs-toggle="tooltip" data-bs-placement="top" title="Filtre Uygula">
                <a href="javascript:void(0);" class="btn btn-icon btn-light-brand" id="filterBtn">
                    <i class="feather-filter"></i>
                </a>
            </div>
            <script>
                $(function() {
                    $('#filterBtn').on('click', function() {
                        $('#collapseOne').collapse("toggle");
                    });
                });
            </script>
            <div class="dropdown" data-bs-toggle="tooltip" data-bs-placement="top" title="Verileri Dışa Aktar">
                <a class="btn btn-icon btn-light-brand" data-bs-toggle="dropdown" data-bs-offset="0, 10" data-bs-auto-close="outside" aria-expanded="false">
                    <i class="feather-download"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end" style="">
                    <a href="javascript:void(0);" class="dropdown-item export" data-format="pdf">
                        <i class="bi bi-filetype-pdf me-3"></i>
                        <span>PDF</span>
                    </a>
                    <a href="javascript:void(0);" class="dropdown-item export" data-format="csv">
                        <i class="bi bi-filetype-csv me-3"></i>
                        <span>CSV</span>
                    </a>
                    <a href="javascript:void(0);" class="dropdown-item export" data-format="xml">
                        <i class="bi bi-filetype-xml me-3"></i>
                        <span>XML</span>
                    </a>
                    <a href="javascript:void(0);" class="dropdown-item export" data-format="txt">
                        <i class="bi bi-filetype-txt me-3"></i>
                        <span>Text</span>
                    </a>
                    <a href="javascript:void(0);" class="dropdown-item export" id="exportExcel" data-format="excel">
                        <i class="bi bi-filetype-exe me-3"></i>
                        <span>Excel</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="javascript:void(0);" class="dropdown-item export" data-format="print">
                        <i class="bi bi-printer me-3"></i>
                        <span>Print</span>
                    </a>
                </div>
            </div>

            <a href="/excelden-gelir-gider-yukle" class="btn btn-icon btn-light-brand" id="excelImportBtn"
                data-bs-toggle="tooltip" data-bs-placement="top" title="Excel'den Gider Yükle">
                <i class="feather-upload"></i>
            </a>

            <button type="button" class="btn btn-primary" id="btnGelirGiderEkle">
                <i class="feather-plus me-2"></i> Yeni Gelir/Gider Ekle
            </button>
        </div>
    </div>
</div>
<div class="main-content">

    <?php
    $title = "Gelir ve Gider Listesi";
    $text = "Site gelir ve giderlerinizi buradan takip edebilir, yeni işlemler ekleyebilir, düzenleyebilir veya silebilirsiniz.";
    require_once 'pages/components/alert.php'
    ?>

    <!-- [Mini Card] start -->
    <div class="row ">
        <div class="col-xxl-4 col-md-6">
            <div class="card card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="me-3">
                        <h5 class="fs-4" id="toplamGelir"><?php echo Helper::formattedMoney($KasaFinansalDurum->toplam_gelir ?? 0); ?></h5>
                        <span class="text-muted">Toplam Gelir</span>
                    </div>
                    <div class="avatar-text avatar-lg bg-success text-white rounded">
                        <i class="feather-trending-up"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-4 col-md-6">
            <div class="card card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="me-3">
                        <h5 class="fs-4" id="toplamGider"><?php echo Helper::formattedMoney($KasaFinansalDurum->toplam_gider ?? 0); ?></h5>
                        <span class="text-muted">Toplam Gider</span>
                    </div>
                    <div class="avatar-text avatar-lg bg-danger text-white rounded">
                        <i class="feather-trending-down"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-4 col-md-6">
            <div class="card card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="me-3">
                        <h5 class="fs-4" id="netKalan"><?php echo Helper::formattedMoney(($KasaFinansalDurum->bakiye ?? 0)); ?></h5>
                        <span class="text-muted">Net Kalan</span>
                    </div>
                    <div class="avatar-text avatar-lg bg-primary text-white rounded">
                        <i class="feather-bar-chart-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [Mini Card] end -->

    <!-- [Filtreleme] start -->
    <div class="row ">
        <div id="collapseOne" class="accordion-collapse collapse page-header-collapse">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Raporları Filtrele</h5>
                    <form id="filterForm">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="startDate" class="form-label">Başlangıç Tarihi</label>
                                <input type="text" id="startDate" name="startDate" class="form-control flatpickr"
                                    value="<?php echo htmlspecialchars($_GET['startDate'] ?? date("01.m.Y")); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="endDate" class="form-label">Bitiş Tarihi</label>
                                <input type="text" id="endDate" name="endDate" class="form-control flatpickr"
                                    value="<?php echo htmlspecialchars($_GET['endDate'] ?? date("t.m.Y")); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="incExpType" class="form-label">Gelir/Gider Türü</label>
                                <select id="incExpType" name="incExpType" class="form-control select2">
                                    <?php $selType = $_GET['incExpType'] ?? 'all'; ?>
                                    <option value="all" <?php echo ($selType === 'all') ? 'selected' : ''; ?>>Tümü</option>
                                    <option value="income" <?php echo ($selType === 'income') ? 'selected' : ''; ?>>Gelir</option>
                                    <option value="expense" <?php echo ($selType === 'expense') ? 'selected' : ''; ?>>Gider</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="ara" class="form-label">Ara</label>
                                <button type="submit" class="btn btn-primary">Filtrele</button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
    <!-- [Filtreleme] bitiş -->

    <!-- Liste Tablosu -->
    <div class="row row-deck row-cards mb-5">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="gelirGiderTable" class="table table-hover table-bordered datatables no-footer">
                            <thead >
                                <tr>
                                    <th data-filter="date">Tarih</th>
                                    <th data-filter="string">İşlem Türü</th>
                                    <th data-filter="string">Daire Kodu</th>
                                    <th data-filter="string">Hesap Adı</th>
                                    <th data-filter="number">Tutar</th>
                                    <th data-filter="number">Bakiye</th>
                                    <th data-filter="string">Kategori</th>
                                    <th data-filter="string">Alt Tür</th>
                                    <th data-filter="string">Makbuz No</th>
                                    <th data-filter="string">Açıklama</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Liste Tablosu Bitiş -->
</div>

<style>
    .option-card {
        border: 1px dashed #e0e0e0;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .option-card:hover {
        border-color: #6c757d;
    }

    .option-card.selected {
        border-color: #0d6efd;
        background-color: #f0f8ff;
    }

    .option-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 5px;
    }

    .option-title {
        font-weight: 600;
        color: #333;
    }

    .option-price {
        font-weight: 600;
        color: #0d6efd;
    }

    .option-desc {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 0;
    }

    input[type="radio"] {
        margin-right: 10px;
    }

    .radio-label {
        display: flex;
        align-items: flex-start;
        width: 100%;
        cursor: pointer;
    }

    .radio-content {
        flex-grow: 1;
    }
</style>

<!-- Gelir Gider Modal -->
<div class="modal fade" id="gelirGiderModal" tabindex="-1" aria-labelledby="gelirGiderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content gelir-gider-modal-content">
            <div class="text-center my-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Yükleniyor...</span>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- JavaScript için ek kod -->
<script>
    $(function() {
        // Re-entrancy guard for export double-click issues
        let __export_in_flight = false;



        $("#btnGelirGiderEkle").on("click", function() {
            $.get('/pages/finans-yonetimi/gelir-gider/modal/gelir_gider_modal.php', function(data) {
                $('.gelir-gider-modal-content').html(data);
                $('#gelirGiderModal').modal('show');

                //Modaldaki select2'leri başlat
                $(".modal .select2").select2({
                    dropdownParent: $("#gelirGiderModal"),
                });
                $("#islem_tarihi").flatpickr({
                    dateFormat: "d.m.Y H:i",
                    locale: "tr",
                    enableTime: true,
                    minuteIncrement: 1,
                    allowInput: true
                })
                gelirGiderKalemleriGetir();
            });
        });


        // Modali kapatınca sayfayı yenile (sunucu tarafı render)
        $('#gelirGiderModal').on('hidden.bs.modal', function() {
            //window.location.reload();
        });

        //#kasalar'da değişiklik olduğunda
        $("#kasalar").on("change", function() {
            //kasalar formunu submit et
            $("#kasalar").submit();
        });

        //flatpickr (modal tarih)
        $("#islem_tarihi").flatpickr({
            dateFormat: "d.m.Y H:i",
            locale: "tr",
            enableTime: true,
            minuteIncrement: 1,
            allowInput: true
        })

        // Filtre formundaki tarih alanları
        $(".flatpickr").flatpickr({
            dateFormat: "d.m.Y",
            locale: "tr",
            allowInput: true
        });


        $(".modal .select2").select2({
            dropdownParent: $("#gelirGiderModal"),
            tags: true
        });

        // Filtre formunu şifreli (token) GET ile gönder (double-bind önleme)
        $('#filterForm').off('submit.filter').on('submit.filter', async function(e) {
            e.preventDefault();
            const payload = {
                startDate: $('#startDate').val(),
                endDate: $('#endDate').val(),
                incExpType: ($('#incExpType').val() || 'all')
            };
            try {
                const res = await fetch('/pages/finans-yonetimi/gelir-gider/token.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data && data.ok) {
                    window.location.href = '/gelir-gider-islemleri?token=' + encodeURIComponent(data.token);
                    return;
                }
            } catch (err) {
                // düşerse normal GET'e geri dön
            }
            const qs = new URLSearchParams(payload).toString();
            window.location.href = '/gelir-gider-islemleri?' + qs;
        });

        // Dışa aktar menüsü: filtrelere göre indir (token ile) – double-download önleme
        $(document).off('click.export').on('click.export', '.export', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (__export_in_flight) {
                return false;
            }
            __export_in_flight = true;
            const format = $(this).data('format') || 'xlsx';
            const sd = ($('#startDate').val() || '').trim();
            const ed = ($('#endDate').val() || '').trim();
            const typeSel = ($('#incExpType').val() || 'all');
            const toIso = (dmy) => {
                const m = dmy.match(/^(\d{2})\.(\d{2})\.(\d{4})$/);
                return m ? `${m[3]}-${m[2]}-${m[1]}` : '';
            };
            const payload = {
                startDate: sd,
                endDate: ed,
                incExpType: typeSel,
                // Ek sağlamlık için ISO da ekle (sunucu iki formatı da destekler)
                start: toIso(sd),
                end: toIso(ed),
                type: typeSel
            };
            // Header aramaları
            const headerMap = ['q_date', 'q_islem', 'q_daire', 'q_hesap', 'q_tutar', 'q_bakiye', 'q_kategori', 'q_makbuz', 'q_aciklama'];
            const headerInputs = $('#gelirGiderTable thead').find('input, select');
            headerInputs.each(function(idx) {
                const v = ($(this).val() || '').toString().trim();
                if (v && headerMap[idx]) payload[headerMap[idx]] = v;
            });
            try {
                const res = await fetch('/pages/finans-yonetimi/gelir-gider/token.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data && data.ok) {
                    const url = '/pages/finans-yonetimi/gelir-gider/export.php?format=' + encodeURIComponent(format) + '&token=' + encodeURIComponent(data.token);
                    window.location.href = url;
                    setTimeout(() => {
                        __export_in_flight = false;
                    }, 2000);
                    return false;
                }
            } catch (err) {
                // yoksa plain GET ile devam
            }
            const params = new URLSearchParams({
                format,
                ...payload
            });
            window.location.href = '/pages/finans-yonetimi/gelir-gider/export.php?' + params.toString();
            setTimeout(() => {
                __export_in_flight = false;
            }, 2000);
            return false;
        });

    });
</script>

<style>
    .optgroup-label {
        font-weight: bold;
        color: #495057;
        background-color: #f8f9fa;
    }

    #gelirGiderTable {
        width: 100% !important;
    }
</style>
<script>
    // (function(){
    //     const nav = performance.getEntriesByType('navigation')[0];
    //     const metrics = { LCP: 0, CLS: 0, FID: 0, TTFB: nav ? nav.responseStart : 0 };
    //     let lcp = 0; let cls = 0; let fidSet = false;
    //     try {
    //         new PerformanceObserver(function(list){
    //             const entries = list.getEntries();
    //             for (let i=0;i<entries.length;i++) {
    //                 const e = entries[i];
    //                 lcp = Math.max(lcp, (e.renderTime || e.loadTime || 0));
    //             }
    //             metrics.LCP = lcp;
    //         }).observe({ type:'largest-contentful-paint', buffered:true });
    //     } catch(e) {}
    //     try {
    //         new PerformanceObserver(function(list){
    //             const entries = list.getEntries();
    //             for (let i=0;i<entries.length;i++) {
    //                 const e = entries[i];
    //                 if (!e.hadRecentInput) { cls += e.value; }
    //             }
    //             metrics.CLS = Math.round(cls*1000)/1000;
    //         }).observe({ type:'layout-shift', buffered:true });
    //     } catch(e) {}
    //     try {
    //         new PerformanceObserver(function(list){
    //             const e = list.getEntries()[0];
    //             if (e && !fidSet) { metrics.FID = e.processingStart - e.startTime; fidSet = true; }
    //         }).observe({ type:'first-input', buffered:true });
    //     } catch(e) {}
    //     function flush(){
    //         const data = {
    //             page: window.location.pathname,
    //             ts: Date.now(),
    //             LCP: metrics.LCP,
    //             FID: metrics.FID,
    //             CLS: metrics.CLS,
    //             TTFB: metrics.TTFB
    //         };
    //         try { localStorage.setItem('yonapp_web_vitals', JSON.stringify(data)); } catch(e) {}
    //         console.log('WebVitals', data);
    //     }
    //     addEventListener('visibilitychange', function(){ if (document.visibilityState === 'hidden') flush(); }, { once:false });
    //     addEventListener('load', function(){ setTimeout(flush, 0); });
    // })();



    $(document).ready(function() {

let url = '/pages/finans-yonetimi/gelir-gider/api.php';

       

    });
</script>