$(document).ready(function () {
    $('#kisi_id').on('change', function () {
        var kisiId = $(this).val();

        if (kisiId) {
            $.ajax({
                url: '/api/kisiBilgileriCek.php',
                type: 'POST',
                data: {id: kisiId},
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        $('#tc').val(response.kimlik_no);
                    } else {
                        $('#tc').val('');
                        alert(response.message);
                    }
                },
                error: function () {
                    $('#tc').val('');
                    alert('Bir hata oluştu.');
                }
            });
        } else {
            $('#tc').val('');
        }
    });
});
