<?php

require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';

use App\Services\MailGonderService;



if($_POST['action'] == 'email_gonder'){
    $toEmail = json_decode($_POST['to'] ?? '', true);
    $cc = json_decode($_POST['cc'] ??'', true);
    $bcc = json_decode($_POST['bcc'] ??'', true);
    $konu = $_POST['subject'] ?? 'Varsayılan Konu';
    $mesaj = $_POST['message'] ?? 'Varsayılan Mesaj';
    //mail adreslerini array olarak al
    $toEmail = is_array($toEmail) ? $toEmail : [$toEmail];

if(!is_array($toEmail) || empty($toEmail)){
    throw new Exception("Geçerli bir alıcı listesi gönderilmedi.");
}


    if(MailGonderService::gonder(
         $toEmail,
         $konu,
         $mesaj,
            [],
            $cc,
            $bcc
        )){
            // Başarılı gönderim işlemi
            $status = "success";
            $message = count($toEmail) . " alıcıya email başarıyla gönderildi.";
            
    } else {
        $status = "error";
        $message = "Email gönderilemedi.";
        //throw new Exception("Email gönderilemedi.");
    }

    $res = [
        'status' => $status,
        'message'=> $message,
        'tomail' => $toEmail
 
    ];

    
    echo json_encode($res);
    exit;

}
// file_put_contents('php://input', $json_data);