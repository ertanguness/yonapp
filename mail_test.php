<?php

require_once  'configs/bootstrap.php';

use App\Services\MailGonderService;

$to = "beyzade83@gmail.com";
$subject = "Gelen posta";
$message = "Bu bir  mailidir.";

if (MailGonderService::gonder($to, $subject, $message)) {

    echo "Mail başarıyla gönderildi.";
} else {
    echo "Mail gönderilemedi.";
}
