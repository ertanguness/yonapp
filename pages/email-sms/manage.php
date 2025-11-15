<?php
require __DIR__ . '/content/email.php';
require __DIR__ . '/content/sms.php';


/*
// Formdan gelen veriler
$notificationType = $_POST['notificationType'];
$recipientId = $_POST['recipientId'];
$subject = $_POST['subject'];
$message = $_POST['message'];


// Veritabanından alıcı bilgilerini çek
$query = "SELECT email, telefon FROM kisiler WHERE id = $recipientId";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $recipient = $result->fetch_assoc();

    if ($notificationType == 'email') {
        // E-posta gönderme
        $email = $recipient['email'];
        $response = sendEmailNotification($email, $subject, $message);
        echo $response;
    } elseif ($notificationType == 'sms') {
        // SMS gönderme
        $phone = $recipient['telefon'];
        $response = sendSMSNotification($phone, $message);
        echo $response;
    }
} else {
    echo "Alıcı bilgisi bulunamadı.";
}

$conn->close(); */
?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Bildirim Gönderimi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Haberleşme</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-header-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Back</span>
                </a>
            </div>
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">

                <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="bildirimler">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </button>
                <button type="button" class="btn btn-primary" id="saveNotification">
                    <i class="feather-send me-2"></i>
                    Gönder
                </button>

            </div>
        </div>
        <div class="d-md-none d-flex align-items-center">
            <a href="javascript:void(0)" class="page-header-right-open-toggle">
                <i class="feather-align-right fs-20"></i>
            </a>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <form action="" id="notificationForm">
                            <div class="card-body custom-card-action p-0">
                                <div class="card-body">
                                    <div class="row mb-4 align-items-center">
                                        <!-- Bildirim Türü -->
                                        <div class="col-lg-2">
                                            <label for="notificationType" class="fw-semibold">Bildirim Türü:</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group flex-nowrap">
                                                <div class="input-group-text"><i class="feather-bell"></i></div>
                                                <select name="notificationType" id="notificationType" class="form-select select2" required>
                                                    <option value="">Seçiniz</option>
                                                    <option value="email">E-Posta</option>
                                                    <option value="sms">SMS</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Gönderilecek Kişi -->
                                        <div class="col-lg-2">
                                            <label for="recipientIds" class="fw-semibold">Gönderilecek Kişiler:</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group flex-nowrap">
                                                <div class="input-group-text"><i class="feather-users"></i></div>
                                                <select name="recipientIds[]" id="recipientIds" class="form-select select2" multiple required>
                                                    <!-- Veritabanından dinamik olarak doldurulacak -->
                                                    <option value="1">Ali Veli</option>
                                                    <option value="2">Ayşe Yılmaz</option>
                                                    <option value="3">Fatma Kaya</option>
                                                    <option value="4">Mehmet Demir</option>
                                                </select>
                                            </div>
                                            <small class="text-muted">CTRL veya SHIFT tuşlarına basarak birden fazla kişi seçebilirsiniz.</small>
                                        </div>
                                    </div>

                                    <div class="row mb-4 align-items-center">
                                        <!-- Konu Başlığı -->
                                        <div class="col-lg-2">
                                            <label for="subject" class="fw-semibold">Konu Başlığı:</label>
                                        </div>
                                        <div class="col-lg-10">
                                            <div class="input-group flex-nowrap">
                                                <div class="input-group-text"><i class="feather-tag"></i></div>
                                                <input type="text" name="subject" id="subject" class="form-control" placeholder="Konu Başlığını Giriniz" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4 align-items-center">
                                        <!-- Mesaj -->
                                        <div class="col-lg-2">
                                            <label for="message" class="fw-semibold">Mesaj:</label>
                                        </div>
                                        <div class="col-lg-10">
                                            <div class="input-group flex-nowrap">
                                                <div class="input-group-text"><i class="feather-message-circle"></i></div>
                                                <textarea name="message" id="message" rows="4" class="form-control" placeholder="Mesajınızı Giriniz..." required></textarea>
                                            </div>
                                        </div>
                                    </div>



                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>