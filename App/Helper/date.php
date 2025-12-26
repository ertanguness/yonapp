<?php

namespace App\Helper;

use DateTime;
use PhpOffice\PhpSpreadsheet\Shared\Date as PhpSpreadsheetDate;



class Date
{
public static function dmY($date = null, $format = 'd.m.Y')
{
    // Boş, null veya 0000-00-00 kontrolü
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return '';
    }
    
    $timestamp = strtotime($date);
    
    // strtotime başarısız olursa (false döndürürse)
    if ($timestamp === false) {
        return '';
    }
    
    return date($format, $timestamp);
}
   public static function dmYHIS(string|int|float|null $input, ?\DateTimeZone $tz = null): string
    {
        if ($input === null || $input === '' || $input === '0') {
            return '';
        }
        return self::parseToFormat($input, 'd.m.Y H:i:s', $tz);
    }

   // 'Y-m-d' çıktısı
    public static function Ymd(string|int|float|null $input, ?\DateTimeZone $tz = null): string
    {
        /** boş gelirse return */
       if ($input === null || $input === '' || $input === '0') {
           return '';
       }
        return self::parseToFormat($input, 'Y-m-d', $tz);
    }

    public static function YmdHIS(string|int|float|null $input, ?\DateTimeZone $tz = null): string
    {
        if ($input === null || $input === '' || $input === '0') {
            return '';
        }
        return self::parseToFormat($input, 'Y-m-d H:i:s', $tz);
    }

    /** Today=>Bugünün tarihi */
    public static function today($format = 'd.m.Y')
    {
        return date($format);
    }

    public static function now($format = 'Y-m-d H:i:s')
    {
        return date($format);
    }


    public static function nowTr($format = 'Y-m-d H:i:s')
    {
        return date($format);
    }
 public static function parseExcelDate($val)
    {
        $s = trim((string)($val ?? ''));
        if ($s === '') return null;

        // Eğer Excel seri numarası gelmişse (sayısal)
        if (is_numeric($s)) {
            try {
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$s);
                return $dt->format(preg_match('/:\d{2}:\d{2}/', $s) ? 'Y-m-d H:i:s' : 'Y-m-d H:i');
            } catch (\Throwable $e) {
            }
        }

        // Temizle: gereksiz karakterler
        $s = preg_replace('/[^0-9\.\-:\s\/]/u', '', $s);
        // Tarih-saat arası tire ise boşluğa çevir (04/12/2025-16:13:53 → 04/12/2025 16:13:53)
        $s = preg_replace('/([\/.])(\d{4})-/', '$1$2 ', $s);

        $outFmt = preg_match('/:\d{2}:\d{2}/', $s) ? 'Y-m-d H:i:s' : 'Y-m-d H:i';

        $formats = [
            'd/m/Y H:i:s',
            'd/m/Y H:i',
            'd.m.Y H:i:s',
            'd.m.Y H:i',
            'd-m-Y H:i:s',
            'd-m-Y H:i',
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y/m/d H:i:s',
            'Y/m/d H:i',
            'd/m/Y',
            'd.m.Y',
            'd-m-Y',
            'Y-m-d',
            'Y/m/d',
        ];

        foreach ($formats as $fmt) {
            $dt = \DateTime::createFromFormat($fmt, $s);
            if ($dt !== false) {
                return $dt->format($outFmt);
            }
        }

        // Son çare: strtotime — US yorumu yapabilir, bu yüzden sadece çok net durumlarda kullanın
        $ts = strtotime($s);
        if ($ts !== false && $ts > 0) {
            return date($outFmt, $ts);
        }

        return null;
    }

    

    /**
     * Farklı yerel tarih formatlarını güvenle çözüp verilen çıktıya dönüştürür.
     * Desteklenen örnekler:
     * - 11/02/2025-12:39:03  (d/m/Y-H:i:s)
     * - 11/02/2025 12:39     (d/m/Y H:i)
     * - 11.02.2025 12:39:03  (d.m.Y H:i:s)
     * - 2025-02-11 12:39:03  (Y-m-d H:i:s)
     * - 11-02-2025           (d-m-Y)
     * - Excel seri sayı (ör: 45567.5)
     */
    private static function parseToFormat(string|int|float $input, string $outputFormat, ?\DateTimeZone $tz = null): string
    {
        $tz = $tz ?: new \DateTimeZone(date_default_timezone_get() ?: 'Europe/Istanbul');

        // Excel seri sayı ise dönüştür (1899-12-30 epoch)
        if (is_numeric($input)) {
            $serial = (float)$input;
            $timestamp = (int)(($serial - 25569) * 86400);
            $dt = (new \DateTime('@' . $timestamp))->setTimezone($tz);
            return $dt->format($outputFormat);
        }

        $raw = trim((string)$input);

        // Tarih-saat arası tire kullanılmışsa (11/02/2025-12:39:03) boşluk yapalım ki formatlar kolay eşleşsin
    $norm = str_replace(['T'], ' ', $raw);
    // 11/02/2025-12:39:03 → 11/02/2025 12:39:03 (sadece tarih-saat ayracını düzelt)
    $norm = preg_replace('/(\b\d{2}[\/.\-]\d{2}[\/.\-]\d{4})-(\d{2}:\d{2}(?::\d{2})?\b)/', '$1 $2', $norm);
    // 2025-12-19-13:40:20 → 2025-12-19 13:40:20 (ISO tarih + saat arasında tek tire gelirse)
    $norm = preg_replace('/(\b\d{4}-\d{2}-\d{2})-(\d{2}:\d{2}(?::\d{2})?\b)/', '$1 $2', $norm);
    // Eski regex'in yaptığı şeyi daha güvenli şekilde yapalım: 04/12/2025-16:13:53 → 04/12/2025 16:13:53
    $norm = preg_replace('/([\/.])(\d{4})-/', '$1$2 ', $norm);

        // Denenecek formatlar (öncelik: Türkiye d/m/Y)
        $formats = [
            'd/m/Y H:i:s',
            'd/m/Y-H:i:s',
            'd/m/Y H:i',
            'd.m.Y H:i:s',
            'd.m.Y H:i',
            'd-m-Y H:i:s',
            'd-m-Y H:i',
            'd/m/Y',
            'd.m.Y',
            'd-m-Y',
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y-m-d',
            'Y/m/d H:i:s',
            'Y/m/d H:i',
            'Y/m/d',
        ];

        foreach ($formats as $fmt) {
            $dt = \DateTime::createFromFormat($fmt, $norm, $tz);
            if ($dt instanceof \DateTimeInterface) {
                // createFromFormat bazen hataları tolere eder; kontrol edelim
                $errors = \DateTime::getLastErrors();
                if (empty($errors['warning_count']) && empty($errors['error_count'])) {
                    return $dt->format($outputFormat);
                }
            }
        }

        // Son çare: strtotime (ABD yorumlayabilir; riskli)
        $ts = strtotime($raw);
        if ($ts !== false) {
            return (new \DateTime('@' . $ts))->setTimezone($tz)->format($outputFormat);
        }

        throw new \InvalidArgumentException("Tarih çözümlenemedi: {$input}");
    }



    public static function firstDay($month, $year)
    {
        return sprintf('%d%02d%02d', $year, $month, 1);
    }

    public static function lastDay($month, $year)
    {
        return sprintf(
            '%d%02d%02d',
            $year,
            $month,
            self::daysInMonth($month, $year),
        );
    }

    // Yarının tarihini d.m.Y formatında döndürür
    public static function getTomorrowDate($format = 'Ymd')
    {
        return date($format, strtotime('+1 day'));
    }

    public static function getDay($date = null, $leadingZero = true)
    {
        $format = $leadingZero ? 'd' : 'j';
        return $date ? date($format, strtotime($date)) : date($format);
    }

    public static function getMonth($date = null, $leadingZero = true)
    {
        $format = $leadingZero ? 'm' : 'n';
        return $date ? date($format, strtotime($date)) : date($format);
    }

    public static function getYear($date = null)
    {
        return $date ? date('Y', strtotime($date)) : date('Y');
    }

    public static function daysInMonth($month, $year)
    {
        return cal_days_in_month(CAL_GREGORIAN, $month, $year);
    }

    public static function generateDates($year, $month, $days)
    {
        $dateList = [];
        for ($day = 1; $day <= $days; $day++) {
            // Tarih formatını ayarlama (d.m.Y)
            $formattedDate = sprintf('%2d%02d%02d', $year, $month, $day);
            $dateList[] = $formattedDate;
        }
        return $dateList;
    }

    public static function isWeekend($date)
    {
        $dateTime = new \DateTime($date);
        $dayOfWeek = $dateTime->format('N');
        return ($dayOfWeek == 7);
    }

    public static function isDate($date)
    {
        return strtotime($date);
    }

    public static function isBetween($date, $startDate, $endDate)
    {
        $date = strtotime($date);
        $startDate = strtotime($startDate);
        $endDate = strtotime($endDate);
        return ($date >= $startDate && $date <= $endDate);
    }

    public static function isBefore($date, $compareDate)
    {
        $date = self::Ymd($date);
        $compareDate = self::Ymd($compareDate);
        return ($date < $compareDate);
    }

    public static function gunAdi($gun)
    {
        $gun = date('D', strtotime($gun));
        $gunler = array(
            'Mon' => 'Pzt',
            'Tue' => 'Sal',
            'Wed' => 'Çar',
            'Thu' => 'Per',
            'Fri' => 'Cum',
            'Sat' => 'Cmt',
            'Sun' => 'Paz'
        );
        return $gunler[$gun];
    }

    const MONTHS = [
        1 => 'Ocak',
        2 => 'Şubat',
        3 => 'Mart',
        4 => 'Nisan',
        5 => 'Mayıs',
        6 => 'Haziran',
        7 => 'Temmuz',
        8 => 'Ağustos',
        9 => 'Eylül',
        10 => 'Ekim',
        11 => 'Kasım',
        12 => 'Aralık'
    ];

    public static function monthName($month)
    {
        // 09 şeklinde gelen ayları 9 şekline çevir
        $month = ltrim($month, '0');
        return self::MONTHS[$month];
    }

    public static function getMonthsSelect(
        $name = 'months',
        $month = null,
    ) {
        if ($month == null) {
            $month = date('m');
        }
        $select = '<select name="' . $name . '" class="form-select select2" id="' . $name . '" style="width:100%">';
        $select .= '<option value="">Ay Seçiniz</option>';
        foreach (self::MONTHS as $key => $value) {
            $selected = $month == $key ? ' selected' : '';
            $select .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
        }
        $select .= '</select>';
        return $select;
    }

    public static function getYearsSelect(
        $name = 'years',
        $year = null,
    ) {
        if ($year == null) {
            $year = date('Y');
        }
        $select = '<select name="' . $name . '" class="form-select select2" id="' . $name . '" style="width:100%">';
        $select .= '<option value="">Yıl Seçiniz</option>';
        for ($i = 2021; $i <= 2030; $i++) {
            $selected = $year == $i ? ' selected' : '';
            $select .= '<option value="' . $i . '"' . $selected . '>' . $i . '</option>';
        }
        $select .= '</select>';
        return $select;
    }

    /**
     * İki tarih arasındaki gün farkını hesaplar.
     *
     * @param string $date1 İlk tarih (Y-m-d H:i:s formatında)
     * @param string $date2 İkinci tarih (Y-m-d H:i:s formatında - boş ise bugünün tarihi alınır)
     * @return int İki tarih arasındaki gün farkı
     */
    public static function getDateDiff($date1, $date2 = '')
    {
        // date2 boş ise bugünün tarihi alınır
        if ($date2 == '') {
            $date2 = date('Y-m-d H:i:s');
        }
        $datetime1 = new \DateTime($date1);
        $datetime2 = new \DateTime($date2);
        $interval = $datetime1->diff($datetime2);
        return (int) $interval->format('%a');
    }

    // Kalan günü hesaplar
    public static function getRemainingDays($date)
    {
        if ($date == null) {
            return '';
        }

        $today = date('Y-m-d');
        $date = date('Y-m-d', strtotime($date));
        $diff = strtotime($date) - strtotime($today);
        return floor($diff / (60 * 60 * 24));
    }




