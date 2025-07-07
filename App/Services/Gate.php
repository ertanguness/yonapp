<?php

namespace App\Services;

use App\Controllers\AuthController;
use Model\PermissionsModel;

class Gate
{
    /**
     * Mevcut kullanıcının belirli bir role sahip olup olmadığını kontrol eder.
     * 
     * @param string|array $roles Kontrol edilecek rol(ler)in adı. 
     *                            Tek bir rol için string, birden fazla rol için dizi verilebilir.
     * @return bool Kullanıcı belirtilen rollerden birine sahipse true, değilse false döner.
     */
    public static function hasRole(string|array $roles): bool
    {
        $user = AuthController::user();
        if (!$user || !isset($user->role_name)) {
            return false;
        }

        $userRole = $user->role_name;

        if (is_array($roles)) {
            // Eğer bir rol dizisi verilmişse, kullanıcının rolü bu dizide var mı diye bak.
            return in_array($userRole, $roles);
        }
        
        // Eğer tek bir rol (string) verilmişse, doğrudan karşılaştır.
        return $userRole === $roles;
    }

    /**
     * Mevcut kullanıcının belirli bir izne (permission) sahip olup olmadığını kontrol eder.
     * Bu metot, rolün sahip olduğu izinleri kontrol eder.
     * 
     * @param string $permissionName Kontrol edilecek iznin adı (örn: 'kullanici_ekle').
     * @return bool Kullanıcının izni varsa true, yoksa false döner.
     */
    public static function allows(string $permissionName): bool
    {
        $user = AuthController::user();
        if (!$user) {
            return false;
        }

        // Süper admin her şeye izinli olmalı (örn: rol adı 'Super Admin' ise)
        // Bu, veritabanına sürekli sorgu atmayı engeller.
        if (isset($user->role_name) && $user->role_name === 'Super Admin') {
            return true;
        }

        // Kullanıcının izinlerini alalım.
        $permissionModel = new PermissionsModel();
        // Bu metodu bir sonraki adımda güncelleyeceğiz.
        $userPermissions = $permissionModel->getPermissionsForUser($user->id); 

        return in_array($permissionName, $userPermissions);
    }


    
    /**
     * Belirtilen izni kontrol eder. Eğer kullanıcının izni yoksa,
     * bir uyarı mesajı basar ve betiği sonlandırır (exit).
     * Bu metot, bir sayfanın veya işlemin en başında "gatekeeper" (kapı bekçisi)
     * olarak kullanılmak üzere tasarlanmıştır.
     * 
     * @param string $permissionName Gerekli olan iznin adı (örn: 'kullanici_ekle').
     * @param string|null $customMessage (İsteğe bağlı) Varsayılan mesaj yerine gösterilecek özel HTML mesajı.
     * @return void Metot, izin varsa hiçbir şey yapmaz, yoksa betiği sonlandırır.
     */
    public static function authorizeOrDie(string $permissionName, ?string $customMessage = null): void
    {
        // Temel yetki kontrolünü `allows()` metodu ile yapıyoruz.
        // Bu, kod tekrarını önler.
        if (self::allows($permissionName)) {
            // Yetkisi var, hiçbir şey yapma ve kodun akışına devam etmesine izin ver.
            return;
        }

        // --- YETKİ YOKSA BURADAN AŞAĞISI ÇALIŞIR ---
        
        // Loglama yapmak iyi bir pratiktir.
        $user = AuthController::user();
        \getLogger()->warning("Yetkisiz erişim denemesi engellendi.", [
            'user_id' => $user->id ?? 'Bilinmiyor',
            'email' => $user->email ?? 'Giriş yapılmamış',
            'ip' => $_SERVER['REMOTE_ADDR'],
            'required_permission' => $permissionName,
            'url' => $_SERVER['REQUEST_URI']
        ]);
        
        // Gösterilecek mesajı belirle.
        if ($customMessage == null) {
                  $customMessage = "Bu işlemi gerçekleştirmek veya bu sayfayı görüntülemek için gerekli yetkiye sahip değilsiniz.";
         
        };
        echo "
        <div class='alert alert-danger p-4 mt-3' role='alert'>
            <h5 class='alert-heading'><i class='feather-slash me-2'></i>Yetkisiz Erişim</h5>
            <p>
            {$customMessage}
            </p>
            <hr>
            <p class='mb-0'>Lütfen sistem yöneticinizle iletişime geçin.</p>
        </div>
        ";
        
        // Betiğin daha fazla çalışmasını engelle.
        // Genellikle bir sayfanın altındaki footer veya diğer bileşenlerin
        // yüklenmesini önlemek için bu gereklidir.
        exit;
    }
}