(function () {
  const API_URL = "/pages/duyuru-talep/admin/api/APIAnket.php";

  async function ensureDataTables() {
    if (typeof jQuery === "undefined" || typeof $ === "undefined") {
      return;
    }
    if ($.fn && $.fn.DataTable) {
      return;
    }
    const head = document.head;
    const cssHref = "/assets/vendors/css/dataTables.bs5.min.css";
    if (!document.querySelector(`link[href="${cssHref}"]`)) {
      const link = document.createElement("link");
      link.rel = "stylesheet";
      link.href = cssHref;
      head.appendChild(link);
    }
    await new Promise((res) => {
      const s1 = document.createElement("script");
      s1.src = "/assets/vendors/js/dataTables.min.js";
      s1.onload = () => {
        const s2 = document.createElement("script");
        s2.src = "/assets/vendors/js/dataTables.bs5.min.js";
        s2.onload = res;
        head.appendChild(s2);
      };
      head.appendChild(s1);
    });
  }

  const SurveyAPI = {
    async list() {
      const r = await fetch(`${API_URL}?action=surveys_list`);
      return r.json();
    },
    async get(id) {
      const r = await fetch(
        `${API_URL}?action=get&id=${encodeURIComponent(id)}`
      );
      return r.json();
    },
    async create(payload) {
      const fd = new FormData();
      fd.append("action", "survey_save");
      fd.append("title", payload.title);
      if (payload.description) fd.append("description", payload.description);
      if (payload.start_date) fd.append("start_date", payload.start_date);
      if (payload.end_date) fd.append("end_date", payload.end_date);
      if (payload.status) fd.append("status", payload.status);
      (payload.options || []).forEach((o) => fd.append("options[]", o));
      const r = await fetch(API_URL, { method: "POST", body: fd });
      return r.json();
    },
    async update(id, payload) {
      const fd = new FormData();
      fd.append("action", "update");
      fd.append("id", id);
      Object.entries(payload).forEach(([k, v]) => {
        if (k === "options") {
          (v || []).forEach((o) => fd.append("options[]", o));
        } else if (v !== undefined && v !== null) {
          fd.append(k, v);
        }
      });
      const r = await fetch(API_URL, { method: "POST", body: fd });
      return r.json();
    },
    async delete(idEnc) {
      const fd = new FormData();
      fd.append("action", "delete");
      fd.append("id", idEnc);
      const r = await fetch(API_URL, { method: "POST", body: fd });
      return r.json();
    },
  };

  const SurveyUI = {
    async initList() {
      await ensureDataTables();
      const rows = await SurveyAPI.list();
      const $tb = $("#surveyList tbody");
      $tb.empty();
      rows.forEach(function (r, idx) {
        const statusBadge =
          r.status === "Aktif"
            ? "success"
            : r.status === "Taslak"
            ? "warning"
            : "secondary";
        const tr = `<tr>
          <td>${idx + 1}</td>
          <td>${r.title}</td>
          <td>${r.created_at ?? ""}</td>
          <td>${r.end_date ?? ""}</td>
          <td><span class="badge bg-${statusBadge}">${r.status}</span></td>
          <td>${r.total_votes ?? "-"}</td>
          <td>
            <div class="btn-group align-items-baseline">
              <a href="/anket-ekle?survey_id=${
                r.id
              }" class="btn btn-outline-primary btn-sm route-link"><i class="feather-edit-2"></i> Düzenle</a>
              <button class="btn btn-outline-danger btn-sm btn-del" data-id="${
                r.id_enc
              }"><i class="feather-trash-2"></i> Sil</button>
            </div>
          </td>
        </tr>`;
        $tb.append(tr);
      });
      $("#surveyList").DataTable({
        retrieve: true,
        responsive: true,
        dom: 'f t<"row m-2"<"col-md-4"i><"col-md-4"l><"col-md-4 float-end"p>>',
      });
      $tb.on("click", ".btn-del", async function () {
        const idEnc = this.getAttribute("data-id");
        const ok = await swal.fire({
          title: "Silinsin mi?",
          text: "Bu işlem geri alınamaz",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Evet",
          cancelButtonText: "Hayır",
        });
        if (ok.isConfirmed) {
          const res = await SurveyAPI.delete(idEnc);
          const title = res.status === "success" ? "Başarılı" : "Hata";
          await swal.fire({ title, text: res.message, icon: res.status });
          if (res.status === "success") {
            this.closest("tr").remove();
          }
        }
      });
    },
    async initListServerRendered() {
      await ensureDataTables();
      const $tb = $("#surveyList tbody");
      $tb.on("click", ".btn-del", async function () {
        const idEnc = this.getAttribute("data-id");
        const ok = await swal.fire({
          title: "Silinsin mi?",
          text: "Bu işlem geri alınamaz",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Evet",
          cancelButtonText: "Hayır",
        });
        if (ok.isConfirmed) {
          const res = await SurveyAPI.delete(idEnc);
          const title = res.status === "success" ? "Başarılı" : "Hata";
          await swal.fire({ title, text: res.message, icon: res.status });
          if (res.status === "success") {
            this.closest("tr").remove();
          }
        }
      });
      $tb.on("click", ".change-status", async function (e) {
        e.preventDefault();
        const id = this.getAttribute("data-id");
        const status = this.getAttribute("data-status");
        const fd = new FormData();
        fd.append("action", "change_status");
        fd.append("id", id);
        fd.append("status", status);
        const r = await fetch(API_URL, { method: "POST", body: fd });
        const res = await r.json();
        const title = res.status === "success" ? "Güncellendi" : "Hata";
        await swal.fire({ title, text: res.message, icon: res.status });
        if (res.status === "success") {
          location.reload();
        }
      });
    },
    async initManage() {
      const addBtn = document.getElementById("addOption");
      const wrapper = document.getElementById("optionsWrapper");
      addBtn.addEventListener("click", () => {
        const div = document.createElement("div");
        div.classList.add("input-group", "mb-2");
        div.innerHTML = `
          <input type="text" name="options[]" class="form-control" placeholder="Yeni Seçenek" required>
          <button type="button" class="btn btn-outline-danger removeOption">Sil</button>
        `;
        wrapper.appendChild(div);
      });
      wrapper.addEventListener("click", function (e) {
        if (e.target.classList.contains("removeOption")) {
          e.target.closest(".input-group").remove();
        }
      });

      const params = new URLSearchParams(location.search);
      const surveyId = params.get("survey_id");
      if (surveyId) {
        const resp = await SurveyAPI.get(surveyId);
        if (resp.status === "success") {
          const d = resp.data;
          $("#pollTitle").val(d.title || "");
          $("#pollDescription").val(d.description || "");
          $("#pollStartDate").val((d.start_date || "").substring(0, 10));
          $("#pollEndDate").val((d.end_date || "").substring(0, 10));
          $("#pollStatus").val(d.status || "Taslak");
          $("#optionsWrapper").empty();
          (d.options || []).forEach((o) => {
            const div = document.createElement("div");
            div.classList.add("input-group", "mb-2");
            div.innerHTML = `
              <input type="text" name="options[]" value="${o}" class="form-control" placeholder="Seçenek" required>
              <button type="button" class="btn btn-outline-danger removeOption">Sil</button>`;
            wrapper.appendChild(div);
          });
        }
      }

      $("#saveSurvey")
        .off("click")
        .on("click", function (e) {
          e.preventDefault();
          const form = document.getElementById("pollForm");
          if (form.requestSubmit) {
            form.requestSubmit();
          } else {
            form.dispatchEvent(new Event("submit", { cancelable: true }));
          }
        });
      const submitHandler = async function (e) {
        e.preventDefault();
        const $btn = $("#saveSurvey");
        if ($btn.prop("disabled")) return;

        const titleEl = document.getElementById("pollTitle");
        const descEl = document.getElementById("pollDescription");
        const startEl = document.getElementById("pollStartDate");
        const endEl = document.getElementById("pollEndDate");
        const statusEl = document.getElementById("pollStatus");
        const title = ((titleEl && titleEl.value) || "").trim();
        const description = descEl ? descEl.value : "";
        const start_date = startEl ? startEl.value : "";
        const end_date = endEl ? endEl.value : "";
        const status = statusEl ? statusEl.value : "Taslak";
        const options = Array.from(
          document.querySelectorAll('#optionsWrapper input[name="options[]"]')
        )
          .map((i) => i.value.trim())
          .filter(Boolean);
        if (!title || options.length < 2) {
          if (window.swal && swal.fire) {
            swal.fire({
              title: "Eksik Bilgi",
              text: "Başlık ve en az iki seçenek gerekli",
              icon: "warning",
            });
          } else {
            alert("Başlık ve en az iki seçenek gerekli");
          }
          return;
        }

        $btn
          .prop("disabled", true)
          .html('<i class="feather-loader fa-spin me-2"></i> Kaydediliyor...');

        let res;
        try {
          if (surveyId) {
            res = await SurveyAPI.update(surveyId, {
              title,
              description,
              start_date,
              end_date,
              status,
              options,
            });
          } else {
            res = await SurveyAPI.create({
              title,
              description,
              start_date,
              end_date,
              status,
              options,
            });
          }
        } catch (err) {
          if (window.swal && swal.fire) {
            await swal.fire({
              title: "Hata",
              text: String(err),
              icon: "error",
            });
          } else {
            alert("Hata: " + String(err));
          }
          $btn
            .prop("disabled", false)
            .html('<i class="feather-send me-2"></i> Kaydet');
          return;
        }
        const titleSw = res.status === "success" ? "Başarılı" : "Hata";
        if (window.swal && swal.fire) {
          await swal.fire({
            title: titleSw,
            text: res.message,
            icon: res.status,
          });
        } else {
          alert(`${titleSw}: ${res.message}`);
        }

        if (res.status === "success") {
          window.location = "/anket-listesi";
        } else {
          $btn
            .prop("disabled", false)
            .html('<i class="feather-send me-2"></i> Kaydet');
        }
      };

      if (window.jQuery && $("#pollForm").on) {
        $("#pollForm").off("submit").on("submit", submitHandler);
      } else {
        const formEl = document.getElementById("pollForm");
        formEl.removeEventListener("submit", submitHandler); // Just in case
        formEl.addEventListener("submit", submitHandler);
      }
    },
  };

  window.SurveyAPI = SurveyAPI;
  window.SurveyUI = SurveyUI;
})();
