<?php

namespace App\Helper;

use Model\DefinesModel;
use PDO;
use Exception;
use DateTime;


class Helper
{
    const MONEY_UNIT = [
        '1' => 'TL',
        '2' => 'USD',
        '3' => 'EUR',
        // FATİH KALAYCI*0012*C2- Daire:15 Canan SUBAŞI KALAYCI gibi formatlar
        '/\*([A-Za-z0-9]+)[\.\-]?\s*DAİRE\s*:?(\d+)/i'
    ];

    const UNITS = [
        '1' => 'Ad.',
        '2' => 'Kg',
        '3' => 'Lt.',
        '4' => 'Mt.',
        '5' => 'Pak.',
    ];

    const KDV_ORANI = [
        '0' => '% 0',
        '1' => '% 1',
        '8' => '% 8',
        '18' => '% 18',
        '20' => '% 20',
    ];

    const INCOME_EXPENSE_TYPE = [
        '1' => 'Gelir',
        '2' => 'Kesinti',
        '3' => 'Ödeme',
        '4' => 'Maaş',
        '5' => 'Puantaj Çalışma',
        '6' => 'Hakediş',
        '' => 'Bilinmiyor',
    ];

    const INC_EXP = [
        '1' => 'Gelir',
        '2' => 'Gider',
    ];

    const PRIORITY = [
        '1' => 'Düşük',
        '2' => 'Orta',
        '3' => 'Yüksek',
    ];

    const PERIOD = [
        '0' => 'AYLIK',
        '1' => '3 AYLIK',
        '2' => '6 AYLIK',
        '3' => 'YILLIK',
        '4' => 'TEK SEFERLİK',
    ];

    const STATE = [
        '1' => 'Aktif',
        '0' => 'Pasif',
    ];

    const TARGETTYPE = [
        '0' => 'Seçiniz',
        'all' => 'Tüm Sakinler',
        'evsahibi' => 'Ev Sahipleri',
        'block' => 'Blok Seçerek',
        'person' => 'Kişi Borçlandırma',
        'dairetipi' => 'Daire Tipine Göre',
    ];

    const  RELATIONSHIP = [
        '1' => 'Anne',
        '2' => 'Baba',
        '3' => 'Kardeş',
        '4' => 'Eş',
        '5' => 'Çocuk',
        '6' => 'Dede',
        '7' => 'Babaanne',
        '8' => 'Anneanne',
        '9' => 'Amca',
        '10' => 'Dayı',
        '11' => 'Teyze',
        '12' => 'Hala',
        '13' => 'Kuzen',
        '14' => 'Diğer'
    ];

    public const ikametTuru = [
        '1' => 'Kat Maliki',
        '2' => 'Kiracı',
        '3' => 'Çalışan',
        '4' => 'Misafir',
        '5' => 'Mirasçı'
    ];
    public const bakimTuru = [
        '1' => 'Bakım / Arıza / Onarım',
        '2' => 'Periyodik Bakım'
    ];
    public const Durum = [
        0 => [
            'label' => 'Bilinmiyor',
            'class' => 'bg-dark text-white',
            'icon'  => 'fas fa-question'
        ],
        1 => [
            'label' => 'Bekliyor',
            'class' => 'bg-secondary text-white',
            'icon'  => 'fas fa-hourglass-start'
        ],
        2 => [
            'label' => 'Devam Ediyor',
            'class' => 'bg-warning text-dark',
            'icon'  => 'fas fa-clock'
        ],
        3 => [
            'label' => 'Tamamlandı',
            'class' => 'bg-success text-white',
            'icon'  => 'fas fa-check'
        ]
    ];

    CONST ODEME_YONTEMI = [
        "1" => "Nakit",
        "2" => "Kredi Kartı",
        "3" => "Havale / EFT",
        "4" => "Çek",
        "5" => "Senet",
        "6" => "Diğer"
    ];

    CONST ODEME_KATEGORISI = [
        "1" => "Aidat",
        "2" => "Su",
        "3" => "Elektrik",
        "4" => "Doğalgaz",
        "5" => "Internet",
        "6" => "Diğer"
    ];
    const DAIRE_TYPE = [];

    public static function getPriority($priority)
    {
        $priorities = self::PRIORITY;
        return $priorities[$priority];
    }


    public static function getOdemeKategoriSelect($name = 'odeme_kategori', $selected = '1')
    {
        $select = '<select id="' . $name . '" name="' . $name . '" class="form-select select2 w-100" >';
        foreach (self::ODEME_KATEGORISI as $key => $value) {
            $selectedAttr = $selected == $key ? 'selected' : '';
            $select .= "<option value='$key' $selectedAttr>$value</option>";
        }
        $select .= '</select>';
        return $select;
    }
    


