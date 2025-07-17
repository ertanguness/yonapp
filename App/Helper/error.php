<?php

namespace App\Helper;


class Error 
{
    /**
     * PDOException hatalarını kontrol eder ve uygun bir mesaj döndürür.
     *
     * @param \PDOException $exception PDOException nesnesi
     * @return array Hata durumunu ve mesajını içeren bir dizi
     */
    public static function handlePDOException(\PDOException $exception)
    {
        if (strpos($exception->getMessage(), 'SQLSTATE[23000]') !== false && strpos($exception->getMessage(), '1451') !== false) {
            return [
                "status" => "error",
                "message" => "Bu kayıt, başka bir tabloyla ilişkili olduğu için silinemez.",
            ];
        }

        // Diğer hatalar için genel bir mesaj döndür
        return [
            "status" => "error",
            "message" => "Hata: " . $exception->getMessage(),
        ];
    }

    /**notFound
     * gelen değer 404 ise sayfa bulunamadı mesajı döndürür.
     */
    public static function notFound()
    {
        return [
            "status" => "error",
            "message" => "Sayfa bulunamadı.",
        ];
       die();
    }

}