let url = "/pages/dues/debit/api.php";
//Borç bilgilerini getir
export function getDueInfo() {
  //dues tablosundan verileri getir
  let duesId = $("#borc_baslik").val();

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
        // console.log(data.data);

        $("#tutar").val(data.data.amount.replace(".", ","));
        $("#ceza_orani").val(data.data.penalty_rate);
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
export function getBlocksBySite(siteId) {
  var formData = new FormData();
  formData.append("action", "get_blocks");

  // for(let pair of formData.entries()) {
  // console.log(pair[0]+ ', ' + pair[1]);
  // }

  fetch(url, {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      return response.json();
    })
    .then((data) => {
      if (data.status == "success") {
        console.log(data);
        $("#block_id").empty();
        //Block Seçiniz seçeneği ekle
        $("#block_id").append(
          $("<option disabled></option>").val(0).text("Blok Seçiniz")
        );
        $.each(data.data, function (index, block) {
          $("#block_id").append(
            $("<option></option>").val(block.id).text(block.blok_adi)
          );
        });
      }
    });
}

//Blok Seçildiğinde kişileri getir
export function getPeoplesByBlock(blockId) {
  var formData = new FormData();
  formData.append("action", "get_peoples_by_block");
  formData.append("block_id", blockId);

  // for(let pair of formData.entries()) {
  // console.log(pair[0]+ ', ' + pair[1]);
  // }

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
        $("#hedef_kisi").empty();
        //Kişi Seçiniz seçeneği ekle
        $("#hedef_kisi").append(
          $("<option disabled></option>").val(0).text("Kişileri Seçiniz")
        );
        $.each(data.data, function (index, people) {
          $("#hedef_kisi").append(
            $("<option></option>").val(people.id).text(people.daire_kodu + " | " + people.adi_soyadi)
          );
        });
      }
    });
}

//sitenin kişilerini getirir
export function getPeoplesBySite() {
  var formData = new FormData();
  formData.append("action", "get_people_by_site");
  // for(let pair of formData.entries()) {
  // console.log(pair[0]+ ', ' + pair[1]);
  // }

  fetch(url, {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      return response.json();
    })
    .then((data) => {
      if (data.status == "success") {
        $("#hedef_kisi").empty();
        $("#hedef_kisi").append(
          $("<option disabled>Kişi Seçiniz</option>")
        );
        $.each(data.data, function (index, person) {
          $("#hedef_kisi").append(
            $("<option></option>")
              .val(person.id)
              .text(person.daire_kodu + " | " + person.adi_soyadi)
              .attr("data-block", person.block_id)
          );
        });
        if (
          $.fn.select2 &&
          $("#hedef_kisi").hasClass("select2-hidden-accessible")
        ) {
          $("#hedef_kisi").select2("destroy");
        }

        $("#hedef_kisi").select2({
          minimumResultsForSearch: 0,
        });
      }
    });
}


export function getApartmentTypes() {
  //Define tablosundan daire tiplerini getir
  var formData = new FormData();
  formData.append("action", "get_apartment_types");
  fetch(url, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status == "success") {
        $("#apartment_type").empty();
        $("#apartment_type").append(
          $("<option disabled>Daire Tipi seçiniz</option>")
        );
        $.each(data.data, function (index, type) {
          $("#apartment_type").append(
            $("<option></option>").val(type.id).text(type.define_name)
          );
        });
        // Select2'yi başlat ve arama kutusunu etkinleştir
        $("#apartment_type").select2({
          minimumResultsForSearch: 0,
        });
      }
    });
}


/** Sitede yapılan borçlandırmaların bilgilerini getirir
* @param borclandirmaId {int} Borçlandırma ID
* @return {Promise} Borçlandırma bilgilerini içeren Promise
*/
export function getBorclandirmaInfo(borclandirmaId) {
  return new Promise((resolve, reject) => {
    var formData = new FormData();
    formData.append("action", "get_borclandirma_info");
    formData.append("id", borclandirmaId);

    fetch(url, {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {

        if (data.status == "success") {
          resolve(data.data);
        } else {
          reject(data.message);
        }
      })
      .catch((error) => {
        reject(error);
      });
  });
}




/** Gelen id'ye göre borçlandırma detay bilgisini verir
 * @param borclandirmaDetayId {int} Borçlandırma Detay ID
 * @return {Promise} Borçlandırma Detay bilgilerini içeren Promise
 */
export function getBorclandirmaDetayInfo(borclandirmaDetayId) {
  return new Promise((resolve, reject) => {
    var formData = new FormData();
    formData.append("action", "get_borclandirma_detay");
    formData.append("id", borclandirmaDetayId);

 

    fetch(url, {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {

        if (data.status == "success") {
          resolve(data.data);
        } else {
          reject(data.message);
        }
      })
      .catch((error) => {
        reject(error);
      });
  });
}
