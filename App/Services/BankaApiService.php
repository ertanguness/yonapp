<?php

namespace App\Services;

/**
 * Banka API Entegrasyon Servisi
 * 
 * Bu servis farklı banka API'leri ile entegrasyon sağlar
 * Desteklenen bankalar: Akbank, İş Bankası, Garanti BBVA, vb.
 */
class BankaApiService
{
    private $apiUrl;
    private $apiKey;
    private $apiSecret;
    private $bankCode;
    
    /**
     * Constructor
     * 
     * @param string $bankCode Banka kodu (akbank, isbank, garanti, vb.)
     * @param array $credentials API bilgileri
     */
    public function __construct($bankCode = 'demo', $credentials = [])
    {
        $this->bankCode = $bankCode;
        $this->apiUrl = $credentials['api_url'] ?? '';
        $this->apiKey = $credentials['api_key'] ?? '';
        $this->apiSecret = $credentials['api_secret'] ?? '';
    }
    
    /**
     * Banka hesap hareketlerini getirir
     * 
     * @param string $hesapNo Hesap numarası veya IBAN
     * @param string $baslangicTarihi YYYY-MM-DD formatında
     * @param string $bitisTarihi YYYY-MM-DD formatında
     * @return array Normalize edilmiş işlem listesi
     */
    public function getHesapHareketleri($hesapNo, $baslangicTarihi, $bitisTarihi)
    {
        // Gerçek API entegrasyonu için banka koduna göre ilgili metodu çağır
        switch ($this->bankCode) {
            case 'akbank':
                return $this->getAkbankHareketleri($hesapNo, $baslangicTarihi, $bitisTarihi);
            case 'isbank':
                return $this->getIsBankasiHareketleri($hesapNo, $baslangicTarihi, $bitisTarihi);
            case 'garanti':
                return $this->getGarantiHareketleri($hesapNo, $baslangicTarihi, $bitisTarihi);
            case 'demo':
            default:
                return $this->getDemoHareketleri($hesapNo, $baslangicTarihi, $bitisTarihi);
        }
    }
    
    /**
     * Akbank API entegrasyonu
     */
    private function getAkbankHareketleri($hesapNo, $baslangicTarihi, $bitisTarihi)
    {
        // TODO: Akbank API entegrasyonu eklenecek
        // Örnek: https://developer.akbank.com/docs/api/accounts/transactions
        
        return [];
    }
    
    /**
     * İş Bankası API entegrasyonu
     */
    private function getIsBankasiHareketleri($hesapNo, $baslangicTarihi, $bitisTarihi)
    {
        // TODO: İş Bankası API entegrasyonu eklenecek
        
        return [];
    }
    
    /**
     * Garanti BBVA API entegrasyonu
     */
    private function getGarantiHareketleri($hesapNo, $baslangicTarihi, $bitisTarihi)
    {
        // TODO: Garanti BBVA API entegrasyonu eklenecek
        
        return [];
    }
    
