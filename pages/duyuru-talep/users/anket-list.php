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
                            <table class="table table-hover table-bordered align-middle" id="userSurveyList">
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
    
  <div class="modal" id="surveyDetailModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="detailTitle">Anket Detayı</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <style>
              #detailOptions .list-group-item{ padding: .75rem 1rem; }
              #voteStats .progress-bar{ background-image: linear-gradient(90deg, #4c6fff, #6ea8fe); }
            </style>
            <div class="mb-3" id="surveyInfo">
              <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                <span class="badge bg-secondary" id="detailStatusBadge">Durum</span>
                <span class="badge bg-light text-dark">Bitiş: <span id="detailEndDate">-</span></span>
              </div>
            </div>
            <p id="detailDesc" class="text-muted"></p>
            <div class="fw-semibold mb-2">Seçenekler</div>
            <div class="card mb-2">
              <div class="card-body p-0">
                <div id="detailOptions" class="list-group list-group-flush"></div>
              </div>
            </div>
            <div id="voteStats" class="mt-3" style="display:none;">
              <div class="fw-semibold mb-2">Oy Sonuçları</div>
              <div id="voteStatsBody"></div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" id="btnClose"><i class="feather-x"></i> Kapat</button>
            <button type="button" class="btn btn-primary" id="btnApprove"><i class="feather-check"></i> Oy Ver</button>
          </div>
        </div>
      </div>
    </div>
</div>

<script src="/pages/duyuru-talep/users/js/anket.js"></script>
<script>
$(function(){ window.UserSurvey && window.UserSurvey.init && window.UserSurvey.init(); });
</script>