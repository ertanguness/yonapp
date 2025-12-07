<?php \App\Helper\Security::checkLogin(); ?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Anketler</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Anketler</li>
        </ul>
    </div>
</div>

<div class="main-content">
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body table-responsive">
                            <table class="table table-hover table-bordered align-middle datatables" id="userSurveyList">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Başlık</th>
                                        <th>Durum</th>
                                        <th>Bitiş</th>
                                        <th>Onay</th>
                                        <th>Red</th>
                                        <th>İşlem</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
  
</div>

<script src="/pages/site-sakini/js/anket.js?v=<?php echo filemtime('pages/site-sakini/js/anket.js'); ?>"></script>
<script>
$(function(){ window.UserSurvey && window.UserSurvey.init && window.UserSurvey.init(); });
</script>
