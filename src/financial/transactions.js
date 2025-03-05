var hasProcess = false;

//Genel Gelir-Gider Ekle
$(document).ready(function () {
  addCustomValidationMethods();
  addCustomValidationValidValue();

  //genel modal form kontrolleri
  $("#transactionModalForm").validate({
    rules: {
      amount: {
        required: true,
        validNumber: true
      },
      gm_case_id: {
        required: true,
        validValue: true
      },
      gm_incexp_type: {
        required: true,
        validValue: true
      }
    },
    messages: {
      amount: {
        required: "Lütfen tutar giriniz",
        validNumber: "Lütfen geçerli bir tutar giriniz!"
      },
      gm_case_id: {
        required: "Lütfen bir kasa seçiniz!",
        validValue: "Lütfen bir kasa seçiniz!"
      },
      gm_incexp_type: {
        required: "İşlem Türünü seçiniz!",
        validValue: "İşlem Türünü seçiniz!"
      }
    },
    errorPlacement: function (error, element) {
      customErrorPlacement(error, element);
    }
  });
});

//Genel modal kaydet butonuna basınca
$(document).on("click", "#saveTransaction", function () {
  var form = $("#transactionModalForm");
  //Eğer tüm kontroller doğru ise
  if (form.valid()) {
    let formData = new FormData(form[0]);

    formData.append("action", "saveTransaction");
    // for (var pair of formData.entries()) {
    //   console.log(pair[0] + ", " + pair[1]);
    // }

    fetch("/api/financial/transaction.php", {
      method: "POST",
      body: formData
    })
      .then((response) => response.json())
      .then((data) => {
        console.log(data);

        if (data.status == "success") {
          title = "Başarılı!";
        } else {
          title = "Hata!";
        }
        Swal.fire({
          title: title,
          text: data.message,
          icon: data.status,
          confirmButtonText: "Tamam"
        }).then((result) => {
          if (result.isConfirmed) {
            //$("#amount").val("");
            hasProcess = true;
          }
        });
      })
      .catch((error) => {
        console.error("Error:", error);
      });
  }
});

//general-modal kaptıldığında sayfayı yenile
$("#general-modal").on("hidden.bs.modal", function () {
  //console.log(hasProcess);

  if (hasProcess === true) {
    window.location.reload();
  }
});

$(document).on("click", ".delete-transaction", function () {
  //Tablo adı butonun içinde bulunduğu tablo
  let action = "deleteTransaction";
  let confirmMessage = "Kasa hareketi silinecektir!";
  let type = $(this).data("type");
  let url = "/api/financial/transaction.php?type=" + type;

  deleteRecord(this, action, confirmMessage, url);
});

$('input[name="amount"]').keypress(function (e) {
  if ((e.which < 48 || e.which > 57) && e.which != 46) {
    return false;
  }
});

$(document).on("click", ".transaction_type", function () {
  var type = $(this).val();
  var formData = new FormData();
  formData.append("action", "getSubTypes");
  formData.append("type", type);

  fetch("api/financial/transaction.php", {
    method: "POST",
    body: formData
  })
    .then((response) => response.json())
    .then((data) => {
      //Elementin içini boşalt
      $("#gm_incexp_type").html("");
      var options = "<option value=''>Tür Seçiniz</option>";
      data = data.subTypes;
      console.log(data);

      data.forEach((element) => {
        options += `<option value="${element.id}">${element.name}</option>`;
      });
      $("#gm_incexp_type").html(options);
    })
    .catch((error) => {
      console.error("Error:", error);
    });
});

$(document).on("change", "#firm_cases", function () {
  //case_id'yi al sayfayı post ile yenile
  var case_id = $(this).val();
  var form = $("#caseForm");
  //case_id'yi form'a ekle
  form.append(`<input type="hidden" name="case_id" value="${case_id}">`);
  form.submit();
});
let isTriggeringChange = false;

function clearAndTrigger(selectors) {
  if (!isTriggeringChange) {
    isTriggeringChange = true;
    $(selectors).val(0).trigger("change");
    isTriggeringChange = false;
  }
}

$(document).on("change", "#gm_project_id", function () {
  clearAndTrigger("#gm_person_name, #gm_company");
});

