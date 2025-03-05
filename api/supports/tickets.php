<?php
//define("ROOT", $_SERVER['DOCUMENT_ROOT']);
use App\Helper\Security;

require_once '../../Database/require.php';
require_once '../../Model/SupportsModel.php';
require_once '../../Model/SupportsMessagesModel.php';
require_once "../../mail-settings.php";

$Supports = new SupportsModel();
$SupportsMessages = new SupportsMessagesModel();

if (isset($_POST['action']) && $_POST['action'] == 'saveSupportTicket') {
    $data = [
        'user_id' => $_SESSION['user']->id,
        'subject' => $_POST['subject'],
        'message' => $_POST['message'],
        'status' => 0,
        'program_name' => 'puantor'
    ];

    try {
        $lastInsertId = $Supports->saveWithAttr($data);

        // Destek talebi oluşturulduktan sonra destek mesajı oluşturuluyor
        $data = [
            'support_id' => Security::decrypt($lastInsertId),
            'message' => $_POST['message']
        ];
        $SupportsMessages->saveWithAttr($data);

        $status = "success";
        $message = "Destek talebiniz başarıyla oluşturuldu.";


        $ticket_number = Security::decrypt($lastInsertId);
        $ticket_subject = $_POST["subject"];
        $user_name = $_SESSION["user"]->full_name;
        $user_email = $_SESSION["user"]->email;
        $message_body = strip_tags($_POST["message"]);

        
        // ticket-mail.php dosyasını dahil et ve değişkenleri geçir
        ob_start();
        include(ROOT . "/pages/supports/ticket-mail.php");
        $body = ob_get_clean();


        // Alıcılar
        $mail->setFrom($_SESSION["user"]->email, 'Yeni Destek Talebi');
        $mail->addAddress('destek@puantor.com.tr');
        $mail->isHTML(true);

        $mail->Subject = 'Yeni Destek Talebi Bildirimi';
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);
        //Karakter seti
        $mail->CharSet = 'UTF-8';

        $mail->send();



    } catch (PDOException $ex) {
        $status = "error";
        $message = $ex->getMessage();
    }
    $res = [
        'status' => $status,
        'message' => $message
    ];
    echo json_encode($res);

}

if (isset($_POST['action']) && $_POST['action'] == 'newTicketMessage') {
    $support_id = Security::decrypt($_POST['support_id']);
    $data = [
        'support_id' => $support_id,
        'message' => $_POST['message']
    ];

    try {
        $lastInsertId = $SupportsMessages->saveWithAttr($data);
        $status = "success";
        $message = "Mesajınız başarıyla gönderildi.";

        try {

            $ticket_number = Security::decrypt($lastInsertId);
            $ticket_subject = $Supports->find($support_id)->subject;
            $user_name = $_SESSION["user"]->full_name;
            $user_email = $_SESSION["user"]->email;
            $message_body = strip_tags($_POST["message"]);



            // ticket-mail.php dosyasını dahil et ve değişkenleri geçir
            ob_start();
            include(ROOT . "/pages/supports/ticket-mail.php");
            $body = ob_get_clean();


            // Alıcılar
            $mail->setFrom($_SESSION["user"]->email, 'Yeni Destek mesajı');
            $mail->addAddress('destek@puantor.com.tr');
            $mail->isHTML(true);

            $mail->Subject = 'Destek Mesajı Bildirimi';
            $mail->Body = $body;
            $mail->AltBody = strip_tags($body);
            //Karakter seti
            $mail->CharSet = 'UTF-8';

            $mail->send();
        } catch (Exception $e) {
            echo "E-posta gönderilemedi. Hata: {$mail->ErrorInfo}";
        }

    } catch (PDOException $ex) {
        $status = "error";
        $message = $ex->getMessage();
    }


    $res = [
        'status' => $status,
        'message' => $message
    ];
    echo json_encode($res);
}

if (isset($_POST['action']) && $_POST['action'] == 'closeTicket') {
    $id = Security::decrypt($_POST['id']);
    $data = [
        'id' => $id,
        'status' => 1
    ];

    try {
        $Supports->saveWithAttr($data);
        $status = "success";
        $message = "Destek talebi başarıyla kapatıldı.";
    } catch (PDOException $ex) {
        $status = "error";
        $message = $ex->getMessage();
    }
    $res = [
        'status' => $status,
        'message' => $message
    ];
    echo json_encode($res);
}
