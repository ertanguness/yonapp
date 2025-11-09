import { lazyLoad } from './utils/lazyLoad.js';

const page = document.body.dataset.page;
//console.log("Aktif Sayfa:", page);

/* ✅ DataTables gereken tüm sayfalar */
const dataTablesPages = [
    "dues/dues-defines/list","offers/list","reports/list","kullanici/list",
    "kullanici-gruplari","bakim-ariza-takip","periyodik-bakim","maliyet-faturalandirma",
    "missions/list","missions/process/list","missions/headers/manage","missions/headers/list",
    "uye/list","dues/debit/detail","dues/payment/list","dues/debit/list","daire-turu-listesi",
    "dues/payment/tahsilat-onay","finans-yonetimi/kasa/list","siteler","dues/collections/list",
    "dues/collections/detail","aidat-turu-listesi","yonetici-aidat-odeme","tahsilatlar",
    "borclandirma","eslesen-odemeler","eslesmeyen-odemeler","kasa-hareketleri","kasa-listesi",
    "gelir-gider-islemleri","borclandirma-detayi","tahsilat-detayi","daire-ekle","daire-duzenle",
    "site-bloklari","site-daireleri","site-sakinleri","site-sakini-ekle","site-sakini-duzenle",
    "kullanici-listesi","onay-bekleyen-tahsilatlar","icralarim","icra-detay","icra-listesi",
    "icra-takibi","ziyaretci-listesi","ziyaretci-ekle","ziyaretci-duzenle","guvenlik",
    "guvenlik-gorev-yerleri","vardiya-listesi","personel-listesi","bildirimler",
    "banka-hesap-hareketleri","daire-tipi-listesi","gelir-gider-tipi-listesi",
    "gelir-gider-tipi-ekle","gelir-gider-tipi-duzenle","aidat-turu-tanimlama",
    "aidat-turu-duzenle","borclandirma-yap","personel-ekle","personel-duzenle"
];