$(document).on("change", "#gm_person_name", function () {
  clearAndTrigger("#gm_company, #gm_project_id");
});

$(document).on("change", "#gm_company", function () {
  clearAndTrigger("#gm_project_id, #gm_person_name");
});

// select2 elemanlarında seçim yapıldığında validator'ı tekrar çalıştır
$(".select2").on("change", function () {
  $(this).valid();
});
//projeden ödeme al
$(document).on("click", "#savePaymentFromProject", function () {
  var id = $("#transaction_id").val();

  addCustomValidationMethods(); //app.js içerisinde tanımlı(validNumber metodu)
  addCustomValidationValidValue(); //app.js içerisinde tanımlı(validValue metodu)
  var form = $("#paymentFromProjectForm");
  form.validate({
    rules: {
      fp_project_name: {
        required: true,
        validValue: true
      },
      fp_amount: {
        required: true,
        validNumber: true
      },
      fp_cases: {
        required: true,
        validValue: true
      }
    },
    messages: {
      fp_project_name: {
        required: "Lütfen proje seçin",
        validValue: "Lütfen proje seçin"
      },
      fp_amount: {
        required: "Lütfen miktarı girin",
        validNumber: "Geçerli bir miktar girin"
      },
      fp_cases: {
        required: "Lütfen kasa seçin",
        validValue: "Lütfen kasa seçin"
      }
    },
    errorPlacement: function (error, element) {
      customErrorPlacement(error, element);
    }
  });
  if (!form.valid()) {
    return;
  }
  let formData = new FormData(form[0]);
  formData.append("action", "getPaymentFromProject");
  formData.append("id", id);

  fetch("api/financial/transaction.php", {
    method: "POST",
    body: formData
  })
    .then((response) => response.json())
    .then((data) => {
      // console.log(data);

      if (data.status == "success") {
        Swal.fire({
          title: "Başarılı!",
          text: data.message,
          icon: data.status,
          confirmButtonText: "Tamam"
        }).then((result) => {
          if (result.isConfirmed) {
            location.reload();
          }
        });
      } else {
        Swal.fire({
          title: "Hata!",
          text: data.message,
          icon: data.status,
          confirmButtonText: "Tamam"
        });
      }
    });
});

//Personellere ödeme yap
$(document).ready(function () {
  addCustomValidationMethods();
  addCustomValidationValidValue();

  $("#payToPersonsForm").validate({
    rules: {
      tps_action_date: {
        required: true
      },
      tps_cases: {
        required: true
      }
    },
    messages: {
      tps_action_date: {
        required: "Lütfen ödeme tarihini girin"
      },
      tps_cases: {
        required: "Lütfen ödeme yapılacak kasayı seçin"
      }
    },
    errorPlacement: function (error, element) {
      if (element.hasClass("select2")) {
        error.insertAfter(element.next("span"));
      } else {
        error.insertAfter(element);
      }
    }
  });

  $("#savePayToPersons").on("click", function () {
    if ($("#payToPersonsForm").valid()) {
      //tablodaki satırlardaki değerleri al
      var person_ids = [];
      var amounts = [];
      var person_id = "";
      var amount = "";

      var form = $("#payToPersonsForm");
      var formData = new FormData(form[0]);
      //preloader göster
      $(".preloader").fadeIn();
      //tablodaki satırlardaki değerleri al
      $("#payToPersons tbody tr").each(function () {
        //ilk td elemanının data-id attribute'undaki değeri al
        person_id = $(this).find("td:eq(0)").data("id");
        amount = $(this).find("td:eq(1) input").val();
        //eğer amount 0'dan büyükse veya boş değilse veya numeric ise işlem yap
        if (amount > 0 && amount != "" && $.isNumeric(amount)) {
          person_ids.push(person_id);
          amounts.push(amount);
        }
      });

      formData.append("person_ids", person_ids);
      formData.append("amounts", amounts);

      for (var pair of formData.entries()) {
        console.log(pair[0] + ", " + pair[1]);
      }

      formData.append("action", "payToPersons");
      fetch("api/financial/transaction.php", {
        method: "POST",
        body: formData
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.status == "success") {
            title = "Başarılı!";
          } else {
            title = "Hata";
          }
          swal
            .fire({
              title: title,
              text: data.message,
              icon: data.status
            })
            .then((result) => {
              if (result.isConfirmed) {
                location.reload();
              }
            });
        });
      //preloader gizle
      $(".preloader").fadeOut();
    }
  });
});

