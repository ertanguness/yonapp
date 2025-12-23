

(function () {


        $(document).on('click', '.toggle-switch-row', function (e) {
            if (!$(e.target).is('input') && !$(e.target).is('label')) {
                let checkbox = $(this).find('input[type="checkbox"]');
                checkbox.prop('checked', !checkbox.prop('checked'));
            }
        });


        /**Genel Ayarlar Tab'ı active mi kontrol ediliyor */
        var tabGeneral = $('#generalSettingsTab');

        /**İletişim Ayarları Tab'ı active mi kontrol ediliyor */
        var tabContact = $('#contactSettingsTab');

        /**Bildirim Ayarları Tab'ı active mi kontrol ediliyor */
        var tabNotification = $('#notificationSettingsTab');
        let url = "/pages/ayarlar/api.php"

        $(document).on('click', '#ayarlar_kaydet', function () {
            if (tabNotification.hasClass('active')) {
                var form = $('#notificationSettingsForm');
                let formData = new FormData(form[0]);
                formData.append('action', 'bildirim_ayarlari_kaydet');

                fetch(url, {
                    method: 'POST',
                    body: formData
                }).then(function (r) {
                    return r.json();
                }).then(function (d) {
                    let title = d.status == 'success' ? 'Başarılı' : 'Hata';
                    Swal.fire({
                        icon: d.status,
                        title: title,
                        text: d.message,
                    });
                }).catch(function (e) {
                    console.log(e);
                });
            } else if (tabGeneral.hasClass('active')) {
                var form = $("#generalSettingsForm");
                var formData = new FormData(form[0]);

                formData.append("action", "ayarlar_kaydet");
                formData.append("id", $("#ayarlar_id").val());

                var validator = form.validate({
                    rules: {
                        smtpServer: { required: true },
                        smtpPort: { required: true, number: true },
                        smtpUser: { required: true, email: true },
                        smtpPassword: { required: true },
                        smsProvider: { required: true },
                        smsUsername: { required: true },
                        smsPassword: { required: true },
                        whatsappApiUrl: { required: true, url: true },
                        whatsappToken: { required: true },
                        whatsappSender: { required: true },

                        eposta: { required: true, email: true },
                        telefon: { required: true },
                        acilIletisim: { required: true }
                    },
                    messages: {
                        smtpPassword: { required: "E-posta şifresini giriniz" },
                        smsProvider: { required: "SMS servis sağlayıcısını giriniz" },
                        smsUsername: { required: "SMS kullanıcı adını giriniz" },
                        smsPassword: { required: "SMS şifresini giriniz" },
                        whatsappApiUrl: {
                            required: "WhatsApp API URL'sini giriniz",
                            url: "Geçerli bir URL giriniz",
                        },
                        whatsappToken: { required: "WhatsApp API tokenını giriniz" },
                        whatsappSender: { required: "Gönderen numarasını giriniz" },

                        // İletişim Bilgileri
                        eposta: {
                            required: "E-posta adresi yazınız",
                            email: "Geçerli bir e-posta adresi giriniz"
                        },
                        telefon: { required: "Telefon numarası yazınız" },
                        acilIletisim: { required: "Acil iletişim numarası yazınız" }
                    },
                });

                formData.append("action", "genel_ayarlar_kaydet");


                fetch(url, {
                    method: "POST",
                    body: formData,
                })
                    .then((response) => {
                        return response.json();
                    })
                    .then((data) => {
                        var title = data.status == "success" ? "Başarılı" : "Hata";
                        swal.fire({
                            title: title,
                            text: data.message,
                            icon: data.status,
                            confirmButtonText: "Tamam",
                        });
                    });
            } else if (tabContact.hasClass('active')) {
                var form = $("#contactSettingsForm");
                var formData = new FormData(form[0]);

                var validator = form.validate({
                    rules: {
                        smtpServer: { required: true },
                        smtpPort: { required: true, number: true },
                        smtpUser: { required: true, email: true },
                        smtpPassword: { required: true },
                        smsProvider: { required: true },
                        smsUsername: { required: true },
                        smsPassword: { required: true },
                        whatsappApiUrl: { required: true, url: true },
                        whatsappToken: { required: true },
                        whatsappSender: { required: true },

                    },
                    messages: {
                        smtpPassword: { required: "E-posta şifresini giriniz" },
                        smsProvider: { required: "SMS servis sağlayıcısını giriniz" },
                        smsUsername: { required: "SMS kullanıcı adını giriniz" },
                        smsPassword: { required: "SMS şifresini giriniz" },
                        whatsappApiUrl: {
                            required: "WhatsApp API URL'sini giriniz",
                            url: "Geçerli bir URL giriniz",
                        },
                        whatsappToken: { required: "WhatsApp API tokenını giriniz" },
                        whatsappSender: { required: "Gönderen numarasını giriniz" },
                    },
                });

                /**gizli tab'ları ignore eder */
                validator.settings.ignore = ":hidden";

                if(!validator.form()){
                    return;
                }

                formData.append("action", "iletisim_ayarlari_kaydet");

                fetch(url, {
                    method: "POST",
                    body: formData,
                })
                    .then((response) => {
                        return response.json();
                    })
                    .then((data) => {
                        var title = data.status == "success" ? "Başarılı" : "Hata";
                        swal.fire({
                            title: title,
                            text: data.message,
                            icon: data.status,
                            confirmButtonText: "Tamam",
                        });
                    });
            }
        });

    })();





$(document).on("change", ".select2", function () {
  $(this).valid(); // Trigger validation for the changed select2 element
});



$(document).ready(function(){
  var params = new URLSearchParams(window.location.search);
  var tab = (params.get('tab')||'').toLowerCase();
  var sub = (params.get('sub')||'').toLowerCase();
  if(tab === 'notifications'){
    var $outerLink = $('a[data-bs-target="#notificationSettingsTab"]');
    if($outerLink.length){
      try{ bootstrap.Tab.getOrCreateInstance($outerLink[0]).show(); }catch(e){}
      $('#settingsTab .nav-link').removeClass('active');
      $outerLink.addClass('active');
      $('#generalSettingsTab').removeClass('show active');
      $('#notificationSettingsTab').addClass('show active');
    }

    var showInner = function(){
      if(sub === 'email'){
        var $btn = $('#v-pills-email-tab');
        if($btn.length){ try{ bootstrap.Tab.getOrCreateInstance($btn[0]).show(); }catch(e){} }
        $('#v-pills-sms').removeClass('show active');
        $('#v-pills-email').addClass('show active');
        $('#v-pills-sms-tab').removeClass('active');
        $('#v-pills-email-tab').addClass('active');
      } else if(sub === 'sms'){
        var $btn2 = $('#v-pills-sms-tab');
        if($btn2.length){ try{ bootstrap.Tab.getOrCreateInstance($btn2[0]).show(); }catch(e){} }
        $('#v-pills-email').removeClass('show active');
        $('#v-pills-sms').addClass('show active');
        $('#v-pills-email-tab').removeClass('active');
        $('#v-pills-sms-tab').addClass('active');
      }
    };
    setTimeout(showInner, 150);
  } else if(tab === 'general'){
    var gen = document.querySelector('a[data-bs-target="#generalSettingsTab"]');
    if(gen){ bootstrap.Tab.getOrCreateInstance(gen).show(); }
  } else if(tab === 'communications'){
    var comm = document.querySelector('a[data-bs-target="#communicationsSettingsTab"]');
    if(comm){ bootstrap.Tab.getOrCreateInstance(comm).show(); }
  }
});
