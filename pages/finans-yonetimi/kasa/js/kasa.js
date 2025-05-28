$(document).on('click', '#kasa_kaydet', function() {
    var form = $('#kasaForm');

    form.validate({
        rules: {
            'kasa_adi': {
                required: true,
                maxlength: 100
            },
        },
        messages: {
            'kasa_adi': {
                required: 'Kasa adı boş bırakılamaz.',
                maxlength: 'Kasa adı en fazla 100 karakter olabilir.'
            },
        },
    });

    if(!form.valid()) {
        return false;
    }


});