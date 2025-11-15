let apiUrl = "/pages/isletme-projesi/api.php";

function addRow(tableId, category = "", amount = "") {
  const tbody = document.querySelector(`#${tableId} tbody`);
  const tr = document.createElement("tr");
  tr.innerHTML = `
    <td><input type="text" class="form-control" name="${tableId === 'income_table' ? 'gelir_kategori[]' : 'gider_kategori[]'}" value="${category}"></td>
    <td><input type="text" class="form-control money" name="${tableId === 'income_table' ? 'gelir_tutar[]' : 'gider_tutar[]'}" value="${amount}"></td>
    <td class="text-end"><button type="button" class="btn btn-sm btn-outline-secondary remove-row"><i class="feather-x"></i></button></td>
  `;
  tbody.appendChild(tr);
}

document.addEventListener("click", (e) => {
  if (e.target.closest("#add_income_row")) {
    addRow("income_table");
  }
  if (e.target.closest("#add_expense_row")) {
    addRow("expense_table");
  }
  if (e.target.closest(".remove-row")) {
    const tr = e.target.closest("tr");
    tr && tr.parentNode.removeChild(tr);
  }
});

document.addEventListener("click", async (e) => {
  if (e.target.closest("#save_project")) {
    const btn = e.target.closest("#save_project");
    btn.disabled = true;
    const original = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> İşleniyor...';

    const form = document.querySelector("#projectForm");
    const $form = $("#projectForm");
    const validator = $form.validate({
      rules: {
        proje_adi: { required: true },
        apartman_site_adi: { required: true },
        donem_baslangic: { required: true },
        donem_bitis: { required: true },
      },
      messages: {
        proje_adi: { required: "Proje adı zorunludur" },
        apartman_site_adi: { required: "Apartman/Site adı zorunludur" },
        donem_baslangic: { required: "Dönem başlangıç tarihi zorunludur" },
        donem_bitis: { required: "Dönem bitiş tarihi zorunludur" },
      },
    });
    if (!validator.form()) {
      btn.disabled = false;
      btn.innerHTML = original;
      return;
    }
    const fd = new FormData(form);
    fd.append("action", "proje_kaydet");

    try {
      const res = await fetch(apiUrl, { method: "POST", body: fd });
      const data = await res.json();
      btn.disabled = false;
      btn.innerHTML = original;
      Swal.fire({
        title: data.status === "success" ? "Başarılı" : "Hata",
        text: data.message,
        icon: data.status,
      }).then(() => {
        if (data.status === "success") {
          if (data.redirect) {
            window.location.href = data.redirect;
          }
        }
      });
    } catch (err) {
      btn.disabled = false;
      btn.innerHTML = original;
      Swal.fire({ title: "Hata", text: err.message || "Bir hata oluştu", icon: "error" });
    }
  }

  if (e.target.closest(".proje-sil")) {
    const id = e.target.closest(".proje-sil").dataset.id;
    const row = e.target.closest("tr");
    const fd = new FormData();
    fd.append("action", "proje_sil");
    fd.append("id", id);
    const ok = await Swal.fire({
      title: "Emin misiniz?",
      text: "Bu işlem geri alınamaz",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Evet, sil",
      cancelButtonText: "İptal",
    });
    if (ok.isConfirmed) {
      const res = await fetch(apiUrl, { method: "POST", body: fd });
      const data = await res.json();
      Swal.fire({
        title: data.status === "success" ? "Başarılı" : "Hata",
        text: data.message,
        icon: data.status,
      });
      if (data.status === "success" && row) {
        row.remove();
      }
    }
  }
});

document.addEventListener("DOMContentLoaded", () => {
  const start = document.querySelector("#donem_baslangic");
  const end = document.querySelector("#donem_bitis");
  if (start && end) {
    start.addEventListener("change", () => {
      const val = start.value;
      if (!val) return;
      const [gun, ay, yil] = val.split(".");
      const d = new Date(`${yil}-${ay}-${gun}`);
      d.setMonth(d.getMonth() + 1);
      d.setDate(0);
      end.value = d.toLocaleDateString("tr-TR");
    });
  }
});