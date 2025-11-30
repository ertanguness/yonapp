let apiUrl = "/pages/personel/api/personInfoApi.php";
$(document).on('click', '#savePerson', function () {
    // Form verilerini al
    var form = $("#personelForm");
    var formData = new FormData(form[0]);
    formData.append('action', "savePerson");

    for (let pair of formData.entries()) {
        console.log(pair[0] + ', ' + pair[1]);
    }

    fetch(apiUrl, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            let title = data.status == 'success' ? 'Başarılı' : 'Hata';
            swal.fire({
                title: title,
                text: data.message,
                icon: data.status,
                confirmButtonText: 'Tamam'
            })
        })
        .catch(error => {
            swal.fire({
                title: 'Hata',
                text: error.message,
                icon: 'error',
                confirmButtonText: 'Tamam'
            })
        });
});