///// GENEL MODALDA BİŞRLEŞTİRİLDİ//////////////////////

//Personele ödeme yap
$(document).ready(function () {
  addCustomValidationMethods();
  addCustomValidationValidValue();

  $("#payToPersonForm").validate({
    rules: {
      tp_person_name: {
        required: true
      },
      tp_amount: {
        required: true,
        validNumber: true,
        validValue: true
      },
      tp_action_date: {
        required: true
      },
      tp_cases: {
        required: true
      }
    },
    messages: {
      tp_person_name: {
        required: "Lütfen personel seçin"
      },
      tp_amount: {
        required: "Lütfen ödeme tutarını girin",
        validNumber: "Lütfen geçerli bir sayı girin",
        validValue: "Lütfen geçerli bir değer girin"
      },
      tp_action_date: {
        required: "Lütfen ödeme tarihini girin"
      },
      tp_cases: {
        required: "Lütfen ödeme yapılacak kasayı seçin"
      }
    },
    errorPlacement: function (error, element) {
      if (element.hasClass("select2")) {
        error.insertAfter(element.next("span"));
      } else {
        error.insertAfter(element);
      }
    }
  });

  $("#savePayToPerson").on("click", function () {
    if ($("#payToPersonForm").valid()) {
      // Form geçerliyse işlemleri yap
      // Örneğin formu submit edebilirsiniz
      var form = $("#payToPersonForm");
      let formData = new FormData(form[0]);
      formData.append("action", "payToPerson");

      fetch("api/financial/transaction.php", {
        method: "POST",
        body: formData
      })
        .then((response) => response.json())
        .then((data) => {
          console.log(data);

          if (data.status == "success") {
            Swal.fire({
              title: "Başarılı!",
              text: data.message,
              icon: data.status,
              confirmButtonText: "Tamam"
            }).then((result) => {
              if (result.isConfirmed) {
                location.reload();
              }
            });
          } else {
            Swal.fire({
              title: "Hata!",
              text: data.message,
              icon: data.status,
              confirmButtonText: "Tamam"
            });
          }
        });
    }
  });
});

//Firma Ödemesi yap
$(document).on("click", "#savePayToCompany", function () {
  let id = $("#transaction_id").val();
  var form = $("#payToCompanyForm");

  addCustomValidationMethods(); //app.js içerisinde tanımlı(validNumber metodu)
  addCustomValidationValidValue(); //app.js içerisinde tanımlı(validValue metodu)
  form.validate({
    rules: {
      tc_company_name: {
        required: true,
        validValue: true
      },
      tc_amount: {
        required: true,
        validNumber: true
      },
      tc_cases: {
        required: true,
        validValue: true
      }
    },
    messages: {
      tc_company_name: {
        required: "Lütfen bir firma seçin",
        validValue: "Lütfen bir firma seçin"
      },
      tc_amount: {
        required: "Lütfen miktarı girin",
        validNumber: "Geçerli bir miktar girin"
      },
      tc_cases: {
        required: "Lütfen bir kasa seçin",
        validValue: "Lütfen bir kasa seçin"
      }
    },
    errorPlacement: function (error, element) {
      customErrorPlacement(error, element);
    }
  });
  if (!form.valid()) {
    return;
  }

  let formData = new FormData(form[0]);

  formData.append("action", "payToCompany");
  formData.append("id", id);

  // for (var pair of formData.entries()) {
  //   console.log(pair[0] + ", " + pair[1]);
  // }

  fetch("api/financial/transaction.php", {
    method: "POST",
    body: formData
  })
    .then((response) => response.json())
    .then((data) => {
      // console.log(data);

      if (data.status == "success") {
        Swal.fire({
          title: "Başarılı!",
          text: data.message,
          icon: data.status,
          confirmButtonText: "Tamam"
        }).then((result) => {
          if (result.isConfirmed) {
            location.reload();
          }
        });
      } else {
        Swal.fire({
          title: "Hata!",
          text: data.message,
          icon: data.status,
          confirmButtonText: "Tamam"
        });
      }
    });
});

