<?php


namespace App;

class Router
{
    private $routes = [];
    private $prefix = '';
    private $basePath = 'pages/';
    private $matchedPattern = null; // Eşleşen desen burada tutulacak

    // GET route ekle
    public function get($pattern, $callback)
    {
        if (is_string($callback)) {
            $callback = $this->basePath . $callback;
            $actualCallback = function () use ($callback) {
                // Parametreleri bu fonksiyona aktarmak için argümanları al
                $args = func_get_args();
                
                // Tüm parametreleri dinamik olarak tanımla
                if (!empty($args)) {
                    // İlk 10 parametreyi tanımla (gerektiği kadar artırabilirsiniz)
                    $id = $args[0] ?? null;
                    $detay_id = $args[1] ?? null;
                    $param3 = $args[2] ?? null;
                    $param4 = $args[3] ?? null;
                    $param5 = $args[4] ?? null;
                    
                    // Alternatif: Tüm parametreleri extract ile çıkar
                    // extract() kullanarak parametreleri değişken olarak tanımla
                    $paramNames = ['id', 'detay_id', 'param3', 'param4', 'param5'];
                    for ($i = 0; $i < count($args) && $i < count($paramNames); $i++) {
                        ${$paramNames[$i]} = $args[$i];
                    }
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
     * - 'borclandirma-kisi-ekle/{id}/{detay_id}' -> 'borclandirma-kisi-ekle'
     * - 'kasa/hareket/{guid}' -> 'kasa/hareket'
     * - 'tahsilatlar' -> 'tahsilatlar' (değişiklik olmaz)
     * 
     * @return string|null
     */
    public function getPageName()
    {
        if ($this->matchedPattern === null) {
            return null;
        }

        // Tüm parametreleri kaldır: /{parametre} formatındaki tüm kısımları temizle
        $pageName = preg_replace('/\/\{[^\/]+\}/', '', $this->matchedPattern);
        
        return $pageName;
    }
}