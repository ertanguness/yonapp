<?php

namespace App;

class Router
{
    private $routes = [];
    private $prefix = '';
    private $basePath = 'pages/';
    private $matchedPattern = null; // Eşleşen desen burada tutulacak

    // get, setBasePath, prefix, group gibi diğer metotlarınız aynı kalabilir...

    // GET route ekle
    public function get($pattern, $callback)
    {
        if (is_string($callback)) {
            $callback = $this->basePath . $callback;
            $actualCallback = function () use ($callback) {
                // Parametreleri bu fonksiyona aktarmak için argümanları al
                $args = func_get_args();
                // Değişkenleri yerel kapsama dahil et, böylece require edilen dosyada kullanılabilirler.
                // Örneğin, $id değişkeni $args[0] içinde olacak.
                if (!empty($args)) {
                    // Bu örnekte sadece tek parametre ($id) varsayıyoruz, 
                    // daha karmaşık durumlar için farklı bir yapı gerekebilir.
                    // En yaygın kullanım:
                    $id = $args[0];
                }
                require $callback;
            };
        } else {
            $actualCallback = $callback;
        }

        $fullPattern = $this->prefix ? $this->prefix . '/' . ltrim($pattern, '/') : $pattern;
        $this->routes[] = ['pattern' => $fullPattern, 'callback' => $actualCallback];
        return $this;
    }

    /**
     * URL'yi analiz eder ve eşleşen rotanın bilgilerini döndürür.
     * Bu metot içeriği çalıştırmaz, sadece tespit eder.
     */
    public function resolve($url)
    {
        foreach ($this->routes as $route) {
            // Deseni regex'e çevir: 'kasa-hareketleri/{id}' -> '@^kasa-hareketleri/([^/]+)$@'
            $pattern = "@^" . preg_replace('/\{([^\/]+)\}/', '([^/]+)', $route['pattern']) . "$@";
            // echo "Checking pattern: " . $pattern . "\n";

            if (preg_match($pattern, $url, $matches)) {
                // EŞLEŞME BULUNDU! Orijinal deseni saklayalım.
                $this->matchedPattern = $route['pattern'];

                array_shift($matches); // Tam URL eşleşmesini kaldır

                return [
                    'callback' => $route['callback'],
                    'params'   => $matches
                ];
            }
        }

        // Hiçbir rota eşleşmedi
        $this->matchedPattern = '404';
        return [
            'callback' => function () {
                http_response_code(404);
                require 'pages/404.php';
            },
            'params'   => []
        ];
    }



    /**
     * YENİ VE ANAHTAR METOT:
     * Eşleşen desenin dinamik kısımlarını atarak temiz sayfa adını döndürür.
     * 
     * Örnekler:
     * - 'kasa-hareketleri/{id}' -> 'kasa-hareketleri'
     * - 'kasa/hareket/{guid}'   -> 'kasa/hareket'
     * - 'tahsilatlar'            -> 'tahsilatlar' (değişiklik olmaz)
     * 
     * @return string|null
     */
    public function getPageName()
    {
        if ($this->matchedPattern === null) {
            return null;
        }

        // Gelen veriyi görelim
        //echo "GİRDİ: " . $this->matchedPattern . "<br>";

        // preg_replace işleminin sonucunu görelim
        $pageName = preg_replace('/\/\{[^\/]+\}$/', '', $this->matchedPattern);
        // echo "İŞLEM SONUCU (\$pageName): " . $pageName . "<br>";

        // Fonksiyonun ne döndürdüğünü görelim ve duralım
        //die("DÖNDÜRÜLEN DEĞER: " . $pageName);

        return $pageName ;
    }
}