//Alınan Proje Masraf Ekle
$(document).on("click", "#saveAddExpenseReceivedProject", function () {
  let id = $("#transaction_id").val();
  var form = $("#addExpenseReceivedProjectForm");

  addCustomValidationMethods(); //app.js içerisinde tanımlı(validNumber metodu)
  addCustomValidationValidValue(); //app.js içerisinde tanımlı(validValue metodu)
  form.validate({
    rules: {
      rp_project_name: {
        required: true,
        validValue: true
      },
      rp_amount: {
        required: true,
        validNumber: true
      },
      rp_cases: {
        required: true,
        validValue: true
      }
    },
    messages: {
      rp_project_name: {
        required: "Lütfen bir proje seçin",
        validValue: "Lütfen bir proje seçin"
      },
      rp_amount: {
        required: "Lütfen miktarı girin",
        validNumber: "Geçerli bir miktar girin"
      },
      rp_cases: {
        required: "Lütfen bir kasa seçin",
        validValue: "Lütfen bir kasa seçin"
      }
    },
    errorPlacement: function (error, element) {
      customErrorPlacement(error, element);
    }
  });
  if (!form.valid()) {
    return;
  }

  let formData = new FormData(form[0]);

  formData.append("action", "addExpenseReceivedProject");
  formData.append("id", id);

  // for (var pair of formData.entries()) {
  //   console.log(pair[0] + ", " + pair[1]);
  // }

  fetch("api/financial/transaction.php", {
    method: "POST",
    body: formData
  })
    .then((response) => response.json())
    .then((data) => {
      // console.log(data);

      if (data.status == "success") {
        Swal.fire({
          title: "Başarılı!",
          text: data.message,
          icon: data.status,
          confirmButtonText: "Tamam"
        }).then((result) => {
          if (result.isConfirmed) {
            location.reload();
          }
        });
      } else {
        Swal.fire({
          title: "Hata!",
          text: data.message,
          icon: data.status,
          confirmButtonText: "Tamam"
        });
      }
    });
});

///// GENEL MODALDA BİŞRLEŞTİRİLDİ//////////////////////

