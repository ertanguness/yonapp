$(document).on('click', '#save_dues', function() {
    var form = $('#duesForm');
    var formData = new FormData(form[0]);

    //console.log("validate:" + typeof $.fn.validate);
    // formData.append('action', 'save_dues');
    // formData.append('dues_id', $('#dues_id').val());

    // for (var pair of formData.entries()) {
    //     console.log(pair[0] + ', ' + pair[1]);
    // }
    $("#duesForm").validate({
        submitHandler: function(form) {
          // do other things for a valid form
          form.submit();
        }
      });

    // form.validate({
    //     rules: {
    //         due_days: {
    //             required: true
    //         },
    //         amount: {
    //             required: true,
    //             number: true
    //         }
    //     },
    //     messages: {
    //         due_days: {
    //             required: "Please enter the dues name"
    //         },
    //         amount: {
    //             required: "Please enter the dues amount",
    //             number: "Please enter a valid number"
    //         }
    //     },
    //     submitHandler: function() {
    //         $.ajax({
    //             url: form.attr('action'),
    //             type: form.attr('method'),
    //             data: formData,
    //             processData: false,
    //             contentType: false,
    //             success: function(response) {
    //                 alert('Dues saved successfully!');
    //             },
    //             error: function() {
    //                 alert('An error occurred while saving dues.');
    //             }
    //         });
    //     }
    // });

    
});