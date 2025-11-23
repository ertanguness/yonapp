<?php \App\Services\Gate::authorizeOrDie('survey_admin_page'); ?>
<?php $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH); $seg = explode('/', trim($path,'/')); $enc = end($seg); $id = \App\Helper\Security::decrypt($enc); $Survey = new \Model\AnketModel(); $Vote = new \Model\AnketVoteModel(); $row = $Survey->find((int)$id); $opts = json_decode($row->options_json ?? '[]', true); $counts = $Vote->getCountsByOption((int)$id); $map = []; $total = 0; foreach ($counts as $c){ $map[$c['option_text']] = (int)$c['c']; $total += (int)$c['c']; } ?>
<div class="page-header">
  <div class="page-header-left d-flex align-items-center">
    <div class="page-header-title"><h5 class="m-b-10">Anket Sonuçları</h5></div>
    <ul class="breadcrumb"><li class="breadcrumb-item"><a href="/anket-listesi" class="route-link">Liste</a></li><li class="breadcrumb-item">Sonuçlar</li></ul>
  </div>
  <div class="page-header-right ms-auto">
    <a href="/anket-listesi" class="btn btn-outline-secondary route-link"><i class="feather-arrow-left me-2"></i> Listeye Dön</a>
  </div>
</div>
<div class="main-content">
  <div class="container-xl">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-2"><?= htmlspecialchars($row->title ?? '') ?></h6>
        <p class="text-muted mb-4"><?= htmlspecialchars($row->description ?? '') ?></p>
        <div class="row g-3">
          <?php foreach ($opts as $o): $cnt = $map[$o] ?? 0; $pct = $total>0 ? round(($cnt/$total)*100) : 0; ?>
            <div class="col-12">
              <div class="d-flex justify-content-between small mb-1"><span><?= htmlspecialchars($o) ?></span><span>%<?= $pct ?> (<?= $cnt ?>)</span></div>
              <div class="progress" style="height: 10px;">
                <div class="progress-bar" role="progressbar" style="width: <?= $pct ?>%"></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>
