<!DOCTYPE html>
<html lang="zxx">

<?php
include './partials/head.php';
require_once 'Database/require.php';
require_once 'Model/UserModel.php';
require_once 'Model/RolesModel.php';
require_once 'Model/Auths.php';
require_once 'Model/RoleAuthsModel.php';
require_once 'Model/Company.php';
require_once 'App/Helper/security.php';

use Database\Db;
use App\Helper\Security;

$db = new Db();
$User = new UserModel();
$company = new Company();
$Roles = new Roles();
$Auths = new Auths();
$RoleAuths = new RoleAuthsModel();



?>
<script src="https://www.google.com/recaptcha/api.js?hl=tr" async defer></script>

<script>
    setTimeout(function() {
        $('.alert-danger').each(function() {
            $(this).fadeOut(500, function() {
                $(this).remove();
            });
        });
    });
    feather.replace();
</script>

<body>
    <!--! ================================================================ !-->
    <!--! [Start] Main Content !-->
    <!--! ================================================================ !-->
    <main class="auth-cover-wrapper">
        <div class="auth-cover-content-inner">
            <div class="auth-cover-content-wrapper">
                <div class="auth-img">
                    <img src="assets/images/auth/auth-cover-register-bg.svg" alt="" class="img-fluid">
                </div>
            </div>
        </div>
        <div class="auth-cover-sidebar-inner">
            <div class="auth-cover-card-wrapper">
                <div class="auth-cover-card p-sm-5 ">
                    <div class="wd-50 mb-5 sm-wd-50 mx-lg-4">
                        <img src="assets/images/yonapp-logo.jpg" style="width: 600%; height: auto; ">
                    </div>
                    <h4 class="fs-16 fw-bold mb-3 text-center"> Apartman & Site Yönetiminde Yeni Dönem!</h4>
                    <h2 class="fs-20 fw-bolder mb-4 text-center">Hesap Oluştur</h2>

                    <?php

                    if (isset($_POST['action']) && $_POST['action'] == 'saveUser') {
                        $recaptchaSecret = '6LfHuWYqAAAAAI4GfJIXZxpeoQGKDFN-Tr24766z';
                        $recaptchaResponse = $_POST['g-recaptcha-response'];

                        // reCAPTCHA doğrulama isteği
                        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptchaSecret&response=$recaptchaResponse");
                        $responseKeys = json_decode($response, true);

                        $full_name = preg_replace('/\s+/', ' ', trim($_POST['full_name']));
                        $company_name = preg_replace('/\s+/', ' ', trim($_POST['company_name']));
                        $email = preg_replace('/\s+/', ' ', trim($_POST['email']));
                        $password = preg_replace('/\s+/', ' ', trim($_POST['password']));

                        //Ad Soyad alanı boş bırakıldıysa hata mesajı verilir
                        if (empty($full_name)) {
                            echo alertdanger('Ad Soyad alanı boş bırakılamaz.');
                            //ad soyad 3 karakterden az ise hata mesajı verilir
                        } elseif (strlen($full_name) < 3) {
                            echo alertdanger('Ad Soyad en az 3 karakter olmalıdır.');

                            //firma adı alanı boş bırakıldıysa hata mesajı verilir
                        } elseif (empty($company_name)) {
                            echo alertdanger('Firma adı boş bırakılamaz.');

                            //firma adı 3 karakterden az ise hata mesajı verilir
                        } elseif (strlen($company_name) < 3) {
                            echo alertdanger('Firma adı en az 3 karakter olmalıdır.');

                            //email alanı boş bırakıldıysa hata mesajı verilir
                        } elseif (empty($email)) {
                            echo alertdanger('Email alanı boş bırakılamaz.');

                            //şifre alanı boş bırakıldıysa hata mesajı verilir
                        } elseif (empty($password)) {
                            echo alertdanger('Şifre alanı boş bırakılamaz.');

                            //şifre alanı en az 6 karakter olmalıdır
                        } elseif (strlen($password) < 6) {
                            echo alertdanger('Şifre en az 6 karakter olmalıdır.');

                            //şifre alanında büyük harf, küçük harf ve rakam olmalıdır
                        } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
                            echo alertdanger('Şifre en az bir büyük harf, bir küçük harf ve bir rakam içermelidir.');


                            //email adresi geçerli bir email adresi olup olmadığı kontrol edilir
                        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            echo alertdanger('Geçerli bir email adresi giriniz.');

                            //şartlar ve koşullar kabul edilmediyse hata mesajı verilir
                        } else if (!isset($_POST['terms_of_service'])) {
                            echo alertdanger('Şartlar ve koşulları kabul etmelisiniz.');

                            //Tüm kontrollerden geçildiyse kullanıcı kaydı yapılır
                        } else if (intval($responseKeys["success"]) !== 1) {
                            echo alertdanger('Lütfen reCAPTCHA doğrulamasını yapınız.');

                            //Email ile daha önce kayıt olunmuşsa hata mesajı verilir
                        } else if ($User->isEmailExists($email)) {
                            echo alertdanger('Bu email adresi ile daha önce kayıt olunmuş.');

                            //Tüm kontrollerden geçildiyse kullanıcı kaydı yapılır


                        } else {
                            $data = [
                                'id' => 0,
                                'full_name' => Security::escape($_POST['full_name']),
                                'email' => Security::escape($_POST['email']),
                                'status' => 0,
                                'user_roles' => 1,
                                'is_main_user' => 1,
                                'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                            ];
                            try {
                                $db->beginTransaction();

                                //Kullanıcı kaydı yapılır
                                $lastInsertUserId = $User->saveWithAttr($data);

                                //Girdiği firma adı ile yeni bir firma kaydedilir
                                $data = [
                                    'firm_name' => Security::escape($_POST['company_name']),
                                    'user_id' => Security::decrypt($lastInsertUserId),
                                ];
                                $lastInsertFirmId = $company->saveMyFirms($data);

                                //Firmaya Admin isimli bir Kullanıcı grubu atanır
                                $data = [
                                    "id" => 0,
                                    "firm_id" => Security::decrypt($lastInsertFirmId),
                                    "roleName" => 'Admin',
                                    "main_role" => 1
                                ];
                                $lastInsertRoleId = $Roles->saveWithAttr($data);

                                //Kaydedilen Yetki grubuna tüm yetkiler atanır

                                //yetki tablosundaki tüm id'ler alınır
                                $auths = $Auths->all();
                                //id'leri aralarında virgül olacak şekilde birleştirilir
                                $auths = implode(',', array_column($auths, 'id'));
                                //oluşturulan yetki grubuna yetkiler atanır
                                $data = [
                                    "role_id" => Security::decrypt($lastInsertRoleId),
                                    "auth_ids" => $auths
                                ];
                                $RoleAuths->saveWithAttr($data);


                                //kaydedilen firma ve role kullanıcıya atanır
                                $data = [
                                    "id" => Security::decrypt($lastInsertUserId),
                                    'firm_id' => Security::decrypt($lastInsertFirmId),
                                    'user_roles' => Security::decrypt($lastInsertRoleId)
                                ];
                                //Kullanıcı GÜncellenir
                                $User->saveWithAttr($data);

                                //Kayıttan sonra kullanıcıya mail gönderilir

                                //Şuan ki zamanı token olarak oluştur
                                $token = (Security::encrypt(time() + 3600));

                                // $token = urlencode(bin2hex(random_bytes(32)));
                                $activate_link = "http://yonapp.com.tr/register-activate.php?email=" . ($email) . "&token=" . $token;

                                // Token ve e-posta adresini veritabanına kaydetme
                                $data = [
                                    'id' => Security::decrypt($lastInsertUserId),
                                    'activate_token' => ($token),
                                ];
                                $User->setActivateToken($data);

                                //**********EPOSTA GÖNDERME ALANI */
                                // mail şablonunu dahil etme

                                ob_start();
                                include 'register-success-email.php';
                                $content = ob_get_clean();


                                try {
                                    //mail sınıfı ve ayarlarını dahil etme
                                    require_once "mail-settings.php";

                                    // Alıcılar
                                    $mail->setFrom('bilgi@puantor.com.tr', 'Puantor');
                                    $mail->addAddress($email);
                                    $mail->isHTML(true);

                                    // E-posta konusu ve içeriği
                                    $mail->Subject = 'Aktivasyon Bağlantısı';
                                    $mail->Body = $content;
                                    $mail->AltBody = strip_tags($content);
                                    //Karakter seti
                                    $mail->CharSet = 'UTF-8';

                                    // PNG dosyasını e-postaya ekleyin
                                    $mail->AddEmbeddedImage('static/png/activation.png', 'activation');

                                    $mail->send();
                                    echo alertdanger('Aktivasyon bağlantısı e-posta adresinize gönderildi.', "info", "Başarılı!");
                                } catch (Exception $e) {
                                    echo "E-posta gönderilemedi. Hata: {$mail->ErrorInfo}";
                                }
                                //**********EPOSTA GÖNDERME ALANI */

                                $db->commit();
                                header('Location: register-success.php');
                            } catch (PDOException $exh) {
                                if ($exh->errorInfo[1] == 1062) {
                                    $db->rollBack();
                                    echo alertdanger('Bu email adresi ile daha önce kayıt olunmuş.');
                                }
                            }
                        }
                    }
                    ?>

                    <form action="#" method="POST" autocomplete="off" novalidate="" class="w-100 mt-4 pt-2">
                        <input type="hidden" name="action" class="form-control" value="saveUser">
                        <div class="mb-4">
                            <input type="text" class="form-control" name="full_name" id="full_name" placeholder="Adınız Soyadınız" value="<?php echo $full_name ?? '' ?>">
                        </div>

                        <div class="mb-4">
                            <input type="email" name="email" id="email" class="form-control" value="<?php echo $email ?? ''; ?>" placeholder="Email">
                        </div>
                        <div class="mb-4">
                            <input type="text" class="form-control" name="company_name" id="company_name" placeholder="Firma adını giriniz!" value="<?php echo $company_name ?? '' ?>">
                        </div>

                        <div class="mb-4 generate-pass">
                            <div class="input-group field">
                                <input type="password" class="form-control password" id="password" name="password" placeholder="Şifre Giriniz" value="<?php echo $password ?? '' ?>">
                                <div class="input-group-text c-pointer gen-pass" data-bs-toggle="tooltip" title="Şifre Oluştur"><i class="feather-hash"></i></div>
                                <div class="input-group-text border-start bg-gray-2 c-pointer show-pass" data-bs-toggle="tooltip" title="Şifre Göster"><i></i></div>
                            </div>
                            <div class="progress-bar mt-2">
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <input type="password" class="form-control" name="password2" id="password2" placeholder="Tekrar Şifrenizi Giriniz." value="<?php echo $password2 ?? '' ?>">
                        </div>
                        <div class="mt-4">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input " name="terms_of_service" id="terms_of_service" <?php echo isset($_POST['terms_of_service']) ? 'checked' : ''; ?>>
                                <label class="custom-control-label c-pointer text-muted" for="terms_of_service"></label>
                                <a href="#" data-bs-toggle="modal" data-bs-target="#modal-scrollable" tabindex="-1" style="margin-left: 10px;"> Üyelik Sözleşmesi ve Kişisel Verilerin İşlenmesine İlişkin Aydınlatma ve Rıza Metni</a>'ni okudum ve kabul ediyorum.
                            </div>
                        </div>
                        <div class="g-recaptcha mb-4" data-sitekey="6LfHuWYqAAAAAMPWjmbVJVLDRi7_IAeY0of0REAk" data-callback="enableSubmitButton"></div>
                        <div class="mt-5">
                            <p class="text-muted text-center">Tüm alanlar doldurulduğunda aktif olur!</p>
                            <button type="submit" id="submitButton" class="btn btn-lg btn-primary w-100" disabled>Hesap Oluştur</button>
                        </div>
                    </form>
                    <div class="mt-3 text-muted text-center">
                        <p class="text-muted mb-0">Zaten hesabınız var mı? <a href="sign-in.php" class="fw-bold">Giriş Yap</a></p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Üyelik Sözleşmesi Modal -->
        <div class="modal modal-blur fade" id="modal-scrollable" tabindex="-1" aria-hidden="true" style="display: none;">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Üyelik sözleşmesi ve KVK'ya ilişkin aydınlatma metni</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h4 style="text-align: center;">ÜYELİK SÖZLEŞMESİ</h4>

                        <h4>1. Taraflar</h4>
                        <p>İşbu Sözleşme, www.yonapp.com.tr internet sitesinin faaliyetlerini yürüten [yonapp.com.tr]
                            (Bundan
                            böyle “yonapp” olarak anılacaktır) ve www.yonapp.com.tr internet sitesine üye olan internet
                            kullanıcısı ("Üye") arasında akdedilmiştir.</p>

                        <h4>2. Sözleşmenin Konusu</h4>
                        <p>İşbu Sözleşme’nin konusu, Üyenin www.yonapp.com.tr internet sitesinden faydalanma şartlarının
                            belirlenmesidir.</p>

                        <h4>3. Tarafların Hak ve Yükümlülükleri</h4>
                        <ol>
                            <li>Üyelik statüsünün kazanılması için, Üye olmak isteyen kullanıcının, web sitesinde bulunan
                                işbu Üyelik Sözleşmesi'ni onaylayarak, burada talep edilen bilgileri doğru ve güncel
                                bilgilerle doldurması gerekmektedir. Üye olmak isteyen kullanıcının 18 (on sekiz) yaşını
                                doldurmuş olması aranacaktır.</li>
                            <li>Üye, verdiği kişisel bilgilerin doğru olduğunu, yonapp’un bu bilgilerin gerçeğe aykırılığı
                                nedeniyle uğrayacağı zararları tazmin edeceğini beyan eder.</li>
                            <li>Üye, kendisine verilen şifreyi başka kişilerle paylaşmamayı taahhüt eder. Şifre
                                kullanımından kaynaklanan sorumluluk tamamen üyeye aittir.</li>
                            <li>Üye, siteyi yasal mevzuata uygun olarak kullanmayı ve başkalarını rahatsız edici
                                davranışlardan kaçınmayı kabul eder.</li>
                            <li>yonapp, üye verilerinin güvenliği için gerekli önlemleri alır, ancak üyenin bu verilerin
                                korunması konusunda da dikkatli olmasını bekler.</li>
                            <li>Üye, diğer kullanıcıların verilerine izinsiz ulaşmamayı ve bu verileri kullanmamayı kabul
                                eder.</li>
                            <li>Üyelik sözleşmesinin ihlali durumunda yonapp, üyenin üyeliğini iptal etme hakkına sahiptir.
                            </li>
                            <li>yonapp, her zaman tek taraflı olarak üyelikleri sonlandırma hakkını saklı tutar.</li>
                            <li>www.yonapp.com.tr internet sitesi yazılım ve tasarımı yonapp’a aittir. Bu içeriklerin
                                izinsiz kullanımı yasaktır.</li>
                            <li>Üye, web sitesi üzerinde herhangi bir otomatik program veya sistem kullanmamayı taahhüt
                                eder.</li>
                        </ol>

                        <h4>4. Sözleşmenin Feshi</h4>
                        <p>Üye, üyeliğini iptal edebilir. yonapp, üyenin sözleşme hükümlerini ihlal etmesi durumunda
                            üyeliği iptal edebilir. Üyelik iptal edildikten sonra, üyenin bilgileri 15 takvim günü
                            içerisinde silinecektir.</p>

                        <h4>5. İhtilafların Halli</h4>
                        <p>İhtilaf durumunda TC Mahkemeleri ve İcra Daireleri yetkilidir.</p>

                        <h4>6. Yürürlük</h4>
                        <p>Üyenin, üyelik kaydı yapması, sözleşme şartlarını kabul ettiği anlamına gelir. İşbu Sözleşme,
                            üyenin üye olması anında yürürlüğe girmiştir.</p>

                        <h4 style="text-align: center;">KİŞİSEL VERİLERİN İŞLENMESİNE İLİŞKİN AYDINLATMA VE RIZA METNİ</h4>

                        <h4>1. Aydınlatma Metninin Amacı ve yonapp’un Veri Sorumlusu Konumu:</h4>
                        <p>yonapp, kişisel verilerin korunmasına ilişkin yükümlülüklerini yerine getirmek amacıyla
                            aşağıdaki açıklamaları sunar. Bu metin, güncellemeler doğrultusunda değiştirilebilir.</p>

                        <h4>2. Kişisel Verilerin İşlenme Amacı:</h4>
                        <p>Kişisel verileriniz, aşağıdaki amaçlarla işlenmektedir:</p>
                        <ul>
                            <li>Kimlik bilgilerinizi teyit etmek,</li>
                            <li>İletişim bilgilerini kaydetmek,</li>
                            <li>Üyelerle iletişime geçmek ve gerekli bilgilendirmeleri yapmak,</li>
                            <li>Yasal yükümlülükleri yerine getirmek.</li>
                        </ul>

                        <h4>3. Kişisel Verilerin Toplanma Yöntemi:</h4>
                        <p>Kişisel verileriniz, web sitemiz üzerinden rızanız ile toplanmakta ve yukarıda belirtilen
                            amaçlarla işlenmektedir.</p>

                        <h4>4. Kişisel Veri Sahibi Olarak Haklarınız:</h4>
                        <p>KVKK’nın 11. maddesi uyarınca, kişisel veri sahipleri:</p>
                        <ul>
                            <li>Kişisel verilerin işlenip işlenmediğini öğrenme,</li>
                            <li>İşlenen veriler hakkında bilgi talep etme,</li>
                            <li>Yanlış veya eksik verilerin düzeltilmesini isteme,</li>
                            <li>Verilerin silinmesini isteme,</li>
                            <li>Yasal yollara başvurma hakkına sahiptir.</li>
                        </ul>

                        <p>Taleplerinizi <a href="mailto:info@yonapp.com.tr">info@yonapp.com.tr</a> adresine
                            iletebilirsiniz. yonapp, taleplerinizi 30 gün içinde değerlendirecektir.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn me-auto btn-danger" data-bs-dismiss="modal">Kapat</button>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Okudum</button>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const fullNameInput = document.getElementById('full_name');
            const emailInput = document.getElementById('email');
            const companyNameInput = document.getElementById('company_name');
            const passwordInput = document.getElementById('password');
            const password2Input = document.getElementById('password2');
            const termsCheckbox = document.getElementById('terms_of_service');
            const submitButton = document.getElementById('submitButton');
            let isRecaptchaValid = false; // reCAPTCHA durumunu tutar

            // reCAPTCHA callback fonksiyonu
            window.enableSubmitButton = function(response) {
                isRecaptchaValid = response !== ''; // reCAPTCHA onaylandıysa true yap
                checkForm(); // Formu tekrar kontrol et
            };

            // Formdaki tüm alanları kontrol eden fonksiyon
            function checkForm() {
                const isFormValid = fullNameInput.value.trim() !== '' &&
                    emailInput.value.trim() !== '' &&
                    companyNameInput.value.trim() !== '' &&
                    passwordInput.value.trim() !== '' &&
                    password2Input.value.trim() !== '' &&
                    termsCheckbox.checked &&
                    isRecaptchaValid;

                submitButton.disabled = !isFormValid; // Form geçerliyse butonu aktif yap
                if (isFormValid) {
                    submitButton.removeAttribute('title');

                } else {
                    submitButton.setAttribute('title', 'Tüm alanlar doldurulduğunda aktif olur.');

                }
            }

            // Input alanlarına event listener ekle
            fullNameInput.addEventListener('input', checkForm);
            emailInput.addEventListener('input', checkForm);
            companyNameInput.addEventListener('input', checkForm);
            passwordInput.addEventListener('input', checkForm);
            password2Input.addEventListener('input', checkForm);
            termsCheckbox.addEventListener('change', checkForm);

            // reCAPTCHA'nın sürekli kontrolü için ekstra bir kontrol
            setInterval(() => {
                const recaptchaResponse = document.querySelector('.g-recaptcha-response');
                if (recaptchaResponse && recaptchaResponse.value !== '') {
                    isRecaptchaValid = true;
                    checkForm();
                } else {
                    isRecaptchaValid = false;
                    checkForm();
                }
            }, 500); // Her 500ms'de bir reCAPTCHA'yı kontrol et
        });
    </script>
    <!--! ================================================================ !-->
    <!--! [End] Main Content !-->
    <!--! ================================================================ !-->
    <?php include './partials/theme-customizer.php' ?>
    <!--<< All JS Plugins >>-->
    <?php include './partials/script.php' ?>
</body>

</html>