<?php 


namespace App\Helper;
use App\Helper\Security;
use PDO;


class Arac 
{

  const ARACMARKA = [
    "AUDI" => "Audi",
    "BMW" => "BMW",
    "MERCEDES" => "Mercedes-Benz",
    "VOLKSWAGEN" => "Volkswagen",
    "RENAULT" => "Renault",
    "FIAT" => "Fiat",
    "FORD" => "Ford",
    "HYUNDAI" => "Hyundai",
    "TOYOTA" => "Toyota",
    "PEUGEOT" => "Peugeot",
    "OPEL" => "Opel",
    "HONDA" => "Honda",
    "DACIA" => "Dacia",
    "NISSAN" => "Nissan",
    "KIA" => "Kia",
    "SEAT" => "Seat",
    "SKODA" => "Skoda",
    "VOLVO" => "Volvo",
    "CITROEN" => "Citroën",
    "TESLA" => "Tesla",
    "JEEP" => "Jeep",
    "MITSUBISHI" => "Mitsubishi",
    "SUBARU" => "Subaru",
    "MAZDA" => "Mazda",
    "MINI" => "Mini",
    "PORSCHE" => "Porsche",
    "LANDROVER" => "Land Rover",
    "JAGUAR" => "Jaguar",
    "SUZUKI" => "Suzuki"
];


/** Araç Marka için select oluştur */
public static function AracMarkaSelect($name, $selected = null)
{
    $options = '';
    foreach (self::ARACMARKA as $key => $value) {
        $isSelected = ($key === $selected) ? 'selected' : '';
        $options .= "<option value=\"$key\" $isSelected>$value</option>";
    }
    return "<select name=\"$name\" id=\"$name\" class=\"form-select select2\">$options</select>";
}

}