"use strict";
var tomailmodal = document.querySelector("input[name=tomailmodal]"),
	ccmailmodal = document.querySelector("input[name=ccmailmodal]"),
	bccmailmodal = document.querySelector("input[name=bccmailmodal]")


var tag_list = [];
window.mailTagify = { to: null, cc: null, bcc: null }; // 3 ayrı instance



$(function () {
    var site_id = $("#mySite option:selected").val();


    $.ajax({
        url: "/pages/home/api.php",
        type: "POST",
        dataType: "json",
        data: {
            action: 'get_tags',
            site_id: site_id
        },
        success: function (data) {
           var users = data.users || [];
            tag_list = users.map(function (user) {
                return {
                    value: user.value,
                    adi_soyadi: user.adi_soyadi,
                    eposta: user.eposta || '',
                    avatar: `https://ui-avatars.com/api/?name=${encodeURIComponent(user.adi_soyadi)}&background=random`,
                };
            });

          //  console.log(tag_list);

            initializeTagify(tomailmodal,'to');
            initializeTagify(ccmailmodal,'cc');
            initializeTagify(bccmailmodal,'bcc');
        }
    });


})



function initializeTagify(element,role) {
 if (!element) return;
  if (element.__tagify) { // zaten init edildiyse referansı ata
    window.mailTagify[role] = element.__tagify;
    return;
  }


    var t = new Tagify(element, {
        tagTextProp: "adi_soyadi",
        enforceWhitelist: false,
        skipInvalid: false,

        
        // Dropdown'u aktif tut
        dropdown: {
            closeOnSelect: false,
            enabled: 0, // 0: manuel açılır, 1: otomatik açılır
            classname: "users-list",
            searchKeys: ["adi_soyadi", "eposta"],
            maxItems: 20, // EKLE: Maksimum gösterilecek item
            highlightFirst: true // EKLE: İlk item'i vurgula
        },
        
        // Email validasyonu ekle
    //  pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, // Email formatı kontrolü
      
   // Manuel eklenen tag'leri düzenle
        transformTag: function(tagData) {
            if (!tagData.eposta) {
                // Manuel yazılan email
                tagData.eposta = tagData.adi_soyadi || tagData.value;
                tagData.adi_soyadi = tagData.eposta;
                tagData.value = tagData.eposta;
                tagData.avatar = `https://ui-avatars.com/api/?name=${encodeURIComponent(tagData.eposta.charAt(0))}&background=random`;
            }
        },
        templates: {
            tag: function (a) {
                return `
			<tag title="${a.eposta}"
					contenteditable='false'
					spellcheck='false'
					tabIndex="-1"
					class="tagify__tag ${a.class || ""}"
					${this.getAttributes(a)}>
				<x title='' class='tagify__tag__removeBtn' role='button' aria-label='remove tag'></x>
				<div>
					<div class='tagify__tag__avatar-wrap'>
						<img onerror="this.style.visibility='hidden'" src="${a.avatar}">
					</div>
					<span class='tagify__tag-text'>${a.adi_soyadi}</span>
				</div>
			</tag>
		`;
            },
            dropdownItem: function (a) {
                return `
			<div ${this.getAttributes(a)}
				class='tagify__dropdown__item ${a.class || ""}'
				tabindex="0"
				role="option">
				${a.avatar
                        ? `
				<div class='tagify__dropdown__item__avatar-wrap'>
					<img onerror="this.style.visibility='hidden'" src="${a.avatar}">
				</div>`
                        : ""
                    }
				<strong>${a.adi_soyadi}</strong>
				<span class="text-gray-500">${a.eposta || "No email"}</span>
			</div>
		`;
            },
            dropdownHeader: function (a) {
                return `
			<div class="${this.settings.classNames.dropdownItem} ${this.settings.classNames.dropdownItem
                    }__addAll">
				<strong>${this.value.length ? "Kalan " + (a.length) + " Kişiyi Ekle" : "Hepsini Ekle"}</strong>
				<span>${a.length} üye</span>
			</div>
		`;
            },
        },
        whitelist: tag_list,
    });
       // Dropdown seçim olayı
   // Dropdown seçim olayı
    t.on("dropdown:select", function (e) {
        if (e.detail.elm.classList.contains(t.settings.classNames.dropdownItem + "__addAll")) {
            t.dropdown.selectAll();
        }
    });

    // Input'a yazınca dropdown'u göster
    t.on('input', function(e) {
        if (e.detail.value.length > 0) {
            t.dropdown.show(e.detail.value);
        }
    });

    // Geçersiz tag eklenmeye çalışıldığında uyar
    t.on('invalid', function(e) {
        console.log('Geçersiz email formatı:', e.detail.data);
        
        // SweetAlert ile uyarı (opsiyonel)
        if (e.detail.message === 'pattern mismatch') {
            setTimeout(() => {
                Swal.fire({
                    icon: 'warning',
                    title: 'Geçersiz Format',
                    text: 'Lütfen geçerli bir email adresi girin (örn: kullanici@example.com)',
                    timer: 3000
                });
            }, 100);
        }
    });
    window.mailTagify[role] = t;
}

// Yardımcı: bir Tagify instance’ından e‑posta dizisi üret
window.pickEmails = function(tagifyInstance) {
  if (!tagifyInstance) return [];
  return tagifyInstance.value
    .map(x => (x.eposta || x.value || x.adi_soyadi || '').trim())
    .filter(Boolean);
};