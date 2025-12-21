<?php

require __DIR__ . '/../App/Router.php';

$r = new \App\Router();
$r->get('borc-odeme', 'test.php');

$url = 'borc-odeme?kisi=cuc83wpkJpOV0VweoHGjXblMJEjnwSQjGV3bLo0%252A&yd_q=muh';

$res = $r->resolve($url);

echo json_encode([
    'input' => $url,
    'page'  => $r->getPageName(),
    'params'=> $res['params'],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
