<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Duyurular</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Haberleşme</li>
        </ul>
    </div>
</div>

<div class="main-content">
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <!-- Duyuru Kartı -->
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Aidat Artışı Hakkında</h5>
                            <p class="card-text">
                                2025 yılı için aidatlara %10 zam yapılması planlanmaktadır.
                                Görüşlerinizi oylamaya katılarak belirtebilirsiniz.
                            </p>

                            <!-- Oylama Alanı -->
                            <div class="border-top pt-3 mt-4">
                                <h6 class="fw-semibold mb-3">🗳️ Oylama: Aidat %10 artırılsın mı?</h6>
                                <form class="poll-form">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="pollOption1" id="option1_1" value="1">
                                        <label class="form-check-label" for="option1_1">Evet</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="pollOption1" id="option1_2" value="2">
                                        <label class="form-check-label" for="option1_2">Hayır</label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="pollOption1" id="option1_3" value="3">
                                        <label class="form-check-label" for="option1_3">Çekimser</label>
                                    </div>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="submitPoll(this)">Oyumu Gönder</button>
                                    <div class="poll-feedback text-success fw-semibold mt-2 d-none">
                                        ✅ Teşekkürler, oyunuz kaydedildi.
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Başka bir duyuru örneği -->
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Otopark Kullanım Düzenlemesi</h5>
                            <p class="card-text">
                                Otopark alanları için yeni kurallar önerildi. Her daireye 1 araç sınırlaması getirilsin mi?
                            </p>

                            <!-- Oylama Alanı -->
                            <div class="border-top pt-3 mt-4">
                                <h6 class="fw-semibold mb-3">🗳️ Oylama: Otopark sınırlaması getirilsin mi?</h6>
                                <form class="poll-form">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="pollOption2" id="option2_1" value="1">
                                        <label class="form-check-label" for="option2_1">Evet</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="pollOption2" id="option2_2" value="2">
                                        <label class="form-check-label" for="option2_2">Hayır</label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="pollOption2" id="option2_3" value="3">
                                        <label class="form-check-label" for="option2_3">Çekimser</label>
                                    </div>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="submitPoll(this)">Oyumu Gönder</button>
                                    <div class="poll-feedback text-success fw-semibold mt-2 d-none">
                                        ✅ Teşekkürler, oyunuz kaydedildi.
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- ... Yukarıdaki duyuru ve anket alanları buraya kadar aynı ... -->

                <!-- GEÇMİŞ ANKETLER BAŞLIĞI -->
                <div class="col-12 mt-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">📊 Geçmiş Anketler</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 40%;">Anket Başlığı</th>
                                            <th style="width: 30%;">Oylama Sonucu</th>
                                            <th style="width: 30%;">Tarih</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Aidat %10 artırılsın mı?</td>
                                            <td>
                                                <div class="text-success fw-bold">Evet - %68</div>
                                            </td>
                                            <td>10 Nisan 2025</td>
                                        </tr>
                                        <tr>
                                            <td>Otopark sınırlaması getirilsin mi?</td>
                                            <td>
                                                <div class="text-danger fw-bold">Hayır - %55</div>
                                            </td>
                                            <td>25 Mart 2025</td>
                                        </tr>
                                        <tr>
                                            <td>Bahçe düzenlemesi için bütçe ayrılsın mı?</td>
                                            <td>
                                                <div class="text-success fw-bold">Evet - %75</div>
                                            </td>
                                            <td>12 Şubat 2025</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- İleride yeni duyurular buraya eklenebilir -->

            </div>
        </div>
    </div>
</div>

<script>
    function submitPoll(button) {
        const form = button.closest('.poll-form');
        const feedback = form.querySelector('.poll-feedback');
        const inputs = form.querySelectorAll('input');

        inputs.forEach(input => {
            input.disabled = true;
        });

        button.disabled = true;
        feedback.classList.remove('d-none');
    }
</script>