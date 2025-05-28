<?php
// Örnek veriler
$block = $_GET['block'] ?? 'A';
$flat_no = $_GET['flat_no'] ?? 1;

// Aidat ve ceza bilgileri (veritabanından çekilecek)
$monthlyDebts = [
    [
        'month' => 'Ocak',
        'aidat' => 100,
        'ceza' => 20,
        'paid' => false,
        'paid_amount' => 50,
        'details' => [
            'Aidat' => 100,
            'Asansör Bakım' => 20,
            'Temizlik' => 10,
            'Güvenlik' => 15,
            'Ortak Elektrik' => 5,
        ]
    ],
    [
        'month' => 'Şubat',
        'aidat' => 100,
        'ceza' => 0,
        'paid' => true,
        'paid_amount' => 100,
        'details' => [
            'Aidat' => 100,
            'Asansör Bakım' => 0,
            'Temizlik' => 0,
            'Güvenlik' => 0,
            'Ortak Elektrik' => 0,
        ]
    ],
    [
        'month' => 'Mart',
        'aidat' => 100,
        'ceza' => 10,
        'paid' => false,
        'paid_amount' => 0,
        'details' => [
            'Aidat' => 100,
            'Asansör Bakım' => 25,
            'Temizlik' => 5,
            'Güvenlik' => 10,
            'Ortak Elektrik' => 10,
        ]
    ],
];
?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10"><?= $block ?> Blok - <?= $flat_no ?> Numaralı Daire Borç Detayı</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item"><a href="index?p=dues/payment/list">Borç Listesi</a></li>
            <li class="breadcrumb-item">Borç Detayı</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <a href="index?p=dues/payment/list" class="btn btn-outline-secondary">
                    <i class="feather-arrow-left me-2"></i>Listeye Dön
                </a>
                <button class="btn btn-success" id="pay-button">
                    <i class="feather-check-circle me-2"></i>Ödeme Yap
                </button>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="row row-deck row-cards">
        <div class="col-12">