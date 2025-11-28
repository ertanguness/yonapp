<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Aidat Ödeme Geçmişi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Aidat Ödeme Listesi</li>
        </ul>
    </div>
</div>

<div class="main-content">
    <?php
    $title = "Aidat Ödeme Geçmişi";
    $text = "Bu sayfada yapılan tüm aidat ödemelerini görebilirsiniz. Ödeme detaylarını inceleyebilir ve gerekli durumlarda müdahale edebilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>

    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover datatables" id="paymentTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Blok</th>
                                            <th>Daire No</th>
                                            <th>Adı Soyadı</th>
                                            <th>Ödeme Tutarı</th>
                                            <th>Ödeme Yöntemi</th>
                                            <th>Durum</th>
                                            <th>Tarih</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>A</td>
                                            <td>1</td>
                                            <td>Ali Veli</td>
                                            <td>100 TL</td>
                                            <td>Nakit</td>
                                            <td id="status-1">Beklemede</td>
                                            <td>11.11.2000</td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-2">
                                                    <button class="btn btn-success btn-md confirmPayment" data-id="1">
                                                        <i class="feather-check-circle"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-md rejectPayment" data-id="1">
                                                        <i class="feather-x-circle"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).on("click", ".confirmPayment", function () {
        var paymentId = $(this).data("id");

        Swal.fire({
            title: "Ödemeyi Onaylamak İstiyor Musunuz?",
            text: "Bu işlemi geri alamazsınız!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#28a745",
            cancelButtonColor: "#d33",
            confirmButtonText: "Evet, Onayla!",
            cancelButtonText: "İptal"
        }).then((result) => {
            if (result.isConfirmed) {
                $("#status-" + paymentId).text("Onaylandı").addClass("text-success");
                Swal.fire("Başarılı", "Ödeme başarıyla onaylandı!", "success");
            }
        });
    });

    $(document).on("click", ".rejectPayment", function () {
        var paymentId = $(this).data("id");

        Swal.fire({
            title: "Ödemeyi Reddetmek İstiyor Musunuz?",
            text: "Bu işlemi geri alamazsınız!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Evet, Reddet!",
            cancelButtonText: "İptal"
        }).then((result) => {
            if (result.isConfirmed) {
                $("#status-" + paymentId).text("Reddedildi").addClass("text-danger");
                Swal.fire("Başarılı", "Ödeme reddedildi!", "error");
            }
        });
    });
</script>
