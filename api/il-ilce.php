<?php
require_once '../Database/require.php';
require_once '../App/Helper/cities.php';

use App\Helper\Cities;



$cities = new Cities(); 

if(isset($_POST['city_id']) && is_numeric($_POST['city_id'])) {
    echo $cities->getCityTowns($_POST['city_id']);
}