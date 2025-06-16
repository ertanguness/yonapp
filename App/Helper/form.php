<?php 

namespace App\Helper;

class Form
{
   
    /**
     * Gelen Değerlere göre Select2 elemanını oluşturur
     * * @param string $name Select elemanının adı
     * * @param array $options Select elemanının seçenekleri
     * * @param string $selected Seçili olan değer
     */
    public static function Select2($name, $options = [], $selected = null, $class = 'form-select select2 w-100')
    {
        // ID değeri verilmemişse, name değerini kullan
        $id = $id ?? $name;

        // Select başlangıcı
        $select = '<select id="' . htmlspecialchars($name) . 
                      '" name="' . htmlspecialchars($name) . 
                     '" class="' . htmlspecialchars($class) . '">';

        // Seçenekleri döngüyle ekle
        foreach ($options as $key => $value) {
            $selectedAttr = ($selected !== null && $selected == $key) ? 'selected' : '';
            $select .= "<option value='" . htmlspecialchars($key) . 
                            "' $selectedAttr>" . htmlspecialchars($value) . "</option>";
        }

        // Select bitişi
        $select .= '</select>';

        return $select;
    }


}