    /**
     * Demo/Test verisi üretir
     * Gerçek API entegrasyonu yapılana kadar kullanılır
     */
    private function getDemoHareketleri($hesapNo, $baslangicTarihi, $bitisTarihi)
    {
        $islemler = [];
        
        // Demo veriler - Gerçek senaryoları simüle eder
        $ornekIslemler = [
            [
                'aciklama' => 'Ali Yılmaz - 101 Nolu Daire Aidat',
                'tutar' => 1500.00,
                'yonu' => 'Gelir'
            ],
            [
                'aciklama' => 'Ayşe Demir - Blok A Daire 5 Ödeme',
                'tutar' => 2000.00,
                'yonu' => 'Gelir'
            ],
            [
                'aciklama' => 'Mehmet Kaya 202',
                'tutar' => 1750.00,
                'yonu' => 'Gelir'
            ],
            [
                'aciklama' => 'Site Elektrik Faturası',
                'tutar' => 5000.00,
                'yonu' => 'Gider'
            ],
            [
                'aciklama' => 'Can Öztürk - Daire 301 Aidat Ödemesi',
                'tutar' => 1500.00,
                'yonu' => 'Gelir'
            ]
        ];
        
        // Tarih aralığındaki günler için rastgele işlemler oluştur
        $baslangic = strtotime($baslangicTarihi);
        $bitis = strtotime($bitisTarihi);
        $gunSayisi = ceil(($bitis - $baslangic) / 86400);
        
        $refCounter = rand(1000, 9999);
        
        for ($i = 0; $i < min($gunSayisi, 10); $i++) {
            $randomGun = rand(0, $gunSayisi);
            $tarih = date('Y-m-d H:i:s', $baslangic + ($randomGun * 86400) + rand(0, 86400));
            
            $ornekIslem = $ornekIslemler[array_rand($ornekIslemler)];
            
            $islemler[] = [
                'islem_tarihi' => $tarih,
                'aciklama' => $ornekIslem['aciklama'],
                'tutar' => $ornekIslem['tutar'] + rand(-200, 200),
                'hareket_yonu' => $ornekIslem['yonu'],
                'banka_ref_no' => 'DEMO-' . date('Ymd', strtotime($tarih)) . '-' . ($refCounter++),
                'hesap_no' => $hesapNo,
                'currency' => 'TRY',
                'bakiye' => rand(10000, 50000)
            ];
        }
        
        // Tarihe göre sırala
        usort($islemler, function($a, $b) {
            return strtotime($b['islem_tarihi']) - strtotime($a['islem_tarihi']);
        });
        
        return $islemler;
    }
    
    /**
     * API yanıtını normalize eder
     * Farklı banka API'lerinden gelen yanıtları standart formata çevirir
     */
    private function normalizeResponse($rawData, $bankCode)
    {
        // Her bankanın API yanıtı farklı olabilir
        // Burada ortak bir formata dönüştürülür
        
        return $rawData;
    }
    
    /**
     * Curl ile HTTP isteği gönderir
     */
    private function makeApiRequest($url, $method = 'GET', $data = null, $headers = [])
    {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Production'da true olmalı
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        // Headers
        $defaultHeaders = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        if ($this->apiKey) {
            $defaultHeaders[] = 'Authorization: Bearer ' . $this->apiKey;
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($defaultHeaders, $headers));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new \Exception("API İsteği Hatası: " . $error);
        }
        
        if ($httpCode !== 200) {
            throw new \Exception("API Hata Kodu: " . $httpCode);
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Açıklamadan sakin bilgilerini çıkarmaya çalışır
     * AI/ML ile geliştirilebilir
     */
    public function extractResidentInfo($aciklama)
    {
        $info = [
            'isim' => null,
            'daire_no' => null,
            'blok' => null,
            'confidence' => 0
        ];
        
        // Daire numarası tespiti (101, 102, A-5, vb.)
        if (preg_match('/\b(\d{3}|\d{2,3}[A-Z]?|[A-Z]-?\d+)\b/i', $aciklama, $matches)) {
            $info['daire_no'] = $matches[1];
            $info['confidence'] += 30;
        }
        
        // Blok tespiti (Blok A, A Blok, vb.)
        if (preg_match('/blok\s*([A-Z])|([A-Z])\s*blok/i', $aciklama, $matches)) {
            $info['blok'] = $matches[1] ?? $matches[2];
            $info['confidence'] += 20;
        }
        
        // İsim tespiti (basit: büyük harfle başlayan 2 kelime)
        if (preg_match('/([A-ZĞÜŞÖÇI][a-zğüşöçı]+)\s+([A-ZĞÜŞÖÇI][a-zğüşöçı]+)/u', $aciklama, $matches)) {
            $info['isim'] = $matches[1] . ' ' . $matches[2];
            $info['confidence'] += 40;
        }
        
        // Anahtar kelimeler varsa confidence artır
        $anahtarKelimeler = ['aidat', 'ödeme', 'daire', 'tahsilat'];
        foreach ($anahtarKelimeler as $kelime) {
            if (stripos($aciklama, $kelime) !== false) {
                $info['confidence'] += 5;
            }
        }
        
        $info['confidence'] = min(100, $info['confidence']);
        
        return $info;
    }
}
