let url = "/pages/kullanici-gruplari/api.php";


//Kullanıcı Grubu Ekle
$(document).on('click', '#saveRoleBtn', function () {
    const roleName = $('#role_name').val();
    const description = $('#description').val();


    var form = $('#roleForm');
    form.validate({
        rules: {
            role_name: {
                required: true
            }
        },
        messages: {
            role_name: {
                required: "Lütfen rol adını girin."
            }
        },
    });

    if (!form.valid()) {
        return;
    }


    var formData = new FormData(form[0]);
    formData.append('action', 'saveRole');

    for (const pair of formData.entries()) {
        console.log(`${pair[0]}: ${pair[1]}`);
    }

    fetch(url, {
        method: "POST",
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            Swal.fire(
                data.status === "success" ? "Başarılı!" : "Hata!",
                data.message,
                data.status
            );
        })
        .catch(error => {
            console.error("Hata:", error);
            Swal.fire("Hata!", "Bir hata oluştu.", "error");
        });
});

//Kullanıcı Grubu Sil
$(document).on('click', '.delete-role', function () {
    const roleId = $(this).data('id');
    const row = $(this).closest('tr');
    if (!roleId) {
        Swal.fire("Hata!", "Geçersiz rol ID'si.", "error");
        return;
    }

    let formData = new FormData();
    formData.append('id', roleId);
    formData.append('action', 'deleteRole');



    Swal.fire({
        title: "Emin misiniz?",
        text: "Bu işlem geri alınamaz!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Evet, sil!",
        cancelButtonText: "Hayır, iptal et"
    }).then((result) => {
        if (result.isConfirmed) {
            // Silme işlemini gerçekleştir
            fetch(url, {
                method: "POST",
                body: formData

            })
                .then(response => response.json())
                .then(data => {
                    Swal.fire(
                        data.status === "success" ? "Başarılı!" : "Hata!",
                        data.message,
                        data.status
                    );

                    if (data.status === "success") {
                        // Satırı tablodan kaldır
                        row.remove();
                    }

                })
                .catch(error => {
                    console.error("Hata:", error);
                    Swal.fire("Hata!", "Bir hata oluştu.", "error");
                });
        }
    });
});