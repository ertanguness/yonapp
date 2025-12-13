function duyuruInit() {
  const token = localStorage.getItem("jwt_token") || "";
  const apiUrl = "/pages/duyuru-talep/admin/api/APIDuyuru.php";
  const $tbl = $("#announcementList");
  if ($tbl.length) {
    const dt = $tbl.DataTable({
      retrieve: true,
      responsive: true,
      serverSide: true,
      processing: true,
      ajax: {
        url: apiUrl + "?datatables=1",
        type: "GET",
        beforeSend: function (xhr) {
          if (token) xhr.setRequestHeader("Authorization", "Bearer " + token);
        }
      },
      columns: [
        { data: 0 },
        { data: 1 },
        { data: 2 },
        { data: 3 },
        { data: 4 },
        { data: 5 },
        {
          data: 6,
          render: function (id) {
            const editUrl =
              "index?p=duyuru-talep/admin/announcements-manage&id=" + id;
            return (
              '<a href="' +
              editUrl +
              '" class="btn btn-sm btn-outline-primary me-1">Düzenle</a>' +
              '<button type="button" class="btn btn-sm btn-outline-danger duyuru-sil" data-id="' +
              id +
              '">Sil</button>'
            );
          }
        }
      ],
      dom: 'f t<"row m-2"<"col-md-4"i><"col-md-4"l><"col-md-4 float-end"p>>'
    });
    $tbl.on("click", ".duyuru-sil", function () {
      const id = $(this).data("id");
      swal
        .fire({
          title: "Emin misiniz?",
          text: "Bu işlem geri alınamaz",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Sil",
          cancelButtonText: "Vazgeç"
        })
        .then(function (res) {
          if (!res.isConfirmed) return;
          fetch(apiUrl, {
            method: "DELETE",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
              ...(token ? { Authorization: "Bearer " + token } : {})
            },
            body: new URLSearchParams({ id: String(id) })
          })
            .then((r) => r.json())
            .then(function (data) {
              var title = data.status === "success" ? "Başarılı" : "Hata";
              swal.fire({
                title,
                text: data.message,
                icon: data.status,
                confirmButtonText: "Tamam"
              });
              if (data.status === "success") dt.ajax.reload(null, false);
            });
        });
    });
  }

  const $form = $("#announcementForm");
  if ($form.length) {
    const $title = $("#title");
    const $id = $("#id");
    const $contentHidden = $("#content");
    var quill = null;
    if (window.Quill && document.querySelector('#contentEditor')) {
      quill = new Quill('#contentEditor', { theme: 'snow', placeholder: 'Duyuru içeriği' });
      try {
        var initialHtml = ($contentHidden.val() || "");
        if (initialHtml) {
          if (quill.clipboard && typeof quill.clipboard.dangerouslyPasteHTML === 'function') {
            quill.clipboard.dangerouslyPasteHTML(initialHtml);
          } else {
            quill.root.innerHTML = initialHtml;
          }
        }
      } catch(e){}
    }
    const $start = $("#start_date");
    const $end = $("#end_date");
  const $status = $("#status");
  const $targetType = $("#target_type");
  const $blockRow = $("#blockSelectRow");
  const $blockId = $("#block_id");
  const $kisiRow = $("#kisiSelectRow");
  const $kisiIds = $("#kisi_ids");
  const $previewSection = $("#emailPreviewSection");
    const $previewSubject = $("#previewSubject");
    const $previewMessage = $("#previewMessage");
    function getContentHtml(){
      if (quill) { return quill.root.innerHTML; }
      return ($contentHidden.val()||"");
    }
    function updatePreview() {
      $previewSubject.text($title.val() || "[Konu]");
      $previewMessage.html(getContentHtml() || "[Mesaj]");
    }
    $title.on("input", updatePreview);
    if(!quill){ $("#contentEditor, #content").on("input", updatePreview); } else { quill.on('text-change', updatePreview); }
    $("#saveAnnouncement").on("click", function () {
      $form.trigger("submit");
    });

    function toggleTargets(){
      var v = ($targetType.val()||'all');
      if (v === 'block') {
        $blockRow.removeClass('d-none');
        $kisiRow.addClass('d-none');
        $previewSection.removeClass('d-none');
      } else if (v === 'kisi') {
        $kisiRow.removeClass('d-none');
        $blockRow.addClass('d-none');
        $previewSection.removeClass('d-none');
      } else {
        $blockRow.addClass('d-none');
        $kisiRow.addClass('d-none');
        $previewSection.addClass('d-none');
      }
      try { $('.select2').select2({ dropdownParent: $form }); } catch(e) {}
      updatePreview();
    }
    $targetType.on('change', toggleTargets);
    toggleTargets();

  
    $form.on("submit", function (e) {
      e.preventDefault();
      const title = ($title.val() || "").trim();
      var content = (getContentHtml()||"").trim();
      const start = $start.val() || "";
      const end = $end.val() || "";
      const status = $status.val() || "draft";
      if (!title) {
        swal.fire({ title: "Hata", text: "Başlık zorunlu", icon: "error" });
        return;
      }
      if (!content) {
        swal.fire({ title: "Hata", text: "İçerik zorunlu", icon: "error" });
        return;
      }
      if (start && end && new Date(end) < new Date(start)) {
        swal.fire({
          title: "Hata",
          text: "Bitiş tarihi başlangıçtan küçük olamaz",
          icon: "error"
        });
        return;
      }
      const fd = new URLSearchParams();
      const id = (($id.val()||"").trim());
      fd.append("title", title);
      $contentHidden.val(content);
      fd.append("content", content);
      if (start) fd.append("start_date", start);
      if (end) fd.append("end_date", end);
      fd.append("status", status);
      fd.append('target_type', $targetType.val()||'all');
      if(($targetType.val()||'')==='block' && ($blockId.val()||'')) { fd.append('block_id', $blockId.val()); }
      if(($targetType.val()||'')==='kisi'){
        var ids = ($kisiIds.val()||[]);
        if(ids.length){ fd.append('kisi_ids', ids.join(',')); }
      }
      if (id) { fd.append("id", id); }
      const method = id ? "PUT" : "POST";
      fetch(apiUrl, {
        method,
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
          ...(token ? { Authorization: "Bearer " + token } : {})
        },
        body: fd
      })
        .then((r) => r.json())
        .then(function (data) {
          var titleTxt = data.status === "success" ? "Başarılı" : "Hata";
          swal.fire({
            title: titleTxt,
            text: data.message,
            icon: data.status,
            confirmButtonText: "Tamam"
          }).then(function () {
            if (data.status === "success") {
              window.location = 'duyuru-listesi';
            }
          });
        });
    });
  }
}
(function waitForJQ() {
  if (typeof window.$ === "function") {
    $(duyuruInit);
  } else {
    setTimeout(waitForJQ, 100);
  }
})();
