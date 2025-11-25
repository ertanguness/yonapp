<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Duyurular</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item active">Duyurular</li>
        </ul>
    </div>
</div>

<div class="main-content">
    <div class="container-xl">
        <div class="row">
            <!-- Aktif Duyurular -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Duyurular</h5>
                        <div class="list-group" id="active-announcements">
                            <!-- Örnek duyurular -->
                            <div class="list-group-item d-flex justify-content-between align-items-start flex-column flex-md-row" data-id="1">
                                <div>
                                    <h6 class="mb-1">Toplantı Duyurusu</h6>
                                    <small class="text-muted">10 Nisan 2025</small>
                                    <p class="mb-1 mt-2">Genel kurul toplantısı 15 Nisan tarihinde yapılacaktır.</p>
                                    <div class="okundu-bilgi d-none text-success fw-bold mt-2">
                                        <i class="feather-check-circle"></i> <span class="okundu-tarih"></span> tarihinde okunmuştur.
                                    </div>
                                </div>
                                <div>
                                    <button class="btn btn-outline-success btn-sm okudum-btn mt-3 mt-md-0">Okudum</button>
                                </div>
                            </div>

                            <div class="list-group-item d-flex justify-content-between align-items-start flex-column flex-md-row" data-id="2">
                                <div>
                                    <h6 class="mb-1">Aidat Artışı</h6>
                                    <small class="text-muted">01 Nisan 2025</small>
                                    <p class="mb-1 mt-2">Nisan ayından itibaren aidatlar %10 artacaktır.</p>
                                    <div class="okundu-bilgi d-none text-success fw-bold mt-2">
                                        <i class="feather-check-circle"></i> <span class="okundu-tarih"></span> tarihinde okunmuştur.
                                    </div>
                                </div>
                                <div>
                                    <button class="btn btn-outline-success btn-sm okudum-btn mt-3 mt-md-0">Okudum</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const okudumBtns = document.querySelectorAll(".okudum-btn");

    okudumBtns.forEach(btn => {
        btn.addEventListener("click", function () {
            const parentItem = this.closest(".list-group-item");

            const now = new Date();
            const datetimeStr = now.toLocaleDateString('tr-TR') + ' ' + now.toLocaleTimeString('tr-TR');

            this.remove();
            const okunduBilgi = parentItem.querySelector(".okundu-bilgi");
            okunduBilgi.querySelector(".okundu-tarih").textContent = datetimeStr;
            okunduBilgi.classList.remove("d-none");
        });
    });
});
</script>