//Güncelleme işlemi
$(document).on("click", ".edit-transactions", function () {
  let id = $(this).data("id");
  $("#transaction_id").val(id);

  var projects = "";
  var cases = "";
  var persons = "";
  var companies = "";

  //preloader göster
  $(".preloader").show();

  //tablonun 2. satırındaki veriyi al
  let type = $(this).closest("tr").find("td:eq(3)").text().trim();

  switch (type) {
    case "Proje(Alınan Ödeme)":
      var modal = $("#get_payment_from_project-modal");
      var case_select = $("#fp_cases");
      var project_select = $("#fp_project_name");
      var amount_input = "fp_amount";
      var date_input = "fp_action_date";
      var description_input = "fp_description";

      break;

    case "Personel Ödemesi":
      var modal = $("#pay_to_person-modal");
      var case_select = $("#tp_cases");
      var person_select = $("#tp_person_name");
      var amount_input = "tp_amount";
      var date_input = "tp_action_date";
      var description_input = "tp_description";
      break;

    case "Firma Ödemesi":
      var modal = $("#pay_to_company-modal");
      var case_select = $("#tc_cases");
      var companies_select = $("#tc_company_name");
      var amount_input = "tc_amount";
      var date_input = "tc_action_date";
      var description_input = "tc_description";

      break;
    case "Alınan Proje Masrafı":
      var modal = $("#add_expense_received_project-modal");
      var case_select = $("#rp_cases");
      var project_select = $("#rp_project_name");
      var amount_input = "rp_amount";
      var date_input = "rp_action_date";
      var description_input = "rp_description";
      break;

    case "Virman":
      swal.fire({
        title: "Uyarı!",
        text: "Virman işlemi buradan güncellenemez!",
        icon: "error",
        confirmButtonText: "Tamam"
      });
      //preload gizle
      $(".preloader").hide();
      return;
    default:
      var modal = $("#general-modal");
      break;
  }

  //kasanın tüm değerleri bir değişkene atanır,
  case_select.find("option").each(function () {
    if ($(this).val() != 0) {
      cases += $(this).val() + ",";
    }
  });

  //Projenin tüm değerleri bir değişkene atanır,
  if (project_select) {
    project_select.find("option").each(function () {
      if ($(this).val() != 0) {
        projects += $(this).val() + ",";
      }
    });
  }
  //Personel selectin tüm değerleri bir değişkene atanır,
  if (person_select) {
    person_select.find("option").each(function () {
      if ($(this).val() != 0) {
        persons += $(this).val() + ",";
      }
    });
  }

  //Firma selectin tüm değerleri bir değişkene atanır,
  if (companies_select) {
    companies_select.find("option").each(function () {
      if ($(this).val() != 0) {
        companies += $(this).val() + ",";
      }
    });
  }

  var formData = new FormData();
  formData.append("id", id);
  formData.append("action", "getTransaction");
  formData.append("cases", cases);
  formData.append("projects", projects);
  formData.append("persons", persons);
  formData.append("companies", companies);

  fetch("api/financial/transaction.php", {
    method: "POST",
    body: formData
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status == "success") {
        var project_id = data.transaction.project_id;
        var person_id = data.transaction.person_id;
        var company_id = data.transaction.company_id;
        var case_id = data.transaction.case_id;
        var amount = data.transaction.amount;
        var date = data.transaction.date;
        var description = data.transaction.description;

        // console.log(data.transaction);

        if (project_select) {
          project_select.val(project_id).trigger("change");
        }
        if (person_select) {
          person_select.val(person_id).trigger("change");
        }
        if (companies_select) {
          companies_select.val(company_id).trigger("change");
        }

        case_select.val(case_id).trigger("change");
        amount_input = $("input[name='" + amount_input + "']").val(amount);
        date_input = $("input[name='" + date_input + "']").val(date);
        description_input = $("textarea[name='" + description_input + "']").val(
          description
        );
        modal.modal("show");

        //preloader gizle
        $(".preloader").hide();
      }
    });
});

// Fetch isteğinden dönen veriyi kullanarak işlemler yapan fonksiyon
function processTransactionData() {}

function customErrorPlacement(error, element) {
  if (element.hasClass("select2")) {
    error.insertAfter(element.next("span"));
  } else {
    error.insertAfter(element);
  }
}

//Virman yaparken çıkış yapılacak kasa seçilince hedef kasaları getirmekiçin
$(document).on("change", "#it_from_cases", function () {
  let from_case_id = $(this).val();
  var formData = new FormData();
  formData.append("from_case_id", from_case_id);
  formData.append("action", "getCaseTransfer");

  fetch("api/financial/transaction.php", {
    method: "POST",
    body: formData
  })
    .then((response) => response.json())
    .then((data) => {
      console.log(data);
      if (data.status == "success") {
        // Başarılı yanıt alındığında kasa seçenekleri oluşturuluyor
        select = "<option value=''>Kasa Seçiniz!!</option>";
        $.each(data.cases, function (index, value) {
          select +=
            "<option value='" + value.id + "'>" + value.case_name + "</option>";
        });

        // Kasa seçenekleri HTML'e ekleniyor
        $("#it_to_case").html(select);
      }
    });
});

//Virman modalindaki kaydet butonuna basınca
$(document).on("click", "#add-case-transfer", function () {
  var form = $("#caseTransferForm");
  var formData = new FormData(form[0]);
  formData.append("action", "intercashTransfer");

  fetch("/api/financial/case.php", {
    method: "POST",
    body: formData
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status == "success") {
        Swal.fire({
          title: "Başarılı!",
          text: data.message,
          icon: data.status,
          confirmButtonText: "Tamam"
        }).then((result) => {
          if (result.isConfirmed) {
            location.reload();
          }
        });
      } else {
        Swal.fire({
          title: "Hata!",
          html: data.message,
          icon: data.status,
          confirmButtonText: "Tamam"
        });
      }
    });
});
