<?php
require_once __DIR__ . '/../configs/bootstrap.php';
$m = new \Model\DuyuruModel();
$rows = $m->all();
echo "Duyuru sayisi: ".count($rows)."\n";