/**
 * Excel'den gelen sayısal bir tarih değerini istenen formatta döndürür.
 * @param mixed $dateValue Excel hücresinden gelen değer (sayı veya string)
 * @param string $format Çıktı formatı (Y-m-d H:i:s, timestamp, vs)
 * @return string|int|null Başarılı ise istenen format, değilse null
 */
public static function convertExcelDate($dateValue, $format = 'Y-m-d'): string|int|null
{
    if (empty($dateValue)) {
        return null;
    }

    // 1. Sayısal ise (Excel seri numarası: 45948.70138888889 gibi)
    if (is_numeric($dateValue)) {
        try {
            // TIMESTAMP İSTİYORSANIZ:
            if ($format === 'timestamp') {
                return PhpSpreadsheetDate::excelToTimestamp((float)$dateValue);
            }

            // TARİH STRING İSTİYORSANIZ (saat+dakika dahil):
            $dateTimeObject = PhpSpreadsheetDate::excelToDateTimeObject((float)$dateValue);
            return $dateTimeObject->format($format);

        } catch (\Exception $e) {
            return null;
        }
    }
    // 2. Metin ise (örn: "18.02.2025 10:41:41")
    if (is_string($dateValue)) {

        $raw = trim($dateValue);
        if ($raw === '') {
            return null;
        }

        // 2.a) Bazı kaynaklar tarih-saat arası '-' gönderiyor: 19/12/2025-13:40:20
        // Bunu güvenle boşluğa çevir (tarih içindeki '-' karakterlerine dokunma)
        $norm = preg_replace('/(\b\d{2}\/\d{2}\/\d{4})-(\d{2}:\d{2}(?::\d{2})?\b)/', '$1 $2', $raw);
        $norm = preg_replace('/(\b\d{2}\.\d{2}\.\d{4})-(\d{2}:\d{2}(?::\d{2})?\b)/', '$1 $2', $norm);
        $norm = preg_replace('/(\b\d{2}-\d{2}-\d{4})-(\d{2}:\d{2}(?::\d{2})?\b)/', '$1 $2', $norm);

        // 2.b) Locale/OS bağımsız normalize
        // - Tarihte '/' veya '-' kullanıldıysa '.' ile uyumlu hale getir (gün.ay.yıl formatı için)
        // - Birden fazla boşluğu tek boşluğa indir
        $norm = preg_replace('/\s+/', ' ', $norm);

        // 2.c) Önce en net formatları dene
        $knownFormats = [
            'd.m.Y H:i:s',
            'd.m.Y H:i',
            'Y-m-d H:i:s',
            'd/m/Y H:i:s',      // 🔥 BUNU EKLE
            'd/m/Y-H:i:s',      // 🔥 ASIL GELEN FORMAT
            'Y-m-d H:i',
            'd.m.Y',
            'Y-m-d',
            // Bazı Excel exportlarında saniye olmadan HH:MM:SS gelebilir veya sonuna nokta/virgül eklenebilir
            'd.m.Y H:i:s.u',
            'd.m.Y H:i.u',
        ];

        foreach ($knownFormats as $fmt) {
            $dt = \DateTime::createFromFormat($fmt, $norm);
            if ($dt instanceof \DateTimeInterface) {
                $errors = \DateTime::getLastErrors();
                if (empty($errors['warning_count']) && empty($errors['error_count'])) {
                    return ($format === 'timestamp') ? (int)$dt->format('U') : $dt->format($format);
                }
            }
        }

        // 2.d) Son çare: PHP'nin DateTime parser'ı (sunucuda locale farklarından dolayı riskli)
        // Buraya yalnızca ISO benzeri formatlar düştüğünde gelmesini istiyoruz.
        try {
            $dt = new \DateTime($norm);
            return ($format === 'timestamp') ? (int)$dt->format('U') : $dt->format($format);
        } catch (\Throwable $e) {
            return null;
        }
    }

    return null;
}
    
}