/* ✅ Sayfa bazlı script tanımları */
const scripts = {

    "aidat-turu-tanimlama": ["/pages/dues/dues-defines/dues.js"],
    "aidat-turu-listesi":   ["/pages/dues/dues-defines/dues.js"],
    "aidat-turu-duzenle":   ["/pages/dues/dues-defines/dues.js"],

    "borclandirma":         [{ file: "/pages/dues/debit/js/debit.js", module: true }],
    "borclandirma-yap":     [{ file: "/pages/dues/debit/js/debit.js", module: true }],
    "borclandirma-duzenle": [{ file: "/pages/dues/debit/js/debit.js", module: true }],
    "borclandirma-detayi":  ["/pages/dues/debit/js/detail.js"],
    "borclandirma-kisi-ekle":[ "/pages/dues/debit/js/single-manage.js" ],
    "borclandirma-kisi-duzenle":[ "/pages/dues/debit/js/single-manage.js" ],

    "kullanici-listesi":[ "/pages/kullanici/js/user.js" ],
    "kullanici-ekle":[ "/pages/kullanici/js/user.js" ],
    "kullanici-duzenle":[ "/pages/kullanici/js/user.js" ],

    "kullanici-grubu-ekle":[ "/pages/kullanici-gruplari/js/duzenle.js" ],
    "kullanici-grubu-duzenle":[ "/pages/kullanici-gruplari/js/duzenle.js" ],
    "kullanici-gruplari":[ "/pages/kullanici-gruplari/js/duzenle.js" ],
    "yetki-yonetimi":[ "/pages/kullanici-gruplari/js/yetkiler.js" ],

    "daire-tipi-listesi":[ "/pages/defines/apartment-type/apartment-type.js" ],
    "daire-tipi-ekle":[ "/pages/defines/apartment-type/apartment-type.js" ],
    "daire-tipi-duzenle":[ "/pages/defines/apartment-type/apartment-type.js" ],

    "excelden-gelir-gider-yukle":[ "/pages/finans-yonetimi/gelir-gider/upload/upload-from-xls.js" ],
    "gelir-gider-tipi-listesi":[ "/pages/defines/gelir-gider-tipi/js/gelir-gider-tipi.js" ],
    "gelir-gider-tipi-ekle":[ "/pages/defines/gelir-gider-tipi/js/gelir-gider-tipi.js" ],
    "gelir-gider-tipi-duzenle":[ "/pages/defines/gelir-gider-tipi/js/gelir-gider-tipi.js" ],

    "tahsilat-detayi":[ "/pages/dues/collections/js/tahsilat.js" ],
    "onay-bekleyen-tahsilatlar":[ "/pages/dues/payment/js/tahsilat-onay.js" ],
    "yonetici-aidat-odeme":[
        "/pages/dues/payment/js/tahsilat-gir.js",
        "/pages/dues/payment/js/tahsilat-detay.js"
    ],

    "site-ekle":[ "/pages/management/sites/sites.js" ],
    "site-duzenle":[ "/pages/management/sites/sites.js" ],
    "siteler":[ "/pages/management/sites/sites.js" ],

    "blok-ekle":[ "/pages/management/blocks/blocks.js" ],
    "blok-duzenle":[ "/pages/management/blocks/blocks.js" ],
    "site-bloklari":[ "/pages/management/blocks/blocks.js" ],

    "daire-ekle":[ "/pages/management/apartment/apartment.js" ],
    "daire-duzenle":[ "/pages/management/apartment/apartment.js" ],
    "site-daireleri":[ "/pages/management/apartment/apartment.js" ],

    "site-sakinleri":[
        "/pages/management/peoples/js/kisiBilgileri.js",
        "/pages/management/peoples/js/aracBilgileri.js",
        "/pages/management/peoples/js/acilDurumKisiBilgileri.js"
    ],
    "site-sakini-ekle":[
        "/pages/management/peoples/js/kisiBilgileri.js",
        "/pages/management/peoples/js/aracBilgileri.js",
        "/pages/management/peoples/js/acilDurumKisiBilgileri.js"
    ],

    "guvenlik":[ "/pages/ziyaretci/guvenlik/guvenlik.js" ],
    "guvenlik-yeni-gorev-ekle":[ "/pages/ziyaretci/guvenlik/guvenlik.js" ],
    "guvenlik-gorev-duzenle":[ "/pages/ziyaretci/guvenlik/guvenlik.js" ],
    "guvenlik-gorev-yerleri":[ "/pages/ziyaretci/guvenlik/GorevYeri/gorevYeri.js" ],
    "vardiya-listesi":[ "/pages/ziyaretci/guvenlik/Vardiya/vardiya.js" ],
    "personel-listesi":[ "/pages/ziyaretci/guvenlik/Personel/guvenlikPersonel.js" ],
    "ziyaretci-listesi":[ "/pages/ziyaretci/ziyaretci.js" ],
    "ziyaretci-ekle":[ "/pages/ziyaretci/ziyaretci.js" ],
    "ziyaretci-duzenle":[ "/pages/ziyaretci/ziyaretci.js" ],

    "icra-ekle":[ "/pages/icra/icra.js" ],
    "icra-listesi":[ "/pages/icra/icra.js" ],
    "icra-detay":[ "/pages/icra/detay/detay.js" ],
    "icra-takibi":[ "/pages/icra/icra.js" ],

    "kasa-ekle":[ "/pages/finans-yonetimi/kasa/js/kasa.js" ],
    "kasa-duzenle":[ "/pages/finans-yonetimi/kasa/js/kasa.js" ],
    "kasa-listesi":[ "/pages/finans-yonetimi/kasa/js/kasa.js" ],

    "ana-sayfa":[
        "/assets/vendors/js/apexcharts.min.js",
        "/assets/vendors/js/daterangepicker.min.js",
        "/assets/vendors/js/circle-progress.min.js",
        "/assets/vendors/js/jquery.time-to.min.js",
        "/assets/js/dashboard-init.min.js"
    ],
};

/* ✅ YÜKLEME MOTORU */
async function loadScripts() {

    if (dataTablesPages.includes(page)) {
        await lazyLoad("/assets/vendors/js/dataTables.min.js");
        await lazyLoad("/assets/vendors/js/dataTables.bs5.min.js");
    }

    if (scripts[page]) {
        for (let sc of scripts[page]) {

            let file = (typeof sc === "string") ? sc : sc.file;
            let isModule = (typeof sc === "object" && sc.module === true);

            await lazyLoad(file, isModule);
        }
    }

    // Datatable varsa init
    if (dataTablesPages.includes(page) && $('.datatable').length) {
        setTimeout(() => {
            $('.datatable').DataTable();
            console.log("✅ DataTables initialized");
        }, 50);
    }
}

loadScripts();