    /**
     * Ödeme Yöntemi Select
     */
    public static function getOdemeYontemiSelect($name = 'odeme_yontemi', $selected = '1')
    {
        $select = '<select id="' . $name . '" name="' . $name . '" class="form-select select2 w-100" >';
        foreach (self::ODEME_YONTEMI as $key => $value) {
            $selectedAttr = $selected == $key ? 'selected' : '';
            $select .= "<option value='$key' $selectedAttr>$value</option>";
        }
        $select .= '</select>';
        return $select;
    }


    public static function short($value, $lenght = 21)
    {
        if (empty($value)) return;
        return strlen($value) > $lenght ? substr($value, 0, $lenght) . '...' : $value;
    }

    public static function formattedMoney($value, $currency = 1)
    {
        return number_format($value, 2, ',', '.') . ' ' . self::MONEY_UNIT[$currency];
    }

    // 109.852,25 şeklinde gelen değeri 109852.25 olarak döndürür
    public static function formattedMoneyToNumber($value)
    {
        //içinde ₺ olabilir, onu kaldırır
        $value = str_replace('₺', '', $value);
        $value = str_replace(' ', '', $value); // Boşlukları kaldırır
        $value = str_replace('TL', '', $value); // TL'yi kaldırır
        return str_replace(['.', ','], ['', '.'], $value);
    }


    // Veritabanından gelen sayıdaki "." yı virgüle çevirir
    public static function moneyToNumber($value)
    {
        return str_replace('.', ',', $value);
    }

    // Para birim formatında TRY olmadan
    public static function formattedMoneyWithoutCurrency($value)
    {
        return number_format($value, 2, ',', '.');
    }

    public static function moneySelect($name = 'moneys', $selected = '1')
    {
        $select = '<select id="' . $name . '" name="' . $name . '" class="form-control" data-select2-selector="money" style="width:100%">';

        foreach (self::MONEY_UNIT as $key => $value) {
            $selectedAttr = $selected == $key ? 'selected' : '';
            $select .= "<option value='$key' $selectedAttr>$value</option>";
        }
        $select .= '</select>';
        return $select;
    }

    public static function money($currency = 1)
    {
        if (!isset(self::MONEY_UNIT[$currency])) {
            return '';
        }
        return self::MONEY_UNIT[$currency];
    }

    public static function unitSelect($name = 'units', $selected = '1')
    {
        $select = '<select id="' . $name . '" name="' . $name . '" class="select2 form-control" style="width:100%">';
        foreach (self::UNITS as $key => $value) {
            $selectedAttr = $selected == $key ? 'selected' : '';
            $select .= "<option value='$key' $selectedAttr>$value</option>";
        }
        $select .= '</select>';
        return $select;
    }

    public static function unit($unit = '1')
    {
        if (!isset(self::UNITS[$unit])) {
            return '';
        }
        return self::UNITS[$unit];
    }

    public static function kdvSelect($name = 'kdv', $selected = '20')
    {
        $select = '<select id="' . $name . '" name="' . $name . '" class="select2 form-control" style="width:100%">';
        foreach (self::KDV_ORANI as $key => $value) {
            $selectedAttr = $selected == $key ? 'selected' : '';
            $select .= "<option value='$key' $selectedAttr>$value</option>";
        }
        $select .= '</select>';
        return $select;
    }

    public static function kdv($value, $kdv = '1')
    {
        return $value . ' %' . self::KDV_ORANI[$kdv];
    }

    public static function balanceColor($tutar)
    {
        if ($tutar < 0) {
            return 'text-danger';
        } elseif ($tutar > 0) {
            return 'text-success';
        } else {
            return '';
        }
    }

    public static function getIncExpTypeName($type)
    {
        $types = self::INC_EXP;
        return $types[$type];
    }

    public static function incExpTypeSelect($name = 'incexp_type', $selected = '1')
    {
        $select = '<select id="' . $name . '" name="' . $name . '" class="form-select select2 w-100" >';
        foreach (self::INC_EXP as $key => $value) {
            $selectedAttr = $selected == $key ? 'selected' : '';
            $select .= "<option value='$key' $selectedAttr>$value</option>";
        }
        $select .= '</select>';
        return $select;
    }

    /**
     * Yakınlık derecelerini select olarak döndürür
     * @param string $name
     * @param string $selected
     * @return string
     */
    public static function relationshipSelect($name = 'relationship', $selected = '1')
    {
        $select = '<select id="' . $name . '" name="' . $name . '" class="form-select select2 w-100" >';
        foreach (self::RELATIONSHIP as $key => $value) {
            $selectedAttr = $selected == $key ? 'selected' : '';
            $select .= "<option value='$key' $selectedAttr>$value</option>";
        }
        $select .= '</select>';
        return $select;
    }


