<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Anket Sonuçları</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item"><a href="index?p=polls/admin/list">Anket Listesi</a></li>
            <li class="breadcrumb-item">Sonuçlar</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="notice/admin/survey-list">
                <i class="feather-arrow-left me-2"></i> Listeye Dön
            </button>
           
        </div>
    </div>
</div>

<div class="main-content">
    <div class="row">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Anket Sonuçları</h5>
                            <span class="text-muted small" id="surveyMeta"></span>
                        </div>
                        <div class="card-body">
                            <p class="mb-4">Toplam Oy Sayısı: <strong id="totalVotes">0</strong></p>

                            <!-- Seçenek Sonuçları -->
                            <div id="resultsWrapper"></div>

                            <script>
                            (function(){
                              var surveyId = <?php echo isset($id) ? intval(\App\Helper\Security::decrypt($id)) : 0; ?>;
                              if(!surveyId){ return; }
                              fetch('/pages/notice/admin/api.php?action=survey_results&survey_id='+surveyId)
                                .then(r=>r.json())
                                .then(data=>{
                                  document.getElementById('totalVotes').innerText = data.total || 0;
                                  var wrap = document.getElementById('resultsWrapper');
                                  wrap.innerHTML = '';
                                  (data.options||[]).forEach(function(o,idx){
                                    var color = idx%2===0 ? 'primary' : 'secondary';
                                    var block = `
                                      <div class="mb-4">
                                        <label class="fw-semibold">${o.option_text}</label>
                                        <div class="progress mb-1">
                                          <div class="progress-bar bg-${color}" style="width:${o.percent}%">${o.percent}%</div>
                                        </div>
                                        <small class="text-muted">Oy Sayısı: ${o.votes}</small>
                                      </div>`;
                                    wrap.insertAdjacentHTML('beforeend', block);
                                  });
                                });
                            })();
                            </script>

                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
