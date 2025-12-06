<?php
use Model\AnketModel;
use Model\AnketVoteModel;
use Model\AnketOyModel;
use App\Helper\Date;
use App\Helper\Helper;
$surveyId = (int)($_GET['id'] ?? 0);
$Anket = new AnketModel();
$Vote = new AnketVoteModel();
$VoteLegacy = new AnketOyModel();
$survey = null;
if ($surveyId > 0) {
    $survey = $Anket->find($surveyId);
}
if (!$survey) {
    $list = $Anket->findWhere(['status' => 'Aktif'], 'created_at DESC', 1);
    $survey = $list[0] ?? null;
}
$options = [];
$counts = [];
$totalVotes = 0;
$userId = (int)($_SESSION['user']->id ?? 0);
$userVoted = false;
$isPassiveByDate = false;
if ($survey) {
    $options = json_decode($survey->options_json ?? '[]', true) ?: [];
    $normalize = function($s){ return mb_strtolower(trim((string)$s)); };
    $optionsNorm = array_map($normalize, $options);
    if ($survey->id) {
        $countsNew = $Vote->getCountsByOption((int)$survey->id);
        $countsLegacy = $VoteLegacy->getResults((int)$survey->id);
        $map = [];
        foreach ($countsNew as $c) {
            $opt = $normalize($c['option_text']);
            $map[$opt] = ($map[$opt] ?? 0) + (int)$c['c'];
        }
        foreach ($countsLegacy['rows'] as $r) {
            $opt = $normalize($r['option_text']);
            $map[$opt] = ($map[$opt] ?? 0) + (int)$r['votes'];
        }
        foreach ($map as $k => $v) { $totalVotes += (int)$v; }
        $counts = $map;
        $chartCategories = $options;
        $chartCounts = [];
        foreach ($optionsNorm as $nopt) { $chartCounts[] = (int)($counts[$nopt] ?? 0); }
        $chartPercentages = [];
        if ($totalVotes > 0) {
            foreach ($chartCounts as $c) { $chartPercentages[] = round(($c * 100 / $totalVotes), 2); }
        } else {
            foreach ($chartCounts as $c) { $chartPercentages[] = 0; }
        }
        if ($userId > 0) { $userVoted = $Vote->getUserVote((int)$survey->id, $userId) !== null; }
        if (!empty($survey->end_date)) {
            $isPassiveByDate = strtotime($survey->end_date) < time();
        }
    }
}
?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Anketler</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/sakin/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Anketler</li>
        </ul>
    </div>
    </div>

<div class="main-content">
    <div class="row g-4">
        <div class="col-12">
            <div class="card rounded-3">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Anket</h5>
                    <a href="/sakin/anket-listesi" class="btn btn-light">Tümü</a>
                </div>
                <div class="card-body">
                    <?php if ($survey) { ?>
                        <div class="mb-4">
                            <div class="fw-semibold mb-2"><?php echo htmlspecialchars($survey->title ?? ''); ?></div>
                            <div class="fs-12 text-muted mb-1"><?php echo htmlspecialchars($survey->description ?? ''); ?></div>
                            <div class="fs-12 text-muted mb-3">Başlangıç: <?php echo $survey->start_date ? Date::dmy($survey->start_date) : '-'; ?></div>
                            <div class="fs-12 text-muted mb-3">Son tarih: <?php echo $survey->end_date ? Date::dmy($survey->end_date) : '-'; ?></div>
                            <?php if (!$userVoted && !empty($options)) { ?>
                            <div class="d-flex flex-column gap-2" id="voteOptions">
                                <?php foreach ($options as $opt) { ?>
                                <label class="d-flex align-items-center gap-2">
                                    <input type="radio" name="anket_option" class="form-check-input" value="<?php echo htmlspecialchars($opt); ?>" />
                                    <span><?php echo htmlspecialchars($opt); ?></span>
                                </label>
                                <?php } ?>
                            </div>
                            <div class="mt-3">
                                <button id="voteBtn" class="btn btn-primary">Oy Ver</button>
                            </div>
                            <?php } ?>
                        </div>
                        <?php if ($userVoted && !empty($options)) { ?>
                            <hr class="border-dashed">
                            <div>
                                <div class="fw-semibold mb-2">Sonuç</div>
                                <div id="anketResultChart" style="height:260px"></div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="alert alert-info mb-0">Henüz anket bulunamadı.</div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    var surveyId = <?php echo (int)($survey->id ?? 0); ?>;
    var hasChart = <?php echo ($userVoted && !empty($options)) ? 'true' : 'false'; ?>;
    var categories = <?php echo json_encode($chartCategories ?? [], JSON_UNESCAPED_UNICODE); ?>;
    var data = <?php echo json_encode($chartPercentages ?? [], JSON_UNESCAPED_UNICODE); ?>;
    function render(){
        if (!window.ApexCharts || !hasChart) return;
        new ApexCharts(document.querySelector('#anketResultChart'), {
            chart: { type: 'bar', height: 260, toolbar: { show: false } },
            series: [{ name: 'Oy Oranı', data: data }],
            xaxis: { categories: categories },
            colors: ['#5e60e8'],
            dataLabels: { enabled: true, formatter: function(val){ return val + '%'; } },
            yaxis: { min: 0, max: 100, labels: { formatter: function(val){ return val + '%'; } } },
            tooltip: { y: { formatter: function(val){ return val + '%'; } } },
            grid: { strokeDashArray: 4 }
        }).render();
    }
    if (hasChart) {
        if (!window.ApexCharts) {
            var s = document.createElement('script');
            s.src = '/assets/vendors/js/apexcharts.min.js';
            s.onload = render;
            document.body.appendChild(s);
        } else {
            render();
        }
    }
    var voteBtn = document.getElementById('voteBtn');
    var canVote = <?php echo (!$userVoted && !$isPassiveByDate && !empty($options)) ? 'true' : 'false'; ?>;
    if (voteBtn && surveyId > 0 && canVote) {
        voteBtn.addEventListener('click', function(){
            var sel = document.querySelector('input[name="anket_option"]:checked');
            if (!sel) return;
            var opt = sel.value;
            fetch('/pages/duyuru-talep/users/api/APIAnket.php?action=vote', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ id: surveyId, selected_option: opt }).toString()
            }).then(function(r){ return r.json(); })
              .then(function(){ window.location.search = '?id=' + surveyId; })
              .catch(function(){});
        });
    }
});
</script>
