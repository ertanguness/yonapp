/* global bootstrap, Pace, Swal, Toastify, setButtonLoading */

// Tahsilat dashboard (list-new.php) tüm JS burada.
(function ($) {
  'use strict';

  var urls = {
    dashboardApi: '/pages/dues/payment/api/borc-dashboard.php',
    actionApi: '/pages/dues/payment/api.php',
    borcModal: '/pages/dues/payment/modal/modal_borc_ekle.php',
    tahsilatModal: 'pages/dues/payment/modal/tahsilat_gir_modal.php'
  };

  function safeUrl() {
    try {
      return new URL(window.location.href);
    } catch (e) {
      return null;
    }
  }

  function getKisiFromUrl() {
    var u = safeUrl();
    return u ? (u.searchParams.get('kisi') || '') : '';
  }

  function getSelectedKisiEncFallback() {
    try {
      // Son yüklenen kişinin enc'i (dashboard API zaten bunu dönüyor)
      if (ydLastPersonData && ydLastPersonData.person && ydLastPersonData.person.kisi_enc) {
        return String(ydLastPersonData.person.kisi_enc || '');
      }
    } catch (e) {
      // ignore
    }

    // DOM'dan aktif seçili kişiyi bul
    var $active = $('#ydPersonnelList').find('a.yd-item.is-active[data-yd-kisi]').first();
    if ($active.length) return String($active.data('yd-kisi') || '');

    // Son çare: ilk görünen kişi
    var $first = $('#ydPersonnelList').find('a.yd-item[data-yd-kisi]').first();
    if ($first.length) return String($first.data('yd-kisi') || '');

    return '';
  }

  function setUrlParamSafe(u, key, value) {
    if (!u || !key) return;
    var v = (value == null ? '' : String(value)).trim();
    if (!v || (key === 'yd_filter' && v === 'all')) u.searchParams.delete(key);
    else u.searchParams.set(key, v);
  }

  function esc(s) {
    return (s == null ? '' : String(s))
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  // ---- select2 helper (modal/ajax içerikleri için güvenli init) ----
  function initSelect2InScope(scopeEl, dropdownParentEl) {
    try {
      if (typeof $.fn.select2 !== 'function') return;

      // scope: element / jquery / selector string
      var $scope = scopeEl ? (scopeEl.jquery ? scopeEl : $(scopeEl)) : $(document);
      if (!$scope || !$scope.length) $scope = $(document);

      var $parent = dropdownParentEl ? (dropdownParentEl.jquery ? dropdownParentEl : $(dropdownParentEl)) : $scope;
      if (!$parent || !$parent.length) $parent = $scope;

      var $sels = $scope.find('select.select2, .select2');
      $sels.each(function () {
        var $el = $(this);

        // önce varsa eski instance'ı sök
        try {
          if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
        } catch (e0) {
          // ignore
        }

        // tekrar init
        try {
          $el.select2({ dropdownParent: $parent });
        } catch (e1) {
          // ignore
        }
      });
    } catch (eSel2) {
      // ignore
    }
  }

  // ---- Sol panel arama + chip filtre ----
  function initLeftSearchAndFilter() {
    var $search = $('#ydSearch');
    var $list = $('#ydPersonnelList');
    var $filterBtns = $('[data-yd-filter]');
    if (!$search.length || !$list.length) return;

    var currentFilter = 'all';

    function restore() {
      var u = safeUrl();
      if (!u) return;
      var q = u.searchParams.get('yd_q') || '';
      var f = u.searchParams.get('yd_filter') || 'all';
      if ($.inArray(f, ['all', 'has_debt', 'paid']) === -1) f = 'all';
      if (q) $search.val(q);
      currentFilter = f;
      $filterBtns.each(function () {
        var $b = $(this);
        $b.toggleClass('is-active', ($b.attr('data-yd-filter') || 'all') === currentFilter);
      });
    }

    function persist() {
      var u = safeUrl();
      if (!u) return;
      setUrlParamSafe(u, 'yd_q', $search.val());
      setUrlParamSafe(u, 'yd_filter', currentFilter);
      try {
        history.replaceState({}, '', u.toString());
      } catch (e) {
        // ignore
      }
    }

    function apply() {
      var term = ($search.val() || '').toString().toLowerCase().trim();
      $list.find('.yd-item').each(function () {
        var $item = $(this);
        var txt = ($item.text() || '').toLowerCase();
        var net = Number($item.data('yd-net') || 0);
        net = isFinite(net) ? net : 0;
        var hasDebt = net < 0;

        var matchChip = true;
        if (currentFilter === 'has_debt') matchChip = hasDebt;
        if (currentFilter === 'paid') matchChip = !hasDebt;
        var matchSearch = (!term || txt.indexOf(term) !== -1);

        $item.toggle(matchChip && matchSearch);
      });
      persist();
    }

    $search.on('input', apply);
    $filterBtns.on('click', function () {
      var $btn = $(this);
      currentFilter = $btn.attr('data-yd-filter') || 'all';
      $filterBtns.removeClass('is-active');
      $btn.addClass('is-active');
      apply();
    });

    restore();
    apply();
  }

  // ---- Dashboard sağ panel: ajax+render ----
  var ydLastPersonData = null;
  var ydDebtMetaByIdEnc = {};
  // Sol listede en son seçilen kişi (satır) state'i
  var ydSelectedKisiEnc = '';
  var ydSelectedLeftRow = null;

  function setActiveLeftItem($list, $el) {
    $list.find('.yd-item').removeClass('is-active');
    if ($el && $el.length) $el.addClass('is-active');
  }

  function setTab(tab) {
    $('.yd-tab-btn').each(function () {
      var $b = $(this);
      $b.toggleClass('is-active', $b.data('yd-tab') === tab);
    });
    $('.yd-tab-panel').each(function () {
      var $p = $(this);
      $p.toggle($p.data('yd-panel') === tab);
    });
  }

  function filterDebtRows() {
    var onlyRemaining = !!$('#ydOnlyDebtsToggle').prop('checked');
    var $tb = $('#ydDebtsTbody');
    if (!$tb.length) return;

    var $rows = $tb.find('tr.yd-debt-row');
    if (onlyRemaining) {
      $rows.each(function () {
        var $tr = $(this);
        var canSelect = String($tr.attr('data-yd-can-select') || '0') === '1';
        $tr.toggle(canSelect);
        if (!canSelect) {
          var $chk = $tr.find('input.yd-debt-check');
          if ($chk.prop('checked')) $chk.prop('checked', false);
          $tr.removeClass('yd-is-active');
        }
      });
    } else {
      $rows.show();
    }

    var anyChecked = $('.yd-debt-check:checked').length > 0;
    $('#ydCollectSelectedDebts').prop('disabled', !anyChecked);
  }

  function syncDebtActiveRow($tr) {
    var checked = !!$tr.find('input.yd-debt-check').prop('checked');
    $tr.toggleClass('yd-is-active', checked);
  }

  function renderDebts(rows) {
    var $tb = $('#ydDebtsTbody');
    if (!$tb.length) return;
    var $collectBtn = $('#ydCollectSelectedDebts');
    var $selectAllBtn = $('#ydSelectAllDebts');

    if (!rows || !rows.length) {
      $tb.html('<tr><td class="px-4" colspan="7"><span class="yd-muted">Borçlandırma detayı bulunamadı.</span></td></tr>');
      $collectBtn.prop('disabled', true);
      $selectAllBtn.prop('disabled', true);
      return;
    }

    $selectAllBtn.prop('disabled', false);
    ydDebtMetaByIdEnc = {};

    var html = $.map(rows, function (r) {
      var title = (r.borc_adi || r.aciklama || '');
      var desc = (r.aciklama && r.borc_adi && r.aciklama !== r.borc_adi) ? r.aciklama : '';
      var period = '';
      if (r.baslangic_tarihi || r.bitis_tarihi) period = (r.baslangic_tarihi || '') + (r.bitis_tarihi ? ' - ' + r.bitis_tarihi : '');
      else if (r.son_odeme_tarihi) period = 'Son Ödeme: ' + r.son_odeme_tarihi;

      if (r.id_enc) {
        ydDebtMetaByIdEnc[r.id_enc] = {
          borc_adi: r.borc_adi || '',
          aciklama: r.aciklama || '',
          baslangic_tarihi: r.baslangic_tarihi || '',
          bitis_tarihi: r.bitis_tarihi || '',
          son_odeme_tarihi: r.son_odeme_tarihi || '',
          gecikme_fmt: r.hesaplanan_gecikme_zammi_fmt || '',
          kalan_fmt: r.toplam_kalan_borc_fmt || ''
        };
      }

      var kalan = parseFloat(r.toplam_kalan_borc || 0);
      var canSelect = kalan > 0.00001;
      var textColor = canSelect ? 'text-danger' : 'text-muted';
      var checkbox = canSelect ? '<input class="form-check-input yd-debt-check" type="checkbox" data-yd-borc-id="' + esc(r.id_enc || '') + '">' : '';

      var toplamCell =
        '<div class="fw-bold">' + esc(r.tutar_fmt || '') + '</div>' +
        '<div class="yd-muted" style="font-size:12px;">G. Zammı: ' + esc(r.hesaplanan_gecikme_zammi_fmt || '') + '</div>';

      var actions =
        '<div class="hstack gap-1 justify-content-end">' +
        '  <a href="javascript:void(0);" class="avatar-text avatar-md yd-borc-edit" data-id="' + esc(r.id_enc || '') + '" title="Düzenle"><i class="feather-edit"></i></a>' +
        '  <a href="javascript:void(0);" class="avatar-text avatar-md text-danger yd-borc-delete" data-id="' + esc(r.id_enc || '') + '" title="Sil"><i class="feather-trash-2"></i></a>' +
        '</div>';

      return (
        '<tr class="yd-debt-row" data-yd-can-select="' + (canSelect ? '1' : '0') + '">' +
        '  <td class="px-4">' + checkbox + '</td>' +
        '  <td class="px-4">' +
        '    <div class="fw-bold">' + esc(title) + '</div>' +
        (desc ? ('<div class="yd-muted" style="font-size:12px;">' + esc(desc) + '</div>') : '') +
        '  </td>' +
        '  <td>' + esc(period) + '</td>' +
        '  <td class="text-end">' + toplamCell + '</td>' +
        '  <td class="text-end">' + esc(r.yapilan_tahsilat_fmt || '') + '</td>' +
        '  <td class="text-end fw-bold ' + textColor + '">' + esc(r.toplam_kalan_borc_fmt || '') + '</td>' +
        '  <td class="text-end">' + actions + '</td>' +
        '</tr>'
      );
    }).join('');

    $tb.html(html);
    $('#ydCollectSelectedDebts').prop('disabled', $('.yd-debt-check:checked').length === 0);
    filterDebtRows();
  }

  function renderTahsilatlar(type, data) {
    var $tb = $('#ydTahsilatTbody');
    if (!$tb.length) return;

    var isArr = $.isArray(data);
    var isObj = (!isArr && data && typeof data === 'object');
    if (!data || (isArr && data.length === 0) || (isObj && Object.keys(data).length === 0)) {
      $tb.html('<tr><td class="px-4" colspan="5"><span class="yd-muted">Tahsilat kaydı bulunamadı.</span></td></tr>');
      return;
    }

    if (type === 'grouped' && isArr) type = 'rows';

    var rowsHtml = '';
    if (type === 'grouped' && isObj) {
      $.each(data, function (k, t) {
        rowsHtml +=
          '<tr>' +
          '  <td class="px-4">' + esc(t.islem_tarihi || '') + '</td>' +
          '  <td>' + esc(t.ana_aciklama || '') + '</td>' +
          '  <td class="text-end fw-bold">' + esc(t.toplam_tutar || '') + '</td>' +
          '  <td class="text-end"><span class="yd-muted" style="font-size:12px;">-</span></td>' +
          '  <td class="text-end"><span class="yd-muted" style="font-size:12px;">-</span></td>' +
          '</tr>';
      });
    } else {
      $.each(isArr ? data : [], function (_, t) {
        var tIdEnc = t.id_enc || t.tahsilat_id_enc || t.tahsilat_enc || t.id_sifreli || t.id || '';
        var detaylar = $.isArray(t.detaylar) ? t.detaylar : [];
        var hasDetay = detaylar.length > 0;
        var detailKey = tIdEnc ? String(tIdEnc) : ('row_' + Math.random().toString(36).slice(2));

        var actionHtml = '<span class="yd-muted" style="font-size:12px;">-</span>';
        if (tIdEnc) {
          var detailPart = hasDetay ?
            '<button type="button" class="avatar-text avatar-md tahsilat-detay-goster" data-id="' + esc(tIdEnc) + '" title="Detay"><i class="feather-chevron-down"></i></button>' :
            '<span class="yd-muted" style="font-size:12px;">-</span>';

          actionHtml =
            '<div class="text-center d-flex justify-content-center align-items-center gap-1">' +
            detailPart +
            '<a href="#" id="delete-tahsilat" data-id="' + esc(tIdEnc) + '" class="avatar-text avatar-md" title="Sil"><i class="feather-trash-2"></i></a>' +
            '</div>';
        }

        var krediVal = Number(t.kullanilan_kredi || 0);
        var krediFmt = (t.kullanilan_kredi_fmt || '').toString();
        var tutarCell = '<div class="fw-bold">' + esc(t.tutar || '') + '</div>';
        if (krediVal > 0.00001) tutarCell += '<div class="yd-muted" style="font-size:12px;">Kredi: ' + esc(krediFmt || '') + '</div>';

        var detailBody = '';
        if (hasDetay) {
          detailBody =
            '<div class="yd-tahsilat-detail-box">' +
            '  <div class="yd-tahsilat-detail-title">::TAHSİLAT DAĞILIMI::</div>' +
            $.map(detaylar, function (d) {
              return (
                '<div class="yd-tahsilat-detail-item">' +
                '  <div style="min-width:0;">' +
                '    <div class="name">' + esc(d.borc_adi || '') + '</div>' +
                (d.aciklama ? ('<div class="desc">' + esc(d.aciklama) + '</div>') : '') +
                '  </div>' +
                '  <div class="amt">' + esc(d.odenen_tutar_fmt || d.odenen_tutar || '') + '</div>' +
                '</div>'
              );
            }).join('') +
            '</div>';
        }

        rowsHtml +=
          '<tr class="yd-tahsilat-row" data-detail-key="' + esc(detailKey) + '" data-tahsilat-id="' + esc(tIdEnc) + '">' +
          '  <td class="px-4">' + esc(t.islem_tarihi || t.tarih || '') + '</td>' +
          '  <td class="yd-desc">' + esc(t.aciklama || '') + '</td>' +
          '  <td class="text-end">' + tutarCell + '</td>' +
          '  <td class="text-end" colspan="2">' + actionHtml + '</td>' +
          '</tr>';

        if (hasDetay) {
          rowsHtml +=
            '<tr class="yd-tahsilat-detail-row" data-detail-key="' + esc(detailKey) + '" style="display:none;">' +
            '  <td class="px-4" colspan="5">' + detailBody + '</td>' +
            '</tr>';
        }
      });
    }

    $tb.html(rowsHtml || '<tr><td class="px-4" colspan="5"><span class="yd-muted">Tahsilat kaydı bulunamadı.</span></td></tr>');
  }

  function setHeader(data) {
    var $name = $('#ydSelectedName');
    var $status = $('#ydSelectedStatus');
    var $unit = $('#ydSelectedUnit');
    var $phone = $('#ydSelectedPhone');
    var $wa = $('#ydWhatsappLink');

    var kisiEnc = data && data.person && data.person.kisi_enc ? String(data.person.kisi_enc) : '';
    var profHref = '/site-sakini-duzenle/' + encodeURIComponent(kisiEnc);

    if ($name.length) {
      $name.text((data.person && data.person.adi_soyadi) || '').attr('href', profHref);
    }
    $unit.text('Daire ' + ((data.person && data.person.daire_kodu) || ''));
    $phone.text((data.person && data.person.telefon) || '');

    var st = (data.person && data.person.status) || '';
    $status
      .text('Durum: ' + st)
      .removeClass('yd-chip-danger yd-chip-success')
      .addClass(st === 'Borçlu' ? 'yd-chip yd-chip-danger' : 'yd-chip yd-chip-success');

    var telRaw = (data.person && data.person.telefon) ? String(data.person.telefon) : '';
    var telefonTemiz = telRaw.replace(/\D/g, '');
    var waTel = telefonTemiz;
    if (waTel && waTel.length >= 10) {
      if (waTel.charAt(0) === '0') waTel = '90' + waTel.substring(1);
      else if (waTel.length === 10) waTel = '90' + waTel;
    }

    var kisiAdi = (data.person && data.person.adi_soyadi) ? String(data.person.adi_soyadi) : '';
    var bakiye = (data.kpi && data.kpi.kalan_borc_fmt) ? String(data.kpi.kalan_borc_fmt) : '';
    var txt = 'Sayın ' + kisiAdi + ',\n\nGüncel bakiye bilginiz aşağıdaki gibidir:\n\n📊 Bakiye: ' + bakiye + ';\n\nSaygılarımızla,';
    var waHref = waTel ? ('https://wa.me/' + encodeURIComponent(waTel) + '?text=' + encodeURIComponent(txt)) : '#';
    if ($wa.length) $wa.attr('href', waHref).attr('data-kisi-id', kisiEnc);

    // KPI
    $('#ydKalanBorcHeader').text((data.kpi && data.kpi.kalan_borc_fmt) ? data.kpi.kalan_borc_fmt : '');
    $('#ydKpiKalan').text((data.kpi && data.kpi.kalan_borc_fmt) ? data.kpi.kalan_borc_fmt : '');

    var toplamBorcFmt = (data.kpi && data.kpi.toplam_borc_fmt) ? data.kpi.toplam_borc_fmt : '';
    var toplamBorcVal = (data.kpi && typeof data.kpi.toplam_borc !== 'undefined') ? Number(data.kpi.toplam_borc || 0) : 0;
    if (!toplamBorcVal && $.isArray(data.borclandirma_detaylari)) {
      toplamBorcVal = $.map(data.borclandirma_detaylari, function (r) {
        return Number(r.tutar || 0) + Number(r.hesaplanan_gecikme_zammi || 0);
      }).reduce(function (sum, x) { return sum + x; }, 0);
      try {
        toplamBorcFmt = toplamBorcVal.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' TL';
      } catch (e) {
        toplamBorcFmt = String(toplamBorcVal) + ' TL';
      }
    }
    $('#ydKpiToplamBorc').text(toplamBorcFmt);

    var tahsilFmt = (data.kpi && data.kpi.toplam_tahsilat_fmt) ? data.kpi.toplam_tahsilat_fmt : '';
    if (!tahsilFmt && data.kpi && typeof data.kpi.toplam_tahsilat !== 'undefined') {
      try {
        var tv = Number(data.kpi.toplam_tahsilat || 0);
        tahsilFmt = tv.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' TL';
      } catch (e) {
        // ignore
      }
    }
    $('#ydKpiTahsilEdilen').text(tahsilFmt);

    function setOrReplaceQueryParam(url, key, value) {
      try {
        var u = new URL(url, window.location.origin);
        u.searchParams.set(key, value);
        return u.pathname + '?' + u.searchParams.toString();
      } catch (e) {
        // fallback: basit replace
        var out = String(url || '');
        if (!out) return out;
        var re = new RegExp('([?&])' + key + '=([^&#]*)', 'i');
        if (re.test(out)) return out.replace(re, '$1' + key + '=' + encodeURIComponent(value));
        return out + (out.indexOf('?') === -1 ? '?' : '&') + key + '=' + encodeURIComponent(value);
      }
    }

    // Header aksiyonları (mesaj/whatsapp/yazdır/pdf/excel) seçili kişiye göre güncelle
    try {
      // Mesaj gönder butonu: numeric kisi_id kullanıyor
      var kisiIdNum = (data && data.person && typeof data.person.kisi_id !== 'undefined') ? String(data.person.kisi_id) : '';
      var $msg = $('.mesaj-gonder').first();
      if ($msg.length) {
        if (kisiIdNum) $msg.attr('data-id', kisiIdNum);
        if (kisiEnc) $msg.attr('data-kisi-id', kisiEnc);
        $msg.attr('data-phone', String(telefonTemiz || ''));
        $msg.attr('data-daire', String((data.person && data.person.daire_kodu) ? data.person.daire_kodu : ''));
      }

      // WhatsApp link: data-id numeric, data-kisi-id enc
      var $waBtn = $('#ydWhatsappLink');
      if ($waBtn.length) {
        if (kisiIdNum) $waBtn.attr('data-id', kisiIdNum);
        if (kisiEnc) $waBtn.attr('data-kisi-id', kisiEnc);
      }

      // Export linkleri: kisi_id numeric query ile çalışıyor
      // (print html / pdf / xlsx)
      if (kisiIdNum) {
        var $print = $('#ydPrintHtml');
        if ($print.length) {
          var printHref = String($print.attr('href') || '/pages/dues/payment/export/kisi_borc_tahsilat.php?format=html');
          printHref = setOrReplaceQueryParam(printHref, 'kisi_id', kisiIdNum);
          printHref = setOrReplaceQueryParam(printHref, 'format', 'html');
          $print.attr('href', printHref);
        }

        var $downloads = $('a.file-download');
        if ($downloads.length) {
          $downloads.each(function () {
            var $a = $(this);
            var href = String($a.attr('href') || '');
            if (!href) return;

            href = setOrReplaceQueryParam(href, 'kisi_id', kisiIdNum);

            // PDF linkinde format parametresi yoksa default pdf olduğu için gerek yok ama
            // bazı durumlarda click handler/route format ister: güvenli olsun diye ekle.
            var isXlsx = href.indexOf('format=xlsx') !== -1;
            var hasFormat = href.indexOf('format=') !== -1;
            if (!hasFormat && !isXlsx) {
              href = setOrReplaceQueryParam(href, 'format', 'pdf');
            }

            $a.attr('href', href);
          });
        }
      }
    } catch (e10) {
      // ignore
    }
  }

  function updateLeftListAfterRefresh(personEnc, data) {
    // Bu fonksiyonun görevi: sol listede ilgili satırı, zaten elimizde olan
    // dashboard verisine göre güncellemek. Buradan tekrar loadPerson çağırmak
    // hem gereksiz istek hem de döngü/çakışma yaratabiliyor.
    if (!data || !data.kpi || !data.person) return;

    // Bazı çağrılar yanlışlıkla numeric kisi_id gönderebiliyor.
    // Sol liste ise data-yd-kisi içinde encrypted kisi_enc tutuyor.
    var encFromData = (data.person && data.person.kisi_enc) ? String(data.person.kisi_enc) : '';
    var normalizedEnc = encFromData || (personEnc == null ? '' : String(personEnc));
    if (!normalizedEnc) return;

    // 1) Önce state'ten git (en güvenlisi)
    var $list = $('#ydPersonnelList');
    var $row = null;
    try {
      if (ydSelectedLeftRow && ydSelectedLeftRow.length) {
        var rowEnc = String(ydSelectedLeftRow.data('yd-kisi') || '');
        if (rowEnc === normalizedEnc) $row = ydSelectedLeftRow;
      }
    } catch (eState) {
      $row = null;
    }

    // Selector güvenliği (enc içinde : . [ ] gibi karakterler olabiliyor)
    var selEnc;
    try {
      if (window.CSS && typeof window.CSS.escape === 'function') selEnc = window.CSS.escape(normalizedEnc);
      else selEnc = normalizedEnc.replace(/\\/g, '\\\\').replace(/\"/g, '\\"');
    } catch (eSel) {
      selEnc = normalizedEnc;
    }

    // 2) State yoksa/uyuşmadıysa selector ile bul
    if (!$row || !$row.length) {
      $row = $list.find('a.yd-item[data-yd-kisi="' + selEnc + '"]');
    }

    // Fallback: data() üzerinden tek tek kontrol et
    if (!$row.length) {
      $row = $list.find('a.yd-item[data-yd-kisi]').filter(function () {
        return String($(this).data('yd-kisi') || '') === normalizedEnc;
      }).first();
    }

    // Son çare: aktif seçili satırı güncelle (sağ panelde işlem yaparken bu kişi seçili oluyor)
    if (!$row.length) {
      $row = $list.find('a.yd-item.is-active[data-yd-kisi]').first();
      // aktif satır varsa ve farklı bir kişiye aitse state'i yine de buna çekmeyelim.
      // (Burada amaç: hiç satır bulunamıyorsa en azından ekranda seçili görünen satırı güncellemek.)
    }

    if (!$row.length) return;

    // Bulduysak state'i de güncelleyelim
    try {
      ydSelectedKisiEnc = normalizedEnc;
      ydSelectedLeftRow = $row;
    } catch (eState2) {
      // ignore
    }


    // Kalan borç (listede sağdaki tutar)
    var kalanFmt = (data.kpi && data.kpi.kalan_borc_fmt) ? String(data.kpi.kalan_borc_fmt) : '';
    var $amount = $row.find('.yd-amount');
    if ($amount.length && kalanFmt) $amount.text(kalanFmt);

    // Status chip (Borçlu / Borcu Yok)
    var status = (data.person && data.person.status) ? String(data.person.status) : '';
    var $badge = $row.find('.yd-chip').first();
    if ($badge.length && status) {
      $badge.text(status);
      $badge.toggleClass('yd-chip-danger', status === 'Borçlu');
      $badge.toggleClass('yd-chip-success', status !== 'Borçlu');
    }

    // Filtrelerin doğru çalışması için net değeri de güncelle.
    // Not: list-new.php net = kredi - kalan; bizim KPI'da kalan_borc var.
    // Kredi bilgisi dashboard response'da varsa onu kullan, yoksa net'i sadece -kalan olarak set et.
    try {
      var kalanNum = Number(data.kpi.kalan_borc || 0);
      kalanNum = isFinite(kalanNum) ? kalanNum : 0;
      var krediNum = Number((data.person && (data.person.kredi_tutari || data.person.kredi)) || 0);
      krediNum = isFinite(krediNum) ? krediNum : 0;
      var net = krediNum - kalanNum;
      $row.attr('data-yd-net', String(net));
      $row.data('yd-net', net);
    } catch (e) {
      // ignore
    }
  }

  // global'e export (başka legacy kodlar çağırıyor olabilir)
  window.updateLeftListAfterRefresh = updateLeftListAfterRefresh;

  function loadPerson(enc) {
    $('#ydDebtsTbody').html('<tr><td class="px-4" colspan="7"><span class="yd-muted">Yükleniyor...</span></td></tr>');
    $('#ydTahsilatTbody').html('<tr><td class="px-4" colspan="5"><span class="yd-muted">Yükleniyor...</span></td></tr>');

    return $.ajax({
      url: urls.dashboardApi,
      method: 'GET',
      dataType: 'json',
      data: { kisi: enc },
      xhrFields: { withCredentials: true }
    }).then(function (json) {
      if (!json || !json.success) throw new Error((json && json.message) || 'Veri alınamadı');
      ydLastPersonData = json.data;
      // diğer modüller güncelleme sonrası KPI/sol liste refresh için kullanabilsin
      try { window.ydLastPersonData = ydLastPersonData; } catch (e0) { /* ignore */ }
      setHeader(json.data);
      renderDebts(json.data.borclandirma_detaylari);
      renderTahsilatlar(json.data.tahsilatlar_type, json.data.tahsilatlar);
      filterDebtRows();
      return json.data;
    });
  }

  window.loadPerson = loadPerson;

  // ---- Borç/Tahsilat aksiyonları ----
  function ensureModal(id) {
    var $el = $('#' + id);
    if ($el.length) return $el;
    var html =
      '<div class="modal fade" id="' + id + '" tabindex="-1" role="dialog">' +
      '  <div class="modal-dialog modal-xl" role="document">' +
      '    <div class="modal-content ' + (id === 'borcEkle' ? 'borc-ekle-modal' : '') + '"></div>' +
      '  </div>' +
      '</div>';
    $('body').append(html);
    return $('#' + id);
  }

  function openEditDebtModal(borcDetayEnc) {
    var kisiEnc = getKisiFromUrl();
    if (!kisiEnc) kisiEnc = getSelectedKisiEncFallback();
    if (!kisiEnc) return $.Deferred().reject(new Error('kisi param yok')).promise();

    var $modal = ensureModal('borcEkle');
    $modal.find('.modal-content').html('<div class="p-4">Yükleniyor...</div>');

    var reqData = { kisi_id: kisiEnc };
    // yeni borç: borc_detay_id göndermeyelim
    if (borcDetayEnc) reqData.borc_detay_id = borcDetayEnc;

    return $.ajax({
      url: urls.borcModal,
      method: 'GET',
      dataType: 'html',
      data: reqData
    }).then(function (html) {
      $modal.find('.modal-content').html(html);

      // Modal içeriği ajax ile geldiği için select2 otomatik bağlanmıyor.
      // Dropdown'ın modal altında kalmaması için dropdownParent modal olur.
      initSelect2InScope($modal, $modal);

      try {
        bootstrap.Modal.getOrCreateInstance($modal[0]).show();
      } catch (e) {
        // Bootstrap global'i yoksa / çakıştıysa jQuery modal fallback
        try {
          if (typeof $modal.modal === 'function') $modal.modal('show');
        } catch (e2) {
          // ignore
        }
      }
    });
  }

  // Yeni borç ekleme (borc_detay_id olmadan)
  function openAddDebtModal() {
    return openEditDebtModal('');
  }

  function deleteDebt(borcDetayEnc) {
    // Swal varsa onu kullan, yoksa confirm() fallback
    function doRequest() {
      var fd = new FormData();
      fd.append('action', 'borc_sil');
      fd.append('id', borcDetayEnc);

      return fetch(urls.actionApi, { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.text(); })
        .then(function (txt) {
          var json;
          try { json = JSON.parse(txt); } catch (e0) { throw new Error('Sunucu yanıtı JSON değil: ' + txt); }
          if (!json || json.status !== 'success') throw new Error((json && json.message) ? json.message : 'Silme işlemi başarısız.');
          return json;
        });
    }

    if (window.Swal) {
      Swal.fire({
        title: 'Emin misiniz?',
        text: 'Bu işlem geri alınamaz!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Evet, Sil',
        cancelButtonText: 'İptal',
        confirmButtonColor: '#d33',
        showLoaderOnConfirm: true,
        allowOutsideClick: function () { return !Swal.isLoading(); },
        preConfirm: function () {
          return doRequest().catch(function (err) {
            // SweetAlert preConfirm içinde hata gösterimi
            var msg = (err && err.message) ? err.message : String(err);
            Swal.showValidationMessage(msg);
            throw err;
          });
        }
      }).then(function (r) {
        if (!r || !r.isConfirmed) return;

        // Silme başarılı -> kişi ekranını yenile
        var kisiEnc = getKisiFromUrl();
        if (!kisiEnc) kisiEnc = getSelectedKisiEncFallback();
        if (!kisiEnc) {
          return Swal.fire({
            icon: 'success',
            title: 'Silindi',
            text: 'Borç başarıyla silindi.',
            timer: 1400,
            showConfirmButton: false
          });
        }

        return loadPerson(kisiEnc)
          .then(function () {
            try {
              if (ydLastPersonData) updateLeftListAfterRefresh(kisiEnc, ydLastPersonData);
            } catch (e1) { /* ignore */ }
          })
          .then(function () {
            return Swal.fire({
              icon: 'success',
              title: 'Silindi',
              text: 'Borç başarıyla silindi.',
              timer: 1400,
              showConfirmButton: false
            });
          })
          .catch(function (err2) {
            var msg2 = 'Borç silindi ama ekran yenilenemedi: ' + (err2 && err2.message ? err2.message : err2);
            return Swal.fire({ icon: 'warning', title: 'Silindi', text: msg2 });
          });
      });

      return;
    }

    if (!confirm('Bu borçlandırmayı silmek istediğinize emin misiniz?')) return;
    doRequest()
      .then(function () {
        var kisiEnc = getKisiFromUrl();
        if (!kisiEnc) kisiEnc = getSelectedKisiEncFallback();
        if (kisiEnc) loadPerson(kisiEnc);
      })
      .catch(function () { alert('Borç silinirken hata oluştu.'); });
  }

  function openTahsilatModalForSelected(kisiEnc, selectedDebtEncIds) {
    if (!kisiEnc || !selectedDebtEncIds || !selectedDebtEncIds.length) return;

    // legacy akış uyumu
    window.secilenBorcIdleri = selectedDebtEncIds.slice();

    $.get(urls.tahsilatModal, { kisi_id: kisiEnc, borc_idler: selectedDebtEncIds.join(',') }, function (data) {
      $('.tahsilat-modal-body').html(data);
      var modalEl = document.getElementById('ydTahsilatModal');
      if (!modalEl) return;
      try {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
      } catch (e) {
        // ignore
      }

      // select2 varsa init
      initSelect2InScope(modalEl, modalEl);


      //Flatpickr varsa init
      $(".flatpickr").flatpickr({
        dateFormat: "d.m.Y H:i",
        locale: "tr",
        enableTime: true,
        time_24hr: true,
        minuteIncrement: 1
      });


      //initFlatpickrInScope(modalEl, modalEl);
    });
  }

  // Legacy tahsilat modalı için kaydet
  function bindLegacyTahsilatKaydet() {
    $(document).on('click', '#tahsilatKaydet', function () {
      var $form = $('#tahsilatForm');
      if (!$form.length) return;

      // modalda açıklama input'u name'e sahip değil; API bekliyor
      var aciklama = String($('#ydTahsilatAciklama').val() || '');

      // select2'de seçili text'i ekle (API bunu kullanıyor olabilir)
      var tahsilatTuru = '';
      try {
        tahsilatTuru = String($('#tahsilat_turu option:selected').text() || '');
      } catch (e) {
        tahsilatTuru = '';
      }

      // FormData + ekstra alanlar
      var formData = new FormData($form[0]);
      formData.append('action', 'tahsilat-kaydet');
      formData.append('tahsilat_turu', tahsilatTuru);
      formData.append('tahsilat_aciklama', aciklama);

      // legacy: seçilen borçlar global'de tutuluyor
      try {
        if (typeof window.secilenBorcIdleri !== 'undefined') {
          // array ise joinle, string ise olduğu gibi
          var v = window.secilenBorcIdleri;
          if ($.isArray(v)) v = v.join(',');
          formData.append('borc_detay_ids', v);
        }
      } catch (e2) {
        // ignore
      }

      // Basit doğrulama (validate plugin yoksa)
      var tutar = String($('#tutar').val() || '').trim();
      var tarih = String($('#islem_tarihi').val() || '').trim();
      var kasa = String($('#kasa_id').val() || '').trim();
      if (!tutar) return alert('Tutar zorunludur.');
      if (!tarih) return alert('İşlem tarihi zorunludur.');
      if (!kasa) return alert('Kasa seçimi zorunludur.');

      try {
        if (typeof setButtonLoading === 'function') setButtonLoading('#tahsilatKaydet', true, 'Kaydediliyor...');
        if (window.Pace && typeof Pace.restart === 'function') Pace.restart();
      } catch (e3) {
        // ignore
      }

      fetch(urls.actionApi, { method: 'POST', body: formData, credentials: 'same-origin' })
        .then(function (r) {
          
          return r.text().then(function (txt) {
            try {
              return JSON.parse(txt);
            } catch (e4) {
              throw new Error('Sunucu yanıtı JSON değil: ' + txt);
            }
          });
        })
        .then(function (data) {
          try {
            if (typeof setButtonLoading === 'function') setButtonLoading('#tahsilatKaydet', false);
          } catch (e5) {
            // ignore
          }

          var ok = data && (data.status === 'success' || data.status === true);
          if (!ok) {
            var msg = (data && data.message) ? data.message : 'Tahsilat kaydedilemedi.';
            if (window.Swal) Swal.fire({ icon: 'error', title: 'Hata', text: msg });
            else alert(msg);
            return;
          }

          // modalı kapat
          try {
            var m = document.getElementById('ydTahsilatModal');
            if (m) bootstrap.Modal.getOrCreateInstance(m).hide();
          } catch (e6) {
            // ignore
          }

          try {
            if (window.Toastify) {
              Toastify({
                text: 'Tahsilat kaydedildi ve borçlara dağıtıldı.',
                duration: 3000,
                close: true,
                gravity: 'top',
                position: 'center',
                style: { background: 'linear-gradient(to right, #199b5aff, #199b5aff)', borderRadius: '6px' }
              }).showToast();
            }
          } catch (e7) {
            // ignore
          }

          // sağ paneli refresh + sol listeyi güncelle
          var kisiEnc = String($('#tahsilatForm input[name="kisi_id"]').val() || getKisiFromUrl() || '');
          if (kisiEnc) {
            loadPerson(kisiEnc).then(function () {
              try {
                if (ydLastPersonData) updateLeftListAfterRefresh(kisiEnc, ydLastPersonData);
              } catch (e8) {
                // ignore
              }
              $('.yd-debt-check:checked').prop('checked', false).trigger('change');
            }).catch(function () {
              // ignore
            });
          }
        })
        .catch(function (err) {
          try {
            if (typeof setButtonLoading === 'function') setButtonLoading('#tahsilatKaydet', false);
          } catch (e9) {
            // ignore
          }
          var msg = (err && err.message) ? err.message : String(err);
          if (window.Swal) Swal.fire({ icon: 'error', title: 'Hata', text: msg });
          else alert(msg);
        });
    });
  }

  // Tahsilat modalı: kredi kullanımı değişince ödenecek/tutar güncelle
  function bindTahsilatKrediChange() {
    var suppressTahsilatSync = false;

    function parseMoney(val) {
      if (val == null) return 0;
      var s = String(val).trim();
      if (!s) return 0;
      // TR format: 1.234,56 -> 1234.56
      s = s.replace(/\./g, '').replace(/,/g, '.');
      // currency/space temizle
      s = s.replace(/[^0-9.\-]/g, '');
      var n = Number(s);
      return Number.isFinite(n) ? n : 0;
    }

    function setMoneyInput($el, amount, triggerEvents) {
      if (!$el || !$el.length) return;
      var n = Math.max(0, Number(amount) || 0);
      // basit TR gösterim
      var txt = n.toFixed(2).replace('.', ',');
      $el.val(txt);
      // money plugin varsa tetikle
      if (triggerEvents !== false) {
        $el.trigger('input').trigger('change');
      }
    }

    function readSelectedTotal() {
      // modal header'da toplam seçilen: #ydTahsilatToplam (örn: 1.234,00 ₺)
      var t = parseMoney($('#ydTahsilatToplam').text());
      if (t > 0) return t;
      // fallback: tutar input ilk değerini toplam kabul et
      return parseMoney($('#tutar').val());
    }

    function readAvailableCredit() {
      // modal header'da kullanılabilir kredi: #ydTahsilatKredi
      return parseMoney($('#ydTahsilatKredi').text());
    }

    function updateTahsilatAmounts() {
      // modal yoksa çık
      if (!$('#tahsilatForm').length) return;

      if (suppressTahsilatSync) return;

      var toplam = readSelectedTotal();
      var kredi = parseMoney($('#kullanilacak_kredi').val());
      if (kredi < 0) kredi = 0;
      if (kredi > toplam) kredi = toplam;

      var net = Math.max(0, toplam - kredi);
  var kalan = Math.max(0, toplam - net - kredi);
      // istenen: kredi değişince tahsil edilecek tutar değişsin
  suppressTahsilatSync = true;
  setMoneyInput($('#tutar'), net, false);
  suppressTahsilatSync = false;

      // sağ üst mini özet label'larını güncelle
      try {
        $('#ydTahsilatKrediKullan').text(kredi.toFixed(2).replace('.', ','));
        $('#ydTahsilatNet').text(net.toFixed(2).replace('.', ','));
        $('#ydTahsilatKalan').text(kalan.toFixed(2).replace('.', ','));
      } catch (e) {
        // ignore
      }
    }

    function updateTahsilatSummaryFromTutar() {
      // modal yoksa çık
      if (!$('#tahsilatForm').length) return;

      if (suppressTahsilatSync) return;

      var toplam = readSelectedTotal();
      var net = parseMoney($('#tutar').val());
      if (net < 0) net = 0;
      if (net > toplam) net = toplam;

  // Kullanıcı tutarı elle giriyorsa, kredi alanını otomatik değiştirmeyelim.
  // Kredi yalnızca kullanıcı tarafından girilsin ya da 'Hepsini Kullan' ile set edilsin.
  var kredi = parseMoney($('#kullanilacak_kredi').val());
  if (kredi < 0) kredi = 0;
  if (kredi > toplam) kredi = toplam;

  // Borçtan kalan = toplam - (tahsil edilen net + kullanılan kredi)
  var kalan = Math.max(0, toplam - net - kredi);

      // Özet alanları güncelle
      try {
        $('#ydTahsilatKrediKullan').text(kredi.toFixed(2).replace('.', ','));
        $('#ydTahsilatNet').text(net.toFixed(2).replace('.', ','));
        $('#ydTahsilatKalan').text(kalan.toFixed(2).replace('.', ','));
      } catch (e) {
        // ignore
      }
    }

    // change + input: elle yazarken de anlık güncellensin
    $(document).on('input change', '#kullanilacak_kredi', function () {
      updateTahsilatAmounts();
    });

    // Kullanıcı doğrudan tahsilat tutarını değiştirirse özetleri güncelle
    $(document).on('input change', '#tutar', function () {
      updateTahsilatSummaryFromTutar();
    });

    // Hepsini kullan butonu varsa
    $(document).on('click', '#ydKrediHepsiniKullan', function () {
      var toplam = readSelectedTotal();
      var avail = readAvailableCredit();
      var use = Math.min(Math.max(0, avail), Math.max(0, toplam));
      setMoneyInput($('#kullanilacak_kredi'), use);
      updateTahsilatAmounts();
    });

    // modal ilk açıldığında da bir kez hesapla
    $(document).on('shown.bs.modal', '#ydTahsilatModal', function () {
      // İlk açılışta kullanıcıya kredi dayatmayalım; mevcut input değerlerine göre özet bas.
      updateTahsilatSummaryFromTutar();
    });
  }

  // ---- Event wiring ----
  $(function () {
    // // URL ile direkt gelen sayfalarda (örn: ?kisi=...&yd_q=...)
    // // bazı template'lerde sidebar "open" state'i önceki sayfadan kalmış gibi görünebiliyor.
    // // Bu sayfada, yükleme anında tüm açık submenu'ları kapatıp sadece server-side aktif işareti
    // // (li.active / li.nxl-trigger) üzerinden gerekli olanları açık bırakıyoruz.
    function normalizeSidebarOpenState() {
      try {
        var u = safeUrl();
        if (!u) return;

        // Sadece URL parametreli direct-entry senaryosunda agresif davran.
        var hasParams = false;
        try { hasParams = u.search && String(u.search).length > 1; } catch (e0) { hasParams = false; }
        if (!hasParams) return;

        var $side = $('#side-menu');
        if (!$side.length) return;

        // 1) Önce tüm trigger/active state'i sıfırla
        $side.find('li.nxl-item.nxl-trigger').removeClass('nxl-trigger');
        $side.find('ul.nxl-submenu').hide();

        // 2) Ardından server'ın işaretlediği active path'i tekrar aç
        // Not: left-sidebar.php aktif path için li.active set ediyor.
        var $activeItems = $side.find('li.nxl-item.active');
        $activeItems.each(function () {
          var $li = $(this);
          // Parent chain'i aç
          $li.parents('li.nxl-item').addClass('nxl-trigger');
          $li.parents('ul.nxl-submenu').show();
          // Kendi submenu'su varsa onu da aç
          $li.children('ul.nxl-submenu').show();
        });
      } catch (e) {
        // ignore
      }
    }

    // DOM yüklendiğinde ve kısa bir gecikmeyle tekrar uygula (CSS/templating geç oturabiliyor)
    normalizeSidebarOpenState();
    setTimeout(normalizeSidebarOpenState, 50);

    var $list = $('#ydPersonnelList');
    if (!$list.length) return;

    // Mesaj Gönder (SMS) - list.php'deki legacy akışla uyumlu
    $(document).on('click', '.mesaj-gonder', function (e) {
      try { e.preventDefault(); e.stopPropagation(); } catch (e0) {}

      var $btn = $(this);
      var id = $btn.data('id');
      var kisiId = $btn.data('kisi-id');
      var phone = ($btn.data('phone') || '').toString();
      var daire = ($btn.data('daire') || $btn.data('daire-kodu') || '').toString();
      var makbuz_bildirim = $btn.data('makbuz-bildirim') || 'false';

      $.get('/pages/email-sms/sms_gonder_modal.php', {
        id: id,
        kisi_id: kisiId,
        includeFile: 'kisiye-mesaj-gonder.php',
        makbuz_bildirim: makbuz_bildirim
      }, function (data) {
        try {
          $('.modal-content.sms-gonder-modal').html(data);
        } catch (e1) {
          $('.sms-gonder-modal').html(data);
        }

        try {
          $('#SendMessage').modal('show');
        } catch (e2) {
          try {
            var el = document.getElementById('SendMessage');
            if (el && window.bootstrap && bootstrap.Modal) bootstrap.Modal.getOrCreateInstance(el).show();
          } catch (e3) {}
        }

        // Modal içeriği basıldıktan sonra init
        setTimeout(function () {
          if (phone) {
            try { window.kisiTelefonNumarasi = ''; } catch (e4) {}
          }
          if (typeof window.initSmsModal === 'function') {
            window.initSmsModal();
          }
          if (typeof window.addPhoneToSMS === 'function' && phone) {
            window.addPhoneToSMS({ phone: phone, id: id, daire: daire });
          }
        }, 100);
      });
    });

    function scrollLeftListToSelected(opts) {
      opts = opts || {};
      var $container = $list; // #ydPersonnelList scroll container
      if (!$container.length) return;

      // Seçili elemanı bul: önce class, yoksa URL'deki kisi paramına göre
      var $target = $container.find('a.yd-item.is-active:visible').first();
      if (!$target.length) {
        var enc = getKisiFromUrl();
        if (enc) $target = $container.find('a.yd-item[data-yd-kisi="' + String(enc).replace(/"/g, '\\"') + '"]:visible').first();
      }
      if (!$target.length) return;

      // container scrollTop'u hedef elemanın görünür olacağı şekilde ayarla
      try {
        var contEl = $container[0];
        var itemEl = $target[0];
        if (!contEl || !itemEl) return;

        // offsetTop parent'a göre hesaplanır; burada item'lar container içinde olduğu için yeterli
        var itemTop = itemEl.offsetTop;
        var itemHeight = itemEl.offsetHeight || $target.outerHeight() || 0;
        var contTop = contEl.scrollTop;
        var contHeight = contEl.clientHeight || $container.height() || 0;

        var itemBottom = itemTop + itemHeight;
        var contBottom = contTop + contHeight;

        var pad = Number(opts.padding || 12);
        if (!isFinite(pad)) pad = 12;

        var shouldScroll = (itemTop - pad) < contTop || (itemBottom + pad) > contBottom;
        if (!shouldScroll) return;

        var newTop;
        if (opts.center) {
          newTop = itemTop - Math.max(0, (contHeight - itemHeight) / 2);
        } else {
          // üstte görünsün
          newTop = itemTop - pad;
        }
        newTop = Math.max(0, newTop);

        contEl.scrollTop = newTop - 10;
      } catch (e) {
        // ignore
      }
    }

    initLeftSearchAndFilter();

    // Kişi seçimi
    $list.on('click', 'a.yd-item', function (e) {
    //  e.preventDefault();
      var $a = $(this);
      var enc = String($a.data('yd-kisi') || '');
      if (!enc) return;

  // Seçili satırı state olarak sakla (sol listeyi güncellemek için)
  ydSelectedKisiEnc = enc;
  ydSelectedLeftRow = $a;

      setActiveLeftItem($list, $a);

      // seçileni her zaman görünür tut
      scrollLeftListToSelected({ center: true });

      var u = safeUrl();
      if (u) {
        u.searchParams.set('kisi', enc);
        try { history.replaceState({}, '', u.toString()); } catch (e2) { /* ignore */ }
      }

      loadPerson(enc).catch(function () {
        alert('Kişi bilgileri alınırken hata oluştu.');
      });
    });

    // Tabs
    $(document).on('click', '.yd-tab-btn', function () {
      setTab($(this).data('yd-tab'));
    });

    // borç filtre toggle
    $(document).on('change', '#ydOnlyDebtsToggle', filterDebtRows);

    // checkbox change
    $(document).on('change', '.yd-debt-check', function () {
      var $tr = $(this).closest('tr.yd-debt-row');
      syncDebtActiveRow($tr);
      $('#ydCollectSelectedDebts').prop('disabled', $('.yd-debt-check:checked').length === 0);
    });

    // satıra tıklayınca checkbox toggle
    $(document).on('click', '#ydDebtsTbody tr.yd-debt-row', function (ev) {
      if ($(ev.target).closest('a,button,input,.hstack').length) return;
      var $tr = $(this);
      if (String($tr.attr('data-yd-can-select') || '0') !== '1') return;
      var $chk = $tr.find('input.yd-debt-check');
      if (!$chk.length) return;
      $chk.prop('checked', !$chk.prop('checked')).trigger('change');
    });

    // tümünü seç
    $(document).on('click', '#ydSelectAllDebts', function () {
      var $checks = $('.yd-debt-check');
      var anyUnchecked = $checks.toArray().some(function (c) { return !c.checked; });
      $checks.each(function () { this.checked = anyUnchecked; });
      $checks.trigger('change');
    });

    // seçilenleri tahsil et
    $(document).on('click', '#ydCollectSelectedDebts', function () {
      var kisiEnc = getKisiFromUrl();
      if (!kisiEnc) kisiEnc = getSelectedKisiEncFallback();
      var ids = $('.yd-debt-check:checked').map(function () { return $(this).data('yd-borc-id'); }).get().filter(Boolean);
      openTahsilatModalForSelected(kisiEnc, ids);
    });

    // borç edit/sil
    $(document).on('click', '.yd-borc-edit', function (e) {
      // Anchor click'i sayfayı başka bir yere yönlendirmesin.
      // Özellikle bazı global handler'lar (#, file-download vb.) bu click'i etkileyebiliyor.
      // if (e && typeof e.preventDefault === 'function') e.preventDefault();
      // if (e && typeof e.stopPropagation === 'function') e.stopPropagation();

      openEditDebtModal($(this).data('id')).catch(function () {
        // fallback: modal içeriği geldi ama init kaçtıysa
        initSelect2InScope(document, document.body);
      });
    });
    $(document).on('click', '.yd-borc-delete', function (e) {
      e.preventDefault();
      deleteDebt($(this).data('id'));
    });

    // borç ekle (başlıktaki + ikon)
    $(document).on('click', '.yd-borc-add', function (e) {
      if (e && typeof e.preventDefault === 'function') e.preventDefault();
      openAddDebtModal().catch(function () {
        // modal açıldıktan sonra select2 gibi initler openEditDebtModal içinde yapılıyor
      });
    });

    // tahsilat detay toggle
    $(document).on('click', '.tahsilat-detay-goster', function (ev) {
      ev.preventDefault();
      ev.stopPropagation();
      var $row = $(this).closest('tr.yd-tahsilat-row');
      var key = String($row.data('detail-key') || '');
      if (!key) return;
      $('#ydTahsilatTbody tr.yd-tahsilat-detail-row').each(function () {
        var $tr = $(this);
        if (String($tr.data('detail-key') || '') !== key) $tr.hide();
      });
      $('#ydTahsilatTbody tr.yd-tahsilat-detail-row[data-detail-key="' + key.replace(/"/g, '\\"') + '"]').toggle();
    });

    // tahsilat sil
    $(document).on('click', '#delete-tahsilat', function (ev) {
      ev.preventDefault();
      ev.stopPropagation();
      var idEnc = String($(this).data('id') || '');
      if (!idEnc) return alert('Tahsilat ID bulunamadı.');

      function doDelete() {
        var kisiEnc = getKisiFromUrl();
        var fd = new FormData();
        fd.append('action', 'tahsilat-sil');
        fd.append('id', idEnc);

        fetch(urls.actionApi, { method: 'POST', body: fd, credentials: 'same-origin' })
          .then(function (r) { return r.text(); })
          .then(function (txt) {
            var json;
            try { json = JSON.parse(txt); } catch (e2) { throw new Error('Sunucu yanıtı JSON değil: ' + txt); }
            if (!json || json.status !== 'success') throw new Error((json && json.message) ? json.message : 'Silme işlemi başarısız');

            if (window.Swal) {
              return Swal.fire({
                icon: 'success',
                title: 'Silindi',
                text: 'Tahsilat kaydı silindi.',
                timer: 1500,
                showConfirmButton: false
              }).then(function () { return json; });
            }
            return json;
          })
          .then(function () {
            if (!kisiEnc) return;
            return loadPerson(kisiEnc).then(function () {
              if (ydLastPersonData) updateLeftListAfterRefresh(kisiEnc, ydLastPersonData);
            });
          })
          .catch(function (err) {
            var msg = 'Tahsilat silinirken hata oluştu: ' + (err && err.message ? err.message : err);
            if (window.Swal) Swal.fire({ icon: 'error', title: 'Hata', text: msg });
            else alert(msg);
          });
      }

      // Swal varsa onu kullan, yoksa confirm() fallback
      if (window.Swal) {
        Swal.fire({
          icon: 'warning',
          title: 'Emin misiniz?',
          text: 'Bu tahsilat kaydı silinsin mi?',
          showCancelButton: true,
          confirmButtonText: 'Evet, sil',
          cancelButtonText: 'Vazgeç',
          confirmButtonColor: '#d33'
        }).then(function (res) {
          if (res && res.isConfirmed) doDelete();
        });
        return;
      }

      if (!confirm('Bu tahsilat kaydı silinsin mi?')) return;
      doDelete();
    });

    // ilk yükleme
    var $active = $list.find('a.yd-item.is-active').first();
    if ($active.length) {
      try {
        ydSelectedLeftRow = $active;
        ydSelectedKisiEnc = String($active.data('yd-kisi') || '');
      } catch (eSeed) {
        // ignore
      }
      loadPerson(String($active.data('yd-kisi') || '')).catch(function () { /* ignore */ });
    }

    // Sayfa yenilenince: seçili kişinin satırına otomatik scroll
    // DOM hazır olduğunda bir kez, ardından kısa gecikmeyle tekrar (bazı template/stil yüklemelerinde ölçüler geç oturuyor)
    scrollLeftListToSelected({ center: true });
    setTimeout(function () { scrollLeftListToSelected({ center: true }); }, 150);

    // legacy modal kaydet event'i
    bindLegacyTahsilatKaydet();

    // tahsilat modalı kredi -> tutar senkronu
    bindTahsilatKrediChange();

    // Global init (common-init) .file-download click'inde preventDefault yapıyor.
    // Bu sayfada PDF/XLSX indirmeyi engellememesi için capture-phase ile override.
    // try {
    //   document.addEventListener('click', function (ev) {
    //     var a = ev.target && ev.target.closest ? ev.target.closest('a.file-download') : null;
    //     if (!a) return;
    //     var href = a.getAttribute('href');
    //     if (!href || href === '#') return;

    //     // Önce diğer handler'ların iptal etmesini engelle
    //     ev.stopImmediatePropagation();
    //     // Bazı durumlarda preventDefault yapılmış olsa bile biz yönlendirelim
    //     ev.preventDefault();

    //     // Dosya indirme için aynı sekmede git
    //     window.location.href = href;
    //   }, true);
    // } catch (e10) {
    //   // ignore
    // }
  });
})(window.jQuery);