let url = "/pages/dues/debit/api.php";

//Borçlandırma kaydet
$(document).on("click", "#save_debit", function () {
  var form = $("#debitForm");
  var formData = new FormData(form[0]);

  formData.append("action", "save_debit");
  formData.append("id", $("#debit_id").val());

  addCustomValidationMethods(); //validNumber methodu için
  var validator = $("#debitForm").validate({
    rules: {
      amount: {
        required: true,
        validNumber: true,
      },
    },
    messages: {
      amount: {
        required: "Lütfen borç miktarını giriniz",
        validNumber: "Lütfen geçerli bir sayı giriniz",
      },
    },
  });
  if (!validator.form()) {
    return;
  }

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
});

//List sayfasından borçlandırma silmek için
$(document).on("click", ".delete-debit", function () {
  let id = $(this).data("id");
  let Name = $(this).data("name");
  let buttonElement = $(this); // Store reference to the clicked button
  swal
    .fire({
      title: "Emin misiniz?",
      html: `${Name} <br> adlı borcu silmek istediğinize emin misiniz?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Evet",
      cancelButtonText: "Hayır",
    })
    .then((result) => {
      if (result.isConfirmed) {
        var formData = new FormData();
        formData.append("action", "delete_debit");
        formData.append("id", id);

        fetch(url, {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.status == "success") {
              let table = $("#debitTable").DataTable();
              table.row(buttonElement.closest("tr")).remove().draw(false);
              swal.fire("Silindi", `${Name} başarıyla silindi.`, "success");
            }
          });
      }
    });
});

//sayfa yüklenince aidat bilgilerini getir
$(document).ready(function () {
  getDueInfo();
});

//Aidat adı değiştiğinde, aidatın güncel verilerini getir
$(document).on("change", "#due_title", function () {
  getDueInfo();
});

//Aidat adı değiştiğinde, aidatın güncel verilerini getir
$(document).on("change", "#block_id", function () {
  getPeoplesByBlock($(this).val());
});

//Borç bilgilerini getir
function getDueInfo() {
  //dues tablosundan verileri getir
  let duesId = $("#due_title").val();

  var formData = new FormData();
  formData.append("action", "get_due_info");
  formData.append("id", duesId);

  fetch(url, {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      return response.json();
    })
    .then((data) => {
      if (data.status == "success") {
        //console.log(data.data);

        $("#amount").val(data.data.amount.replace(".", ","));
        $("#penalty_rate").val(data.data.penalty_rate);
      } else {
        swal.fire({
          title: "Hata",
          text: data.message,
          icon: "error",
          confirmButtonText: "Tamam",
        });
      }
    });
}

//Site değiştiğinde blokları getir
function getBlocksBySite(siteId) {
  var formData = new FormData();
  formData.append("action", "get_blocks");

  fetch(url, {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      return response.json();
    })
    .then((data) => {
      if (data.status == "success") {
        $("#block_id").empty();
        //Block Seçiniz seçeneği ekle
        $("#block_id").append(
          $("<option></option>").val(0).text("Blok Seçiniz")
        );
        $.each(data.data, function (index, block) {
          $("#block_id").append(
            $("<option></option>").val(block.id).text(block.block_name)
          );
        });
      }
    });
}

//Blok Seçildiğinde kişileri getir
function getPeoplesByBlock(blockId) {
  var formData = new FormData();
  formData.append("action", "get_peoples_by_block");
  formData.append("block_id", blockId);

  fetch(url, {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      return response.json();
    })
    .then((data) => {
      if (data.status == "success") {
        //console.log(data.data);
        $("#target_person").empty();
        //Kişi Seçiniz seçeneği ekle
        $("#target_person").append(
          $("<option disabled></option>").val(0).text("Kişileri Seçiniz")
        );
        $.each(data.data, function (index, people) {
          $("#target_person").append(
            $("<option></option>").val(people.id).text(people.fullname)
          );
        });
       }
    });
}
