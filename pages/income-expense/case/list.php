<div class="page-header">
  <div class="page-header-left d-flex align-items-center">
    <div class="page-header-title">
      <h5 class="m-b-10">Finans Yönetimi</h5>
    </div>
    <ul class="breadcrumb">
      <li class="breadcrumb-item"><a href="index?p=home">Ana Sayfa</a></li>
      <li class="breadcrumb-item">Kasa Listesi</li>
    </ul>
  </div>
  <div class="page-header-right ms-auto">
    <div class="page-header-right-items d-flex align-items-center gap-2">
      <a href="#" class="btn btn-primary route-link" data-page="income-expense/case/manage">
        <i class="feather-plus me-2"></i>
        Yeni Kasa Ekle
      </a>
    </div>
  </div>
</div>

<div class="main-content">
  <div class="container-xl">
    <?php
    $title = "Kasa Listesi!";
    $text = "Tanımlı kasalarınızı görüntüleyebilir, yeni kasa ekleyebilir veya düzenleyebilirsiniz. Gelir/Gider işlemleri için varsayılan kasayı unutmayın!";
    require_once 'pages/components/alert.php';
    ?>

    <div class="row row-deck row-cards">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4 class="card-title">Tüm Kasalar</h4>
          </div>
          <div class="table-responsive">
            <table class="table table-hover table-bordered datatables">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Kasa Adı</th>
                  <th>Kasa Türü</th>
                  <th>Para Birimi</th>
                  <th>Varsayılan mı?</th>
                  <th>Bakiye</th>
                  <th>Açıklama</th>
                  <th class="text-center">İşlem</th>
                </tr>
              </thead>
              <tbody>
                <?php
                // Örnek veri — Gerçek veriler veritabanından alınmalı
                $kasalar = [
                  [
                    'id' => 1,
                    'adi' => 'Nakit Kasa',
                    'tur' => 'Nakit',
                    'kur' => '₺',
                    'default' => true,
                    'bakiye' => 12500.75,
                    'aciklama' => 'Ofis içi nakit hareketler için'
                  ],
                  [
                    'id' => 2,
                    'adi' => 'İş Bankası',
                    'tur' => 'Banka',
                    'kur' => '₺',
                    'default' => false,
                    'bakiye' => 84000,
                    'aciklama' => 'Aidat ödemeleri için kullanılan hesap'
                  ]
                ];

                foreach ($kasalar as $kasa) {
                  echo '<tr>';
                  echo '<td>' . $kasa['id'] . '</td>';
                  echo '<td>' . htmlspecialchars($kasa['adi']) . '</td>';
                  echo '<td>' . htmlspecialchars($kasa['tur']) . '</td>';
                  echo '<td>' . htmlspecialchars($kasa['kur']) . '</td>';
                  echo '<td>' . ($kasa['default'] ? '<span class="badge bg-success">Evet</span>' : '<span class="badge bg-secondary">Hayır</span>') . '</td>';
                  echo '<td>' . number_format($kasa['bakiye'], 2) . '</td>';
                  echo '<td>' . htmlspecialchars($kasa['aciklama']) . '</td>';
                  echo '<td class="text-center">';
                  echo '<div class="hstack gap-2 justify-content-center">';
                  echo '<a href="#" class="avatar-text avatar-sm text-primary" title="Düzenle"><i class="feather-edit"></i></a>';
                  echo '<a href="#" class="avatar-text avatar-sm text-danger" title="Sil"><i class="feather-trash-2"></i></a>';
                  echo '<a href="javascript:void(0)" 
                           class="avatar-text avatar-sm text-info" 
                           title="Detay"
                           data-bs-toggle="modal" 
                           data-bs-target="#kasaDetayModal"
                           onclick="showKasaDetails(' . htmlspecialchars(json_encode($kasa)) . ')">
                           <i class="feather-info"></i>
                        </a>';
                  echo '</div>';
                  echo '</td>';
                  echo '</tr>';
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Kasa Detay Modal -->

<div class="modal fade" id="kasaDetayModal" tabindex="-1" aria-labelledby="kasaDetayModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content rounded-3 shadow-sm border-0">
      
      <!-- Başlık -->
      <div class="modal-header bg-primary text-white py-2 px-3">
        <h6 class="modal-title mb-0" id="kasaDetayModalLabel">
          <i class="feather-info me-2"></i> Kasa Detayları
        </h6>
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>

      <!-- İçerik -->
      <div class="modal-body px-4 py-3">
        <div class="mb-2">
          <small class="text-muted d-block">Kasa Adı</small>
          <div id="detayKasaAdi" class="fw-semibold text-dark">-</div>
        </div>

        <div class="mb-2">
          <small class="text-muted d-block">Kasa Türü</small>
          <div id="detayKasaTur" class="fw-semibold text-dark">-</div>
        </div>

        <div class="mb-2">
          <small class="text-muted d-block">Para Birimi</small>
          <div id="detayKasaKur" class="fw-semibold text-dark">-</div>
        </div>

        <div class="mb-2">
          <small class="text-muted d-block">Varsayılan mı?</small>
          <div id="detayKasaDefault" class="fw-semibold text-dark">-</div>
        </div>

        <div class="mb-2">
          <small class="text-muted d-block">Güncel Bakiye</small>
          <div id="detayKasaBakiye" class="fw-semibold text-dark">-</div>
        </div>

        <div class="mb-1">
          <small class="text-muted d-block">Açıklama</small>
          <div id="detayKasaAciklama" class="fw-semibold text-dark">-</div>
        </div>
      </div>

      <!-- Alt Buton -->
      <div class="modal-footer border-0 px-4 pt-0 pb-3">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Kapat</button>
      </div>

    </div>
  </div>
</div>


<script>
  function showKasaDetails(kasa) {
    document.getElementById('detayKasaAdi').innerText = kasa.adi || '-';
    document.getElementById('detayKasaTur').innerText = kasa.tur || '-';
    document.getElementById('detayKasaKur').innerText = kasa.kur || '-';
    document.getElementById('detayKasaDefault').innerHTML = kasa.default ? '<span class="badge bg-success">Evet</span>' : '<span class="badge bg-secondary">Hayır</span>';
    document.getElementById('detayKasaBakiye').innerText = new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(kasa.bakiye);
    document.getElementById('detayKasaAciklama').innerText = kasa.aciklama || '-';
  }
</script>
