<!DOCTYPE html>
<html lang="tr">

<head>
<?php include './partials/head.php' ?>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hesap Aktivasyonu</title>
    <style>
        body {
            font-family: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            text-align: center;
        }

        .header {
            text-align: center;
            padding: 10px 0;
        }

        .content {
            text-align: center;
        }

        .content h1 {
            color: #333333;
        }

        .content p {
            color: #666666;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            margin: 20px 0;
            background-color: #007bff;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
        }

        .button:hover {
            background-color: #0056b3;
        }

        .footer {
            text-align: center;
            padding: 10px 0;
            color: #999999;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="assets/images/icons/activation.png" width="96" alt="Logo">
        </div>
        <div class="content text-center">
            <h1>Hesap Aktivasyonu</h1>
            <p>Hesabınızı aktifleştirmek için aşağıdaki düğmeye tıklayın. Bu bağlantı 24 saat içinde geçerliliğini
                yitirecektir.</p>
            <a href="<?php echo $activate_link; ?>" class="btn btn-lg btn-success w-50" style="display: inline-block; text-align: center;">Hesabı Aktifleştir</a>
            
            <br>
            <p style="margin-top: 10px;">Üstteki düğme ile ilgili bir sorun mu yaşıyorsunuz? Lütfen şu URL'yi kopyalayın: <a
                    href="<?php echo $activate_link; ?>" class="link"><?php echo $activate_link; ?></a> ve tarayıcınızda
                açın.</p>
            <p>Eğer bu talebi siz yapmadıysanız, lütfen bu mesajı dikkate almayın.</p>
        </div>
        <div class="footer">
            <p>© 2025 YonApp. Tüm hakları saklıdır.</p>
        </div>
    </div>
</body>

</html>