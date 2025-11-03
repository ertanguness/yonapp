
<?php
require_once __DIR__ . '/configs/bootstrap.php';

?>

<script src="https://www.google.com/recaptcha/api.js?hl=tr" async defer></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<style>
    .auth-hero-side {
        position: relative;
        /* ... */
    }

    .auth-cover-wrapper .auth-cover-content-inner .auth-cover-content-wrapper .auth-img {
        width: 500px;
        margin: 40px auto !important;
    }

    /* Swiper Konteyneri */
    .testimonial-swiper {
        position: absolute;
        bottom: 8%;
        left: 10%;
        right: 10%;
        z-index: 10;
        text-align: center;
        color: #334155;
        padding-bottom: 30px;
    }

    .auth-hero-side {
        margin-top: 100px !important;

    }

    .testimonial-swiper .quote-icon {
        font-size: 4rem;
        font-family: 'Source Sans Pro', serif;
        color: #4f46e5;
        line-height: 1;
        opacity: 0.3;
    }

    .testimonial-swiper h2 {
        font-size: 1.5rem;
        font-weight: 600;
        margin-top: -1rem;
        margin-bottom: 0.75rem;
    }

    .testimonial-swiper p {
        font-size: 1rem;
        line-height: 1.6;
        max-width: 500px;
        margin: 0 auto;
    }

    /* Swiper pagination noktalarını özelleştirme */
    .swiper-pagination-bullet {
        background-color: rgba(79, 70, 229, 0.5);
        opacity: 1;
    }

    .swiper-pagination-bullet-active {
        background-color: #4f46e5;
    }
</style>

<!DOCTYPE html>
<html lang="zxx">

<?php
// HTML'i başlatalım
$page = 'kayit-ol';
include './partials/head.php';
?>

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
                <div class="auth-hero-side">

                    <!-- YENİ SWIPER SLIDER ALANI -->
                    <div class="swiper testimonial-swiper">
                        <div class="swiper-wrapper">
                            <!-- Slide 1 -->
                            <div class="swiper-slide">
                                <div class="quote-icon">“</div>
                                <h2>Topluluğunuzu akıllıca yönetin</h2>
                                <p>Tüm işlemlerinizi kolayca takip edin. YonApp ile kontrol artık parmaklarınızın ucunda!</p>
                            </div>
                            <!-- Slide 2 -->
                            <div class="swiper-slide">
                                <div class="quote-icon">“</div>
                                <h2>Finansal şeffaflık sağlayın</h2>
                                <p>Aidat ve gider takibini kolaylaştırın, tüm sakinlerinizle anında paylaşın.</p>
                            </div>
                            <!-- Slide 3 -->
                            <div class="swiper-slide">
                                <div class="quote-icon">“</div>
                                <h2>İletişimde kalın, güçlü kalın</h2>
                                <p>Duyuru ve anketlerle topluluğunuzla her an bağlantıda olun.</p>
                            </div>
                        </div>
                        <!-- Navigasyon Noktaları -->
                        <div class="swiper-pagination"></div>
                    </div>

                </div>
            </div>

        </div>
        <div class="auth-cover-sidebar-inner">
            <div class="auth-cover-card-wrapper">
                <div class="auth-cover-card p-sm-5 ">
                    <div class="text-center mb-5">
                        <img src="assets/images/logo/logo.svg" style="max-width: 50%; height: auto;">
                    </div>
                    <h2 class="fs-20 fw-bolder mb-4">Hesap Oluştur </h2>

                    <?php

                    // --- TEK SATIRDA FLASH MESAJLARI GÖSTERME ---
                    include_once  'partials/_flash_messages.php';
                   
                    ?>

                    <form action="public/register-handler.php" method="POST" autocomplete="off" novalidate class="w-100 mt-4 pt-2">
                        <input type="hidden" name="action" class="form-control" value="saveUser">
                        <div class="mb-4">
                            <input type="text" class="form-control" name="full_name" id="full_name" placeholder="Adınız Soyadınız" value="<?php echo $full_name ?? '' ?>">
                        </div>

                        <div class="mb-4">
                            <input type="email" name="email" id="email" class="form-control" value="<?php echo $_GET['email'] ?? ''; ?>" placeholder="Email">
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
                        <div class="g-recaptcha mb-4" data-sitekey="6LdplvwrAAAAAMaDr597pZXA5sWRhQJXT4Y9vtTH" data-callback="enableSubmitButton"></div>
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

	<script src="assets/vendors/js/lslstrength.min.js"></script>
	
	<script src="assets/vendors/js/cleave.min.js"></script>
	
	    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const fields = ['full_name', 'email', 'company_name', 'password', 'password2'];
        const submitButton = document.getElementById('submitButton');
        const termsCheckbox = document.getElementById('terms_of_service');
        let isRecaptchaValid = false;

        window.enableSubmitButton = function(response) {
            isRecaptchaValid = !!response;
            checkForm();
        };

        function checkForm() {
            const allFilled = fields.every(id => document.getElementById(id).value.trim() !== '');
            const valid = allFilled && termsCheckbox.checked && isRecaptchaValid;
            submitButton.disabled = !valid;
            submitButton.title = valid ? '' : 'Tüm alanlar doldurulduğunda aktif olur.';
        }

        fields.forEach(id => document.getElementById(id).addEventListener('input', checkForm));
        termsCheckbox.addEventListener('change', checkForm);

        setInterval(() => {
            const recaptchaResponse = document.querySelector('.g-recaptcha-response');
            isRecaptchaValid = recaptchaResponse && recaptchaResponse.value !== '';
            checkForm();
        }, 500);
    });
    </script>
    <!--! ================================================================ !-->



    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const swiper = new Swiper('.testimonial-swiper', {
                // Döngüsel olmasını sağlar
                loop: true,

                // Otomatik oynatma
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false, // Kullanıcı etkileşiminden sonra durmasın
                },

                // Geçiş efekti (fade daha şık durur)
                effect: 'fade',
                fadeEffect: {
                    crossFade: true
                },

                // Navigasyon noktaları
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
            });
        });
    </script>


</body>

</html>