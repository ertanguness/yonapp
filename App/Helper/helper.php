<?php

namespace App\Helper;

use Model\DefinesModel;

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

    const DAIRE_TYPE = [];

    public static function getPriority($priority)
    {
        $priorities = self::PRIORITY;
        return $priorities[$priority];
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

    public static function targetTypeSelect($name = 'target_type', $selected = '0')
    {
        $select = '<select id="' . $name . '" name="' . $name . '" class="form-select select2 w-100" >';
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
    public static function extractApartmentInfo($description)
    {
        // Önce tüm desenleri tanımlayalım
        $patterns = [
            // Standart formatlar: C5 Daire 9, B1 DAİRE 6, C2- Daire:15
            '/([A-Z]\d+)\s*(?:BLOK|Blok|blok)?\s*(?:DAİRE|Daire|DAiRE|D\.?)\s*[:\.\-]?\s*(\d+)/i',

            // C5 16 numara, B3 Blok Daire10 gibi bitişik yazımlar
            '/([A-Z]\d+)\s+(\d+)\s*(?:numara|Daire|No|no)?/i',

            // C2.D.13, A1-DAİRE9 gibi nokta/tire ile ayrılmış
            '/([A-Z]\d+)[\.\-]\s*D\.?\s*(\d+)/i',

            // B3 BLOK DAİRE5 gibi bitişik yazımlar
            '/([A-Z]\d+)\s*BLOK\s*DAİRE\s*(\d+)/i',

            // b1 blok d 17 veya B1 Blok D 17
            '/([A-Z]\d+)\s*blok\s*d\s*(\d+)/i',

            // c5 blok no 19
            '/([A-Z]\d+)\s*blok\s*no\s*(\d+)/i',

            // A1-DAİRE 9, C1-Daire 5
            '/([A-Z]\d+)[\-\.]?\s*DAİRE\s*(\d+)/i',

            // C3 16 gibi basit formatlar
            '/([A-Z]\d+)\s+(\d+)/',

            // B1D5, C2D13 gibi direkt formatlar
            '/([A-Z]\d+D\d+)/i',

            // Özel durumlar: C2- Daire:15
            '/([A-Z]\d+)\-?\s*Daire\s*\:?\s*(\d+)/i',

            // Blok ve daire farklı konumda: BLOK C5 DAİRE 9
            '/(?:BLOK|Blok)\s*([A-Z]\d+).*?(?:DAİRE|Daire)\s*(\d+)/i',

            // Daire kelimesi önce: DAİRE 9 BLOK C5
            '/(?:DAİRE|Daire)\s*(\d+).*?(?:BLOK|Blok)\s*([A-Z]\d+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $description, $matches)) {
                // Eğer direkt B1D5 formatında eşleşme varsa
                if (isset($matches[1]) && preg_match('/^[A-Z]\d+D\d+$/i', $matches[1])) {
                    return strtoupper($matches[1]);
                }

                // Normalde blok ve daire ayrı eşleşir
                if (isset($matches[1]) && isset($matches[2])) {
                    return strtoupper(trim($matches[1])) . 'D' . trim($matches[2]);
                }
            }
        }

        // Özel durumlar için manuel kontrol
        $specialCases = [
            'C5 16 numara asansör bakım ücreti' => 'C5D16',
            'B3 BLOK DAİRE5' => 'B3D5',
            'B3 Blok Daire 10' => 'B3D10',
            'b1 blok d 17' => 'B1D17',
            'C2. D.13' => 'C2D13',
            'C3 16' => 'C3D16',
        ];

        foreach ($specialCases as $case => $code) {
            if (strpos($description, $case) !== false) {
                return $code;
            }
        }

        // Son çare: sayısal bloklar için (A1, B2 gibi olmayanlar)
        if (preg_match('/(\d+)\s*(?:BLOK|Blok|blok)?\s*(?:DAİRE|Daire|DAiRE|D\.?)\s*[:\.\-]?\s*(\d+)/i', $description, $matches)) {
            return 'BLOCK' . trim($matches[1]) . 'D' . trim($matches[2]);
        }

        return null;
    }


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

        // Gün farkını hesapla
        $days = ($end - $start) / (60 * 60 * 24);
        if ($days <= 0) {
            return 0; // Geçersiz gün farkı
        }

        // Gün bazında tutarı hesapla
        $daily_amount = $amount / $days;
        $param_days = ($param - $start) / (60 * 60 * 24);

        return round($daily_amount * $param_days, 2); // İstenen tarihe göre tutarı döner
    }
}