    // dd fonksiyonu
    public static function dd($data)
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        die();
    }

    /*     
    * Gelen kelime adından baş harfleri alır
     * @param string $name
     * @param int $count Baş harf sayısı
     * @return string Baş harfler
     */
    public static function getInitials($name, $count = 2)
    {
        if (empty($name) || $name == null) {
            return '';
        }
        $name = explode(' ', $name);
        $initials = '';
        $counter = 0;
        foreach ($name as $n) {
            if (!empty($n) && $counter < $count) {  // Boş olup olmadığını ve counter'ı kontrol et
                $initials .= $n[0];
                $counter++;
            }
        }
        return strtoupper($initials);
    }

    // authorize sayfasını include eder
    public static function authorizePage()
    {
        echo '<div class="empty">
                <div class="empty-img">
                    <img src="static/unauthorize-red.svg" alt="" style="width:200px;height:200px">


                </div>
                <p class="empty-title">Yetkiniz Yok!!!</p>
                <p class="empty-subtitle text-secondary">
                    Bu alanı görüntüleme yetkiniz bulunmamaktadır!
                </p>
            
            </div>';
    }

    public static function PeriodSelect($name = 'period', $selected = '0')
    {
        $select = '<select id="' . $name . '" name="' . $name . '" class="form-select select2 w-100" >';
        foreach (self::PERIOD as $key => $value) {
            $selectedAttr = $selected == $key ? 'selected' : '';
            $select .= "<option value='$key' $selectedAttr>$value</option>";
        }
        $select .= '</select>';
        return $select;
    }

    public static function StateSelect($name = 'state', $selected = '0')
    {
        $select = '<select id="' . $name . '" name="' . $name . '" class="form-select select2 w-100" >';
        foreach (self::STATE as $key => $value) {
            $selectedAttr = $selected == $key ? 'selected' : '';
            $select .= "<option value='$key' $selectedAttr>$value</option>";
        }
        $select .= '</select>';
        return $select;
    }

    public static function getState($state)
    {
        $states = self::STATE;
        return $states[$state];
    }

    public static function getTargetType($type)
    {
        $types = self::TARGETTYPE;
        return $types[$type];
    }

    public static function targetTypeSelect($name = 'target_type', $selected = '0', $disabled = false)
    {
        $select = '<select id="' . $name . '" name="' . $name . '" class="form-select select2 w-100" '
            . ($disabled ? 'disabled' : '') . '>';
        foreach (self::TARGETTYPE as $key => $value) {
            $selectedAttr = $selected == $key ? 'selected' : '';
            $select .= "<option value='$key' $selectedAttr>$value</option>";
        }
        $select .= '</select>';
        return $select;
    }

    /**
     * Get icon with color by type
     * @param $type int app/helper/financial.php içindeki sabitler
     * @return string
     */
    public static function getIconWithColorByType($type)
    {
        $icon = '';
        $color = '';

        switch ($type) {
            case 1:  // Gelir
            case 6:  // Hakediş
                $icon = 'ti-arrow-up-dashed';
                $color = 'color-green';
                break;
            case 2:
                $icon = 'ti-arrow-down-dashed';
                $color = 'color-red';
                break;
            case 3:
                $icon = 'ti-upload';
                $color = 'color-yellow';
                break;
            case 5:  // Ödeme
            case 7:  // Maaş
                $icon = 'ti-cash-register';
                $color = 'color-yellow';
                break;
            case 10:  // Hakediş
                $icon = 'ti-building-estate';
                $color = 'color-green';
                break;
            case 11:  // Masraf
            case 12:  // Kesinti
            case 15:  // Personel Kesinti
                $icon = 'ti-fold-down';
                $color = 'color-red';
                break;
            case 14:  // Puantaj Çalışma
                $icon = 'ti-stretching';
                $color = 'color-red';
                break;

            default:
                $icon = 'ti-question-mark';
                $color = '';
                break;
        }

        return "<i class='ti $icon icon $color me-1'></i>";
    }

    /**
     * Site id'ye göre daire tiplerini select olarak döndürür
     * @param int $site_id
     * @return string
     */
    public static function getApartmentTypesSelect($site_id)
    {
        $Defines = new DefinesModel();
        $apartmentTypes = $Defines->getAllByApartmentType(3);
        $select = '<select id="apartment_type" multiple data-live-search="true" name="apartment_type[]"  class="form-select select2 w-100">';
        $select .= '<option value="">Daire Tipi Seçiniz</option>';
        foreach ($apartmentTypes as $type) {
            $select .= '<option value="' . $type->id . '">' . $type->define_name . '</option>';
        }
        $select .= '</select>';
        return $select;
    }



    /**
     * Serbest metin formatındaki bir açıklamadan standart 'BLOKDDAİRE' formatında
     * daire kodunu çıkaran, en yüksek isabet oranına sahip nihai fonksiyon.
     * Bu fonksiyon, bir önceki cevaptaki yapı üzerine inşa edilmiştir.
     *
     * @param string|null $description Analiz edilecek açıklama metni.
     * @return string|null Bulunan ilk daire kodu veya bulunamazsa null.
     */
    public static function extractApartmentInfo($description)
    {
        if (empty($description)) {
            return null;
        }

        // 1. ADIM: HAZIRLIK - Metni standart bir forma getir.
        $text = ' ' . strtoupper($description) . ' '; // Başına/sonuna boşluk ekle

        // Adım 1a: Türkçe karakterleri ve özel karakterleri standartlaştır.
        $text = str_replace(
            ['İ', 'Ü', 'Ö', 'Ç', 'Ş', 'Ğ', '’', '`', "'", 'Ğ'],
            ['I', 'U', 'O', 'C', 'S', 'G', '', '', '', 'G'],
            $text
        );

        // Adım 1b: Bitişik yazımları ayır (örn: C3BLOK -> C3 BLOK, DAIRE5 -> DAIRE 5)
        $text = preg_replace('/([A-Z]\d+)(BLOK|DAIRE)/', '$1 $2', $text);
        $text = preg_replace('/(BLOK|DAIRE|NO)(\d+)/', '$1 $2', $text);

        // Adım 1c: Tüm ayraçları boşlukla değiştir.
        $text = str_replace(['/', ',', ':', '_', '(', ')', '.'], ' ', $text);

        // Adım 1d: "D-5" gibi yapıları "D 5" haline getir.
        $text = preg_replace('/\b(D|NO)\s*-\s*(\d+)\b/', 'D $2', $text);

        // Adım 1e: Çoklu boşlukları tek boşluğa indir.
        $text = preg_replace('/\s+/', ' ', $text);

        // 2. ADIM: HİYERARŞİK DESENLER (En spesifikten en genele doğru)
        $patterns = [
            // DESEN 1: En yüksek öncelikli, %100 kesin format. Bitişik yazım.
            ['pattern' => '/\b([A-Z]\d+D\d+)\b/'],

            // DESEN 2: Blok-Daire arasında sadece tire olan yapılar (örn: "C2-5", "B2-16").
            ['pattern' => '/\b([A-Z]\d+)\s*-\s*(\d+)\b/'],

            // DESEN 3: En güçlü ve esnek desen. Blok ve Daire arasında her türlü "gürültü" olabilir.
            ['pattern' => '/\b([A-Z]\s*\d+)\b.*?(?:DAIRE|DA|D|NO|NUMARA|N)\s*(\d+)\b/'],

            // DESEN 4: Ters sıralı yapı. Önce Daire, sonra Blok.
            ['pattern' => '/\b(?:DAIRE|DA|D|NO)\s*(\d+)\b.*?\b(BLOK|BLK)\s*([A-Z]\s*\d+)\b/'],

            // DESEN 5: En genel fallback. Sadece "Blok Numara" var (örn: "C1 17", "B1 03").
            ['pattern' => '/\b([A-Z]\d+)\s+(\d+)\b/']
        ];

        // 3. ADIM: Eşleşmeyi bul ve sonucu döndür.
        foreach ($patterns as $item) {
            if (preg_match($item['pattern'], $text, $matches)) {

                $blok = '';
                $daire = '';

                if (count($matches) === 2) { // Tek grup yakalayanlar (Desen 1)
                    if (preg_match('/([A-Z]\d+)D(\d+)/', $matches[1], $subMatches)) {
                        return $subMatches[1] . 'D' . $subMatches[2];
                    }
                } else if (count($matches) >= 3) { // 2 veya daha fazla grup yakalayanlar
                    $potentialDaire = $matches[count($matches) - 1];
                    $potentialBlok = $matches[1];

                    if (is_numeric($potentialDaire)) {
                        $blok = $potentialBlok;
                        $daire = $potentialDaire;
                    }
                }

                if (!empty($blok) && !empty($daire)) {
                    $blok = preg_replace('/\s+/', '', $blok);
                    if ((int)$daire > 0 && (int)$daire < 100) {
                        return $blok . 'D' . $daire;
                    }
                }
            }
        }

        return null;
    }


    //   /**
    //  * Verilen bir açıklama metni içinde, sağlanan kişi listesinden birinin adını arar.
    //  * Eşleşme bulursa o kişinin ID'sini döndürür.
    //  *
    //  * @param string $description       İçinde isim aranacak olan ham açıklama metni.
    //  * @param array $people             Arama yapılacak kişilerin listesi. 
    //  *                                  Her bir kişi nesnesi en az 'id' ve 'adi_soyadi' alanlarına sahip olmalıdır.
    //  * @return int|null                 Bulunan kişinin ID'si veya eşleşme yoksa null.
    //  */
    // public static function findMatchingPersonInDescription($description, $people)
    // {
    //     if (empty($description) || empty($people)) {
    //         return null;
    //     }

    //     // Açıklamayı standart bir formata getir.
    //     $standartDescription = self::standardizeText($description);

    //     $foundPeople = [];

    //     foreach ($people as $person) {
    //         // ---> DÜZELTME 1: ARANAN İSMİ DE STANDARTLAŞTIR <---
    //         // Veritabanından gelen ismi de aynı standart formata getiriyoruz.
    //         $personName = self::standardizeText($person->adi_soyadi);

    //         $logger = getlogger();

    //         // Loglamayı burada yapmak, hem standartlaşmış açıklamayı hem de standartlaşmış ismi görmenizi sağlar.
    //         $logger->info("Aranan standart kişi adı: " . $personName . 
    //                                " | Person ID: " . $person->id . 
    //                                " | Açıklama: " . $standartDescription);

    //         if (str_word_count($personName) < 2) {
    //             continue;
    //         }

    //         // ---> DÜZELTME 2: REGEX'E BÜYÜK/KÜÇÜK HARF DUYARSIZLIĞI EKLE ('i' FLAG'I) <---
    //         // Bu, standartlaştırmada bir hata olsa bile eşleşmeyi garantiler.
    //         if (preg_match('/\b' . preg_quote($personName, '/') . '\b/i', $standartDescription)) {
    //             $foundPeople[] = $person->id;
    //         }
    //     }

    //     if (!empty($foundPeople)) {
    //         return $foundPeople[0];
    //     }

    //     return null;
    // }




    /**
     * Verilen bir açıklama metni içinde, sağlanan kişi listesinden birini
     * "bulanık" (fuzzy) bir mantıkla arar. Eşleşme bulursa o kişinin ID'sini döndürür.
     * Bu yöntem, yazım hatalarına, eksik isimlere ve kelime sırasına karşı daha toleranslıdır.
     *
     * @param string $description      İçinde isim aranacak olan ham açıklama metni.
     * @param array $people   Arama yapılacak kişilerin listesi.
     * @return int|null                En yüksek benzerlik skoruna sahip kişinin ID'si veya eşleşme yoksa null.
     */
    public static function findMatchingPersonInDescription($description, $people)
    {
        if (empty($description) || empty($people)) {
            return null;
        }

        // Açıklamayı sadece bir kere standartlaştır.
        $standartDescription = self::standardizeText($description);
        $logger = getlogger();

        $bestMatch = [
            'person_id' => null,
            'score' => 0 // En iyi eşleşme skorunu tutacak
        ];

        foreach ($people as $person) {
            $personName = self::standardizeText($person->adi_soyadi);

            // "EV SAHIBI GIRILECEK" gibi anlamsız isimleri atla.
            if (strpos($personName, 'GIRILECEK') !== false) {
                continue;
            }

            // İsimdeki parantezleri ve içindekileri temizle, sadece kelimeleri al.
            $personName = preg_replace('/\s*\(.*\)/', '', $personName);
            $nameParts = explode(' ', $personName);

            if (count($nameParts) < 2) { // İsim en az iki kelimeden oluşmalı
                continue;
            }

            $matchesFound = 0;
            $finalScore = 0;
            $totalParts = count($nameParts);

            // İsimdeki her bir parçanın açıklamada geçip geçmediğini kontrol et.
            foreach ($nameParts as $part) {
                if (strlen($part) > 1 && strpos($standartDescription, $part) !== false) {
                    $matchesFound++;
                }
            }

            // Eşleşme bulundu mu?
            if ($matchesFound > 0) {
                // Bir skor hesapla. İsimdeki tüm parçalar bulunursa skor daha yüksek olur.
                // Bu, "MEHMET EMİN SÖNMEZOĞLU"nun "EMİN"den daha iyi bir eşleşme olmasını sağlar.
                $currentScore = ($matchesFound / $totalParts) * 100;

                // Yazım hatalarını yakalamak için similar_text ile skoru daha da iyileştir.
                similar_text($personName, $standartDescription, $similarityPercent);

                // İki skorun ortalamasını veya ağırlıklı ortalamasını alabiliriz.
                $finalScore = ($currentScore + $similarityPercent) / 2;

                // Eğer bu skor, şimdiye kadarki en iyi skordan daha iyiyse, bunu en iyi eşleşme olarak kaydet.
                if ($finalScore > $bestMatch['score']) {
                    $bestMatch['score'] = $finalScore;
                    $bestMatch['person_id'] = $person->id;
                }
            }

            // Loglama, hangi kişinin ne kadar benzerlik skoru aldığını gösterir.
            $logger->info("Person ID: " . $person->id .
                " | Person Name: " . $personName .
                " | Description: " . $standartDescription .
                " | Match Score: " . $finalScore);
        }

        // Eğer makul bir eşleşme bulunduysa (örn: skoru 40'tan yüksekse), o kişinin ID'sini döndür.
        if ($bestMatch['score'] > 40) {
            return $bestMatch['person_id'];
        }

        return null;
    }



    // ... standardizeText fonksiyonu değişmeden kalır ...
    /**
     * Metni karşılaştırma için standart bir forma (büyük harf, standart Türkçe karakterler) dönüştürür.
     * Bu yardımcı fonksiyon, kod tekrarını önler.
     *
     * @param string $text
     * @return string
     */
    private static function standardizeText($text)
    {
        $text = mb_strtoupper($text, 'UTF-8');
        return str_replace(
            ['İ', 'Ü', 'Ö', 'Ç', 'Ş', 'Ğ'],
            ['I', 'U', 'O', 'C', 'S', 'G'],
            $text
        );
    }


    // /**
    //  * Serbest metin formatındaki bir açıklamadan standart 'BLOKDDAİRE' formatında
    //  * daire kodunu çıkaran, en yüksek isabet oranına sahip nihai fonksiyon.
    //  * Bu fonksiyon, bir önceki cevaptaki yapı üzerine inşa edilmiştir.
    //  *
    //  * @param string|null $description Analiz edilecek açıklama metni.
    //  * @return string|null Bulunan ilk daire kodu veya bulunamazsa null.
    //  */
    // public static function extractApartmentInfo($description)
    // {
    //     if (empty($description)) {
    //         return null;
    //     }

    //     // 1. ADIM: HAZIRLIK - Metni standart bir forma getir.
    //     $text = ' ' . strtoupper($description) . ' '; // Başına/sonuna boşluk ekle, bu regex'i basitleştirir.

    //     // Adım 1a: Türkçe karakterleri ve özel karakterleri standartlaştır.
    //     $text = str_replace(
    //         ['İ', 'Ü', 'Ö', 'Ç', 'Ş', 'Ğ', '’', '`', "'", 'Ğ'],
    //         ['I', 'U', 'O', 'C', 'S', 'G', '', '', '', 'G'],
    //         $text
    //     );

    //     // Adım 1b: Bitişik yazımları ayır (örn: C3BLOK -> C3 BLOK, DAIRE5 -> DAIRE 5)
    //     $text = preg_replace('/([A-Z]\d+)(BLOK|DAIRE)/', '$1 $2', $text);
    //     $text = preg_replace('/(BLOK|DAIRE|NO)(\d+)/', '$1 $2', $text);

    //     // Adım 1c: Tüm ayraçları boşlukla değiştir.
    //     $text = str_replace(['/', ',', ':', '_', '(', ')', '.'], ' ', $text);

    //     // Adım 1d: "D-5" gibi yapıları "D 5" haline getir.
    //     $text = preg_replace('/(D|NO)\s*-\s*(\d+)/', 'D $2', $text);

    //     // Adım 1e: Çoklu boşlukları tek boşluğa indir.
    //     $text = preg_replace('/\s+/', ' ', $text);

    //     // 2. ADIM: HİYERARŞİK DESENLER (En spesifikten en genele doğru)
    //     $patterns = [
    //         // DESEN 1: En yüksek öncelikli, %100 kesin format. Bitişik yazım.
    //         // Örn: "A1D9", "B1_D14" (temizlik sonrası), "SAMİERGİNB1D5"
    //         [
    //             'pattern' => '/([A-Z]\d+D\d+)/',
    //             'formatter' => function($m) { return $m[1]; }
    //         ],

    //         // DESEN 2: Blok-Daire arasında sadece tire olan yapılar.
    //         // Örn: "C2-5", "B2-16", "C4-3"
    //          [
    //             'pattern' => '/\s([A-Z]\d+)\s*-\s*(\d+)\s/',
    //             'formatter' => function($m) { return $m[1] . 'D' . $m[2]; }
    //         ],

    //         // DESEN 3: En güçlü ve esnek desen. Blok ve Daire arasında her türlü "gürültü" olabilir.
    //         [
    //             'pattern' => '/([A-Z]\s*\d+)\s+.*?(?:DAIRE|DA|D|NO|NUMARA|N)\s*(\d+)/',
    //             'formatter' => function($m) {
    //                 $blok = preg_replace('/\s+/', '', $m[1]); // "C 2" -> "C2"
    //                 return $blok . 'D' . $m[2];
    //             }
    //         ],

    //         // DESEN 4: Ters sıralı yapı. Önce Daire, sonra Blok.
    //         [
    //             'pattern' => '/(?:DAIRE|DA|D|NO)\s*(\d+)\s+.*?(?:BLOK|BLK)\s+([A-Z]\s*\d+)/',
    //             'formatter' => function($m) {
    //                  $blok = preg_replace('/\s+/', '', $m[2]);
    //                  return $blok . 'D' . $m[1];
    //             }
    //         ],

    //         // DESEN 5: En genel fallback. Sadece "Blok Numara" var.
    //         [
    //             'pattern' => '/\s([A-Z]\d+)\s+(\d+)\s/',
    //             'formatter' => function($m) {
    //                 if ((int)$m[2] > 0 && (int)$m[2] < 100) {
    //                     return $m[1] . 'D' . $m[2];
    //                 }
    //                 return null;
    //             }
    //         ],
    //     ];

    //     // 3. ADIM: Eşleşmeyi bul ve sonucu döndür.
    //     foreach ($patterns as $item) {
    //         if (preg_match($item['pattern'], $text, $matches)) {
    //             $result = call_user_func($item['formatter'], $matches);
    //             if ($result !== null) {
    //                 return $result;
    //             }
    //         }
    //     }

    //     return null; // Hiçbir desen eşleşmedi.
    // }




    // public static function extractApartmentInfo($description)
    // {
    //     // Önce tüm desenleri tanımlayalım
    //     $patterns = [
    //         // Standart formatlar: C5 Daire 9, B1 DAİRE 6, C2- Daire:15
    //         '/([A-Z]\d+)\s*(?:BLOK|Blok|blok)?\s*(?:DAİRE|Daire|DAiRE|D\.?)\s*[:\.\-]?\s*(\d+)/i',

    //         // C5 16 numara, B3 Blok Daire10 gibi bitişik yazımlar
    //         '/([A-Z]\d+)\s+(\d+)\s*(?:numara|Daire|No|no)?/i',

    //         // C2.D.13, A1-DAİRE9 gibi nokta/tire ile ayrılmış
    //         '/([A-Z]\d+)[\.\-]\s*D\.?\s*(\d+)/i',

    //         // B3 BLOK DAİRE5 gibi bitişik yazımlar
    //         '/([A-Z]\d+)\s*BLOK\s*DAİRE\s*(\d+)/i',

    //         // b1 blok d 17 veya B1 Blok D 17
    //         '/([A-Z]\d+)\s*blok\s*d\s*(\d+)/i',

    //         // c5 blok no 19
    //         '/([A-Z]\d+)\s*blok\s*no\s*(\d+)/i',

    //         // B 2 blok daire 2
    //         '/([A-Z]\d+)\s*blok\s*daire\s*(\d+)/i',


    //         // A1-DAİRE 9, C1-Daire 5
    //         '/([A-Z]\d+)[\-\.]?\s*DAİRE\s*(\d+)/i',

    //         // A1 D9 gibi basit formatlar
    //         '/([A-Z]\d+)\s*D(\d+)/i',


    //         // C2D13 gibi direkt formatlar
    //         '/([A-Z]\d+D\d+)/i',

    //         // A5 16 gibi basit formatlar
    //         '/([A-Z]\d+)\s+(\d+)/',


    //         // C3 16 gibi basit formatlar
    //         '/([A-Z]\d+)\s+(\d+)/',

    //         // B1D5, C2D13 gibi direkt formatlar
    //         '/([A-Z]\d+D\d+)/i',

    //         // Özel durumlar: C2- Daire:15
    //         '/([A-Z]\d+)\-?\s*Daire\s*\:?\s*(\d+)/i',

    //         // Blok ve daire farklı konumda: BLOK C5 DAİRE 9
    //         '/(?:BLOK|Blok)\s*([A-Z]\d+).*?(?:DAİRE|Daire)\s*(\d+)/i',

    //         // Daire kelimesi önce: DAİRE 9 BLOK C5
    //         '/(?:DAİRE|Daire)\s*(\d+).*?(?:BLOK|Blok)\s*([A-Z]\d+)/i',
    //     ];

    //     foreach ($patterns as $pattern) {
    //         if (preg_match($pattern, $description, $matches)) {
    //             // Eğer direkt B1D5 formatında eşleşme varsa
    //             if (isset($matches[1]) && preg_match('/^[A-Z]\d+D\d+$/i', $matches[1])) {
    //                 return strtoupper($matches[1]);
    //             }

    //             // Normalde blok ve daire ayrı eşleşir
    //             if (isset($matches[1]) && isset($matches[2])) {
    //                 return strtoupper(trim($matches[1])) . 'D' . trim($matches[2]);
    //             }
    //         }
    //     }

    //     // Özel durumlar için manuel kontrol
    //     $specialCases = [
    //         'C5 16 numara asansör bakım ücreti' => 'C5D16',
    //         'B3 BLOK DAİRE5' => 'B3D5',
    //         'B3 Blok Daire 10' => 'B3D10',
    //         'b1 blok d 17' => 'B1D17',
    //         'C2. D.13' => 'C2D13',
    //         'C3 16' => 'C3D16',
    //     ];

    //     foreach ($specialCases as $case => $code) {
    //         if (strpos($description, $case) !== false) {
    //             return $code;
    //         }
    //     }

    //     // Son çare: sayısal bloklar için (A1, B2 gibi olmayanlar)
    //     if (preg_match('/(\d+)\s*(?:BLOK|Blok|blok)?\s*(?:DAİRE|Daire|DAiRE|D\.?)\s*[:\.\-]?\s*(\d+)/i', $description, $matches)) {
    //         return 'BLOCK' . trim($matches[1]) . 'D' . trim($matches[2]);
    //     }

    //     return null;
    // }


    /**
     * Gelen değerlerden gün bazında hesaplama yapar
     * @param string $start_date Başlangıç tarihi
     * @param string $end_date Bitiş tarihi
     * @param string $param_date Parametre tarihi
     * @param float $amount Tutar
     */

    public static function calculateDayBased($start_date, $end_date, $param_date, $amount)
    {
        $start = strtotime($start_date);
        $end = strtotime($end_date);
        $param = strtotime($param_date);

        if ($param < $start || $param > $end) {
            return $amount; // Parametre tarihi başlangıç ve bitiş tarihleri arasında değilse tutar döner
        }


        //Bitiş tarihi, paramatre tarihinden sonraysa  0 döner
        if ($end < $param) {
            return 0; // Bitiş tarihi parametre tarihinden önceyse 0 döner
        }

        // Gün farkını hesapla
        $days = ($end - $start) / (60 * 60 * 24);
        if ($days <= 0) {
            return 0; // Geçersiz gün farkı
        }

        // Gün bazında tutarı hesapla
        $daily_amount = $amount / $days;
        $param_days = ($end - $param) / (60 * 60 * 24);

        return round($daily_amount * $param_days, 2); // İstenen tarihe göre tutarı döner
    }




    /**
     * Belirtilen iki tarih aralığının kesişimine göre orantılı tutarı hesaplar.
     * '0000-00-00' gibi geçersiz veya boş tarihleri mantıklı varsayımlarla yönetir.
     *
     * @param string $billing_start_date Fatura başlangıç tarihi (örn: '2025-01-01')
     * @param string $billing_end_date   Fatura bitiş tarihi (örn: '2025-01-31')
     * @param string|null $person_entry_date  Kişinin giriş tarihi. '0000-00-00' veya boş olabilir.
     * @param string|null $person_exit_date   Kişinin çıkış tarihi. '0000-00-00' veya boş olabilir.
     * @param float $total_amount         Fatura döneminin toplam tutarı
     * @return float                      Hesaplanan orantılı tutar
     */
    public static function calculateProratedAmount(
        $billing_start_date,
        $billing_end_date,
        $person_entry_date,
        $person_exit_date,
        $total_amount
    ) {
        try {
            // 1. Ana fatura tarihlerini DateTime nesnelerine çevirelim.
            $billing_start = new DateTime($billing_start_date);
            $billing_end = new DateTime($billing_end_date);
        } catch (Exception $e) {
            // Fatura tarihleri geçersizse hesaplama yapılamaz.
            return 0;
        }

        // 2. Kişinin giriş ve çıkış tarihlerini mantığa göre belirleyelim.

        // Giriş tarihi geçerli bir tarih mi? Değilse, fatura başlangıcını varsay.
        $person_entry = ($person_entry_date && $person_entry_date !== '0000-00-00')
            ? new DateTime($person_entry_date)
            : $billing_start;

        // Çıkış tarihi geçerli bir tarih mi? Değilse, fatura bitişini varsay.
        $person_exit = ($person_exit_date && $person_exit_date !== '0000-00-00')
            ? new DateTime($person_exit_date)
            : $billing_end;

        // 3. Günlük ücreti hesaplamak için fatura dönemindeki toplam gün sayısını bulalım.
        $total_billing_days = $billing_start->diff($billing_end)->days + 1;

        if ($total_billing_days <= 0) {
            return 0; // Hatalı aralık veya sıfıra bölme hatasını önle.
        }
        $daily_rate = $total_amount / $total_billing_days;

        // 4. Ücretlendirilecek dönemin başlangıcını belirleyelim (kesişim başlangıcı).
        // Bu, fatura başlangıcı ve kişinin efektif giriş tarihinden hangisi daha SONRA ise odur.
        $charge_start_date = max($billing_start, $person_entry);

        // 5. Ücretlendirilecek dönemin sonunu belirleyelim (kesişim sonu).
        // Bu, fatura bitişi ve kişinin efektif çıkış tarihinden hangisi daha ÖNCE ise odur.
        $charge_end_date = min($billing_end, $person_exit);

        // 6. Ücretlendirilecek gün sayısını hesaplayalım.
        if ($charge_start_date > $charge_end_date) {
            // Konaklama süresi, fatura dönemiyle hiç kesişmiyorsa ücret 0'dır.
            return 0;
        }

        $chargeable_days = $charge_start_date->diff($charge_end_date)->days + 1;

        // 7. Sonucu hesaplayıp döndürelim.
        return round($chargeable_days * $daily_rate, 2);
    }
    public static function paraFormat($tutar, $decimal = 2)
    {
        return number_format((float)$tutar, $decimal, ',', '.');
    }
}
