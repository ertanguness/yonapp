
    /** Gelir Ekle Modalini açar */
    $(document).on('click', '.gelir-ekle, .gider-ekle', function() {
        var islem_tipi = $(this).hasClass('gelir-ekle') ? 'gelir' : 'gider';
        $.get("pages/home/modal/gelir_gider_modal.php", {
            islem_tipi: islem_tipi,
            includeFile: ''
        }, function(data) {
            // Gelen yanıtı işleyin (örneğin, bir modal açarak)
            $('#gelirGiderModal .modal-content').html(data);
            $('#gelirGiderModal').modal('show');

            $(".select2").select2({
                dropdownParent: $('#gelirGiderModal')
            });
        });


    });
   
    /** Mail Gönder Modalini açar */
    $(document).on('click', '.mail-gonder', function() {
        var kisi_id = $(this).data('kisi-id');

        $.get("pages/home/modal/mail_gonder_modal.php", {
            kisi_id,
            includeFile: ''
        }, function(html) {
            $('#composeMail .modal-content').html(html);
            const $modal = $('#composeMail').modal('show');

            // // Modal gösterildikten sonra yükle/çalıştır
            //  $modal.on('shown.bs.modal', function() {
            //     // apps-email-init dosyasını sadece bir kez yükle
            //     if (!window.__appsEmailInitLoaded) {
            // assets\vendors\js\tagify-data.min.js
            $.getScript('/pages/home/js/tagify-data.js')
                .done(function() {
                    console.log('tagify-data.min.js yüklendi.');
                    window.quillMailEditor = new Quill("#mailEditorModal", {
                        placeholder: "Compose an epic...@mention, #tag",
                        theme: "snow"
                    });

                })
            $.getScript('/assets/js/apps-email-init.min.js')
                .done(function() {
                    console.log('apps-email-init.min.js yüklendi.');

                })

            document.querySelectorAll('#composeMail [data-bs-toggle="tooltip"]').forEach(function(el) {
                bootstrap.Tooltip.getOrCreateInstance(el);
            });
            document.querySelectorAll('#composeMail [data-bs-toggle="dropdown"]').forEach(function(el) {
                bootstrap.Dropdown.getOrCreateInstance(el, {
                    popperConfig: {
                        strategy: 'fixed',
                        modifiers: [{
                                name: 'preventOverflow',
                                options: {
                                    boundary: 'viewport',
                                    altAxis: true
                                }
                            },
                            {
                                name: 'offset',
                                options: {
                                    offset: [0, 6]
                                }
                            }
                        ]
                    }
                });
            });

            // });
            // Modal içindeki select2’ler
            $(".select2").select2({
                dropdownParent: $('#composeMail')
            });
        });
    });

    /**Mail Gönder butonuna basınca */
    $(document).on('click', '#SendMail', function() {
        const toList = window.pickEmails?.(window.mailTagify?.to) || [];
        const ccList = window.pickEmails?.(window.mailTagify?.cc) || [];
        const bccList = window.pickEmails?.(window.mailTagify?.bcc) || [];

        // Tekrarları temizle ve çakışmaları ayıkla
        const dedup = arr => [...new Set(arr.map(e => e.toLowerCase()))];
        const to = dedup(toList);
        const cc = dedup(ccList).filter(e => !to.includes(e));
        const bcc = dedup(bccList).filter(e => !to.includes(e) && !cc.includes(e));

        // En az bir alıcı kontrolü
        if (!to.length && !cc.length && !bcc.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Alıcı yok',
                text: 'En az bir alıcı ekleyin.'
            });
            return;
        }

        const formData = new FormData();
        formData.append('action', 'email_gonder');
        formData.append('to', JSON.stringify(to));
        formData.append('cc', JSON.stringify(cc));
        formData.append('bcc', JSON.stringify(bcc));
        formData.append('subject', $('#composeMail input[placeholder="Subject"]').val());
        formData.append('message', window.quillMailEditor ? window.quillMailEditor.root.innerHTML : '');

        // //formdata ieriğini console'a yazdır
        // for(let pair of formData.entries()){
        //     console.log(`${pair[0]}: ${pair[1]}`);
        // }
        // return;



        let UrlEmail = "/pages/email-sms/api/APIEmail.php";
        fetch(UrlEmail, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log(data);
                let title = data.status === 'success' ? 'Başarılı!' : 'Hata!';
                Swal.fire({
                    title: title,
                    text: data.message,
                    icon: data.status === 'success' ? 'success' : 'error',
                    confirmButtonText: 'Tamam'
                });

            })
            .catch(error => {
                console.log(error);

                console.error('Error:', error);
                Swal.fire({
                    title: 'Hata!',
                    text: 'E-posta gönderilirken bir hata oluştu.',
                    icon: 'error',
                    confirmButtonText: 'Tamam'
                });
            });
        //console.log('E-postalar:', emailList);
        // ... gönderme işlemi


    });

    /**Sms Gönderme Modalini açar */
    $(document).on('click', '.sms-gonder', function(e) {
        e.preventDefault();
        var kisi_id = $(this).data('kisi-id') || '';
        const url = '/pages/email-sms/sms_gonder_modal.php';

        // Güvenlik: Modal DOM'da var mı?
        if (!document.getElementById('SendMessage')) {
            console.error('[SMS] #SendMessage modal DOM içinde bulunamadı');
            return;
        }

        $.ajax({
            url: url,
            method: 'GET',
            data: { kisi_id: kisi_id, includeFile: '' },
            dataType: 'html'
        }).done(function(data){
            $('#SendMessage .modal-content').html(data);

            if (typeof window.registerNewModal === 'function') {
                window.registerNewModal('SendMessage');
            }

            $('#SendMessage').modal('show');

            // Bootstrap show animasyonu bitmesine yakın init
            setTimeout(function() {
                if (typeof window.initSmsModal === 'function') {
                    window.initSmsModal();
                    $(".select2").select2({ dropdownParent: $('#SendMessage') });
                }
            }, 150);
        }).fail(function(xhr, status, err){
            console.error('[SMS] Modal içeriği alınırken hata', status, err, xhr.status, xhr.responseText);
            Swal.fire({
                icon: 'error',
                title: 'SMS Modal Hatası',
                text: 'İçerik yüklenemedi. Lütfen tekrar deneyin.'
            });
        });
    });