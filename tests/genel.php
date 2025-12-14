<?php
require_once dirname(__DIR__, levels: 1) . '/configs/bootstrap.php';

use App\Helper\Helper;
use Model\KisiKredileriModel;
use Model\BorclandirmaDetayModel;
use Model\DefinesModel;


$KisiKredileri = new KisiKredileriModel();
$BorclandirmaDetay = new BorclandirmaDetayModel();
$Defines = new DefinesModel();


$kalemler = $Defines->getGelirGiderKalemleri(6, "AİDAT");

Helper::dd($kalemler);