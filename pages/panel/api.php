<?php
if (!headers_sent()) { header('Content-Type: application/json; charset=utf-8'); }
require_once dirname(__DIR__, 2) . '/configs/bootstrap.php';

use Model\SitelerModel;
use Model\DairelerModel;
use Model\UserModel;
use App\Helper\Cities;
use Model\UserSitePricingModel;
use Model\UserSiteBillingModel;
use Model\UserAccessLockModel;
use Model\SettingsModel;

$action = $_POST['action'] ?? '';

if ($action === 'creator_sites') {
    $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    if ($userId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz kullanıcı ID']);
        exit;
    }
    $Siteler = new SitelerModel();
    $Users = new UserModel();
    $cities = new Cities();
    $Pricing = new UserSitePricingModel();
    $Billing = new UserSiteBillingModel();
    $LockUser = new UserAccessLockModel();
    $LockSite = new \Model\UserSiteLockModel();
    $creator = $Users->getUser($userId);
    if (!$creator) {
        echo json_encode(['status' => 'error', 'message' => 'Kullanıcı bulunamadı']);
        exit;
    }
    $sites = $Siteler->getCreatorSitesWithApartmentCount($userId);
    $creatorSitesOut = [];
    foreach ($sites as $s) {
        try { $pricing = $Pricing->getByUserAndSite($userId, (int)$s->id); } catch (\Throwable $e) { $pricing = null; }
        $unitFee = $pricing ? (float)$pricing->unit_fee : 0.0;
        $startDate = $pricing ? ($pricing->start_date ?: null) : null;
        $dueDay = $pricing ? (int)($pricing->due_day ?? 0) : 0;
        $graceDays = $pricing ? (int)($pricing->grace_days ?? 0) : 0;
        $monthlyTotal = $unitFee * (int)($s->apartment_count ?? 0);
        $schedule = [];
        if ($startDate) {
            try {
                $start = new \DateTime($startDate);
                $cur = new \DateTime();
                $periods = new \DatePeriod($start, new \DateInterval('P1M'), $cur->modify('first day of next month'));
                $cnt = 0;
                foreach ($periods as $p) {
                    if ($cnt >= 12) break;
                    $ym = $p->format('Y-m');
                    $paid = false;
                    try {
                        $paid = $Billing->isPaid($userId, (int)$s->id, $ym);
                    } catch (\Throwable $e) {
                        $paid = false;
                    }
                    $schedule[] = ['period' => $ym, 'amount' => round($monthlyTotal,2), 'paid' => $paid ? 1 : 0];
                    $cnt++;
                }
            } catch (\Throwable $e) {
                $schedule = [];
            }
        }
        $siteLocked = 0; 
        try { $siteLocked = $LockSite->isLocked($userId, (int)$s->id) ? 1 : 0; } catch (\Throwable $e) { $siteLocked = 0; }
        $creatorSitesOut[] = [
            'id' => (int)$s->id,
            'site_adi' => $s->site_adi ?? '',
            'il' => $s->il ?? '',
            'ilce' => $s->ilce ?? '',
            'il_ad' => $cities->getCityName($s->il ?? 0),
            'ilce_ad' => $cities->getTownName($s->ilce ?? 0),
            'telefon' => $s->telefon ?? '',
            'eposta' => $s->eposta ?? '',
            'tam_adres' => $s->tam_adres ?? '',
            'kayit_tarihi' => $s->kayit_tarihi ?? '',
            'aktif_mi' => (int)($s->aktif_mi ?? 0),
            'apartment_count' => (int)($s->apartment_count ?? 0),
            'unit_fee' => round($unitFee,2),
            'start_date' => $startDate,
            'monthly_total' => round($monthlyTotal,2),
            'due_day' => $dueDay ?: null,
            'grace_days' => $graceDays ?: null,
            'schedule' => $schedule,
            'locked' => $siteLocked
        ];
    }
    echo json_encode([
        'status' => 'success',
        'data' => [
            'creator' => [
                'id' => (int)$creator->id,
                'full_name' => $creator->full_name ?? ($creator->email ?? ''),
                'phone' => $creator->phone ?? '',
                'email' => $creator->email ?? ''
            ],
            'creator_site_count' => count($creatorSitesOut),
            'creator_sites' => $creatorSitesOut,
            'total_monthly' => array_sum(array_map(function($x){ return (float)$x['monthly_total']; }, $creatorSitesOut)),
            'locked' => (int)$LockUser->getLockStatusByUser($userId)
        ]
    ]);
    exit;
}

if ($action === 'site_detail') {
    $siteId = isset($_POST['site_id']) ? (int)$_POST['site_id'] : 0;
    if ($siteId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz site ID']);
        exit;
    }
    $Siteler = new SitelerModel();
    $Daireler = new DairelerModel();
    $Users = new UserModel();
    $site = $Siteler->SiteBilgileri($siteId);
    if (!$site) {
        echo json_encode(['status' => 'error', 'message' => 'Site bulunamadı']);
        exit;
    }
    $apartmentCount = $Daireler->countBySiteId($siteId);
    $creatorId = (int)($site->user_id ?? 0);
    $creator = $creatorId > 0 ? $Users->getUser($creatorId) : null;
    $creatorSites = $creatorId > 0 ? $Siteler->getCreatorSitesWithApartmentCount($creatorId) : [];

    // City/Town names
    $cities = new Cities();
    $siteCityName = $cities->getCityName($site->il ?? 0);
    $siteTownName = $cities->getTownName($site->ilce ?? 0);

    $creatorSitesOut = [];
    foreach ($creatorSites as $s) {
        $creatorSitesOut[] = [
            'id' => (int)$s->id,
            'site_adi' => $s->site_adi ?? '',
            'il' => $s->il ?? '',
            'ilce' => $s->ilce ?? '',
            'il_ad' => $cities->getCityName($s->il ?? 0),
            'ilce_ad' => $cities->getTownName($s->ilce ?? 0),
            'telefon' => $s->telefon ?? '',
            'eposta' => $s->eposta ?? '',
            'tam_adres' => $s->tam_adres ?? '',
            'kayit_tarihi' => $s->kayit_tarihi ?? '',
            'aktif_mi' => (int)($s->aktif_mi ?? 0),
            'apartment_count' => (int)($s->apartment_count ?? 0),
        ];
    }
    $res = [
        'status' => 'success',
        'data' => [
            'id' => (int)$site->id,
            'site_adi' => $site->site_adi ?? '',
            'il' => $site->il ?? '',
            'ilce' => $site->ilce ?? '',
            'il_ad' => $siteCityName,
            'ilce_ad' => $siteTownName,
            'telefon' => $site->telefon ?? '',
            'eposta' => $site->eposta ?? '',
            'tam_adres' => $site->tam_adres ?? '',
            'apartment_count' => $apartmentCount,
            'creator' => [
                'id' => $creator ? (int)$creator->id : 0,
                'full_name' => $creator->full_name ?? ($creator->email ?? ''),
                'phone' => $creator->phone ?? '',
                'email' => $creator->email ?? ''
            ],
            'creator_site_count' => count($creatorSites),
            'creator_sites' => $creatorSitesOut
        ]
    ];
    echo json_encode($res);
    exit;
}

if ($action === 'pricing_set') {
    $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $siteId = isset($_POST['site_id']) ? (int)$_POST['site_id'] : 0;
    $unitFee = isset($_POST['unit_fee']) ? (float)$_POST['unit_fee'] : 0.0;
    $startDate = isset($_POST['start_date']) ? ($_POST['start_date'] ?: null) : null;
    $dueDay = isset($_POST['due_day']) && $_POST['due_day'] !== '' ? (int)$_POST['due_day'] : null;
    $graceDays = isset($_POST['grace_days']) && $_POST['grace_days'] !== '' ? (int)$_POST['grace_days'] : null;
    if ($userId <= 0 || $siteId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz veri']);
        exit;
    }
    $Pricing = new UserSitePricingModel();
    $ok = $Pricing->upsert($userId, $siteId, $unitFee, $startDate, $dueDay, $graceDays);
    echo json_encode(['status' => $ok ? 'success' : 'error']);
    exit;
}

if ($action === 'billing_mark') {
    $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $siteId = isset($_POST['site_id']) ? (int)$_POST['site_id'] : 0;
    $period = isset($_POST['period']) ? $_POST['period'] : '';
    $paid = isset($_POST['paid']) ? (int)$_POST['paid'] : 0;
    $amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0.0;
    if ($userId <= 0 || $siteId <= 0 || !$period) {
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz veri']);
        exit;
    }
    $Billing = new UserSiteBillingModel();
    $ok = $Billing->mark($userId, $siteId, $period, $paid, $amount);
    echo json_encode(['status' => $ok ? 'success' : 'error']);
    exit;
}
if ($action === 'billing_bulk_mark') {
    try {
        $itemsJson = $_POST['items'] ?? '[]';
        $items = json_decode($itemsJson, true);
        if (!is_array($items) || empty($items)) { echo json_encode(['status'=>'error','message'=>'Eksik veri']); exit; }
        $Billing = new UserSiteBillingModel();
        $okAll = true;
        foreach ($items as $it) {
            $userId = (int)($it['user_id'] ?? 0);
            $siteId = (int)($it['site_id'] ?? 0);
            $period = $it['period'] ?? '';
            $amount = (float)($it['amount'] ?? 0);
            $paid = (int)($it['paid'] ?? 1);
            if ($userId<=0 || $siteId<=0 || !$period) { $okAll = false; continue; }
            $ok = $Billing->mark($userId, $siteId, $period, $paid, $amount);
            $okAll = $okAll && $ok;
        }
        echo json_encode(['status'=>$okAll?'success':'error']);
    } catch (\Throwable $e) {
        echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
    }
    exit;
}

if ($action === 'lock_toggle') {
    try {
        $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
        $siteId = isset($_POST['site_id']) ? (int)$_POST['site_id'] : 0;
        $lock = isset($_POST['lock']) ? (int)$_POST['lock'] : 0;
        $reason = isset($_POST['reason']) ? $_POST['reason'] : null;
        if ($userId <= 0) { echo json_encode(['status' => 'error', 'message' => 'Geçersiz kullanıcı']); exit; }
        try {
            $db = \getDbConnection();
            $db->exec("CREATE TABLE IF NOT EXISTS `user_site_locks` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `user_id` int(11) NOT NULL,
              `site_id` int(11) NOT NULL,
              `locked` tinyint(1) NOT NULL DEFAULT 0,
              `reason` varchar(255) DEFAULT NULL,
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`id`),
              KEY `user_site_lock_idx` (`user_id`,`site_id`,`locked`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;");
        } catch (\Throwable $e) {}
        if ($siteId > 0) {
            $Lock = new \Model\UserSiteLockModel();
            $ok = $Lock->setLock($userId, $siteId, $lock, $reason);
            echo json_encode(['status' => $ok ? 'success' : 'error', 'locked' => $lock ? 1 : 0, 'scope' => 'site']);
        } else {
            $Lock = new UserAccessLockModel();
            $ok = $Lock->setLock($userId, $lock, $reason);
            echo json_encode(['status' => $ok ? 'success' : 'error', 'locked' => $lock ? 1 : 0, 'scope' => 'user']);
        }
    } catch (\Throwable $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'site_lock_status') {
    $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $siteId = isset($_POST['site_id']) ? (int)$_POST['site_id'] : 0;
    if ($userId <= 0 || $siteId <= 0) { echo json_encode(['status' => 'error', 'message' => 'Geçersiz veri']); exit; }
    $Siteler = new SitelerModel();
    $Pricing = new UserSitePricingModel();
    $Billing = new UserSiteBillingModel();
    $Lock = new \Model\UserSiteLockModel();
    $site = $Siteler->SiteBilgileri($siteId);
    if (!$site) { echo json_encode(['status' => 'error', 'message' => 'Site bulunamadı']); exit; }
    $locked = $Lock->isLocked($userId, $siteId) ? 1 : 0;
    $pricing = null; try { $pricing = $Pricing->getByUserAndSite($userId, $siteId); } catch (\Throwable $e) {}
    $unitFee = $pricing ? (float)$pricing->unit_fee : 0.0;
    $startDate = $pricing ? ($pricing->start_date ?: null) : null;
    $unpaidMonths = [];
    $totalDebt = 0.0;
    $dueDay = null; $graceDays = null;
    try { $pricing = $Pricing->getByUserAndSite($userId, $siteId); $dueDay = $pricing ? ($pricing->due_day ?? null) : null; $graceDays = $pricing ? ($pricing->grace_days ?? null) : null; } catch (\Throwable $e) {}
    if ($startDate) {
        try {
            $start = new \DateTime($startDate);
            $cur = new \DateTime();
            $periods = new \DatePeriod($start, new \DateInterval('P1M'), $cur->modify('first day of next month'));
            $now = new \DateTime();
            $shouldAutoLock = false;
            foreach ($periods as $p) {
                $ym = $p->format('Y-m');
                $paid = false; try { $paid = $Billing->isPaid($userId, $siteId, $ym); } catch (\Throwable $e) { $paid = false; }
                if (!$paid) {
                    $amount = $unitFee; // amount per apartment will be multiplied by count; we need apartment count:
                    $apartmentCount = 0;
                    try {
                        $apartmentCount = (int)(new \Model\DairelerModel())->countBySiteId($siteId);
                    } catch (\Throwable $e) { $apartmentCount = 0; }
                    $amount = $unitFee * $apartmentCount;
                    $unpaidMonths[] = ['period' => $ym, 'amount' => round($amount,2)];
                    $totalDebt += $amount;
                    if ($dueDay && $graceDays && $graceDays > 0) {
                        $year = (int)$p->format('Y'); $month = (int)$p->format('m');
                        $lastDay = (int)(new \DateTime("$year-$month-01"))->format('t');
                        $day = min((int)$dueDay, $lastDay);
                        $dueDate = new \DateTime("$year-$month-$day");
                        $dueDate->setTime(23,59,59);
                        $lockDate = (clone $dueDate)->modify("+{$graceDays} days");
                        if ($now > $lockDate) { $shouldAutoLock = true; }
                    }
                }
            }
            if ($shouldAutoLock) {
                try { (new \Model\UserSiteLockModel())->setLock($userId, $siteId, 1, 'Otomatik kilitleme: gecikme süresi aşıldı'); } catch (\Throwable $e) {}
            }
        } catch (\Throwable $e) {}
    }
    $locked = $Lock->isLocked($userId, $siteId) ? 1 : 0;
    echo json_encode(['status' => 'success', 'data' => [
        'locked' => $locked,
        'site_name' => $site->site_adi ?? '',
        'unpaid_months' => $unpaidMonths,
        'total_debt' => round($totalDebt,2),
    ]]);
    exit;
}
if ($action === 'settings_set') {
    $emailFrom = $_POST['email_from'] ?? null;
    $smsSender = $_POST['sms_sender'] ?? null;
    $emailTemplate = $_POST['email_template'] ?? null;
    $smsTemplate = $_POST['sms_template'] ?? null;
    $waUrl = $_POST['whatsapp_api_url'] ?? null;
    $waToken = $_POST['whatsapp_token'] ?? null;
    $waTemplate = $_POST['whatsapp_template'] ?? null;
    try {
        $db = \getDbConnection();
        $db->exec("CREATE TABLE IF NOT EXISTS superadmin_notify_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email_from VARCHAR(255),
            sms_sender VARCHAR(100),
            email_template TEXT,
            sms_template TEXT,
            whatsapp_api_url VARCHAR(255),
            whatsapp_token VARCHAR(255),
            whatsapp_template TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        $stmt = $db->query("SELECT id FROM superadmin_notify_settings LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        if ($row) {
            $up = $db->prepare("UPDATE superadmin_notify_settings SET email_from=?, sms_sender=?, email_template=?, sms_template=?, whatsapp_api_url=?, whatsapp_token=?, whatsapp_template=? WHERE id=?");
            $ok = $up->execute([$emailFrom, $smsSender, $emailTemplate, $smsTemplate, $waUrl, $waToken, $waTemplate, $row->id]);
            echo json_encode(['status' => $ok ? 'success' : 'error']);
        } else {
            $ins = $db->prepare("INSERT INTO superadmin_notify_settings (email_from, sms_sender, email_template, sms_template, whatsapp_api_url, whatsapp_token, whatsapp_template) VALUES (?,?,?,?,?,?,?)");
            $ok = $ins->execute([$emailFrom, $smsSender, $emailTemplate, $smsTemplate, $waUrl, $waToken, $waTemplate]);
            echo json_encode(['status' => $ok ? 'success' : 'error']);
        }
    } catch (\Throwable $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'settings_set_pairs') {
    try {
        $siteId = $_SESSION['site_id'] ?? 0;
        $userId = $_SESSION['user_id'] ?? 0;
        $pairs = [
            'superadmin_email_from' => ['value' => ($_POST['email_from'] ?? ''), 'aciklama' => 'Süperadmin e-posta gönderen'],
            'superadmin_email_template' => ['value' => ($_POST['email_template'] ?? ''), 'aciklama' => 'Süperadmin e-posta şablonu'],
            'superadmin_sms_sender' => ['value' => ($_POST['sms_sender'] ?? ''), 'aciklama' => 'Süperadmin SMS başlığı'],
            'superadmin_sms_template' => ['value' => ($_POST['sms_template'] ?? ''), 'aciklama' => 'Süperadmin SMS şablonu'],
            'superadmin_whatsapp_api_url' => ['value' => ($_POST['whatsapp_api_url'] ?? ''), 'aciklama' => 'Süperadmin WhatsApp API URL'],
            'superadmin_whatsapp_token' => ['value' => ($_POST['whatsapp_token'] ?? ''), 'aciklama' => 'Süperadmin WhatsApp Token'],
            'superadmin_whatsapp_template' => ['value' => ($_POST['whatsapp_template'] ?? ''), 'aciklama' => 'Süperadmin WhatsApp şablonu'],
        ];
        $model = new SettingsModel();
        $ok = $model->upsertPairs((int)$siteId, (int)$userId, $pairs);
        echo json_encode(['status' => $ok > 0 ? 'success' : 'error']);
    } catch (\Throwable $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
/**
 * Temsilci Yönetimi
 */
if ($action === 'rep_list') {
    try {
        $db = \getDbConnection();
        try { $db->exec("ALTER TABLE users ADD COLUMN rep_iban VARCHAR(34) NULL"); } catch (\Throwable $e) {}
        $db->exec("CREATE TABLE IF NOT EXISTS representative_site_assignments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            representative_id INT NOT NULL,
            site_id INT NOT NULL,
            commission_rate DECIMAL(5,2) NOT NULL DEFAULT 25.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY rep_site_unique (representative_id, site_id)
        )");
        $ownerId = (int)($_SESSION['owner_id'] ?? 0);
        $stmt = $db->prepare("
            SELECT u.id, u.full_name, u.phone, u.email, u.rep_iban AS iban, u.created_at,
                   (SELECT COUNT(*) FROM representative_site_assignments a WHERE a.representative_id = u.id) AS assigned_count
            FROM users u
            LEFT JOIN user_roles r ON u.roles = r.id
            WHERE r.role_name = 'Temsilci' AND (r.owner_id = :owner OR r.owner_id IS NULL)
            ORDER BY u.full_name ASC
        ");
        $stmt->execute(['owner' => $ownerId]);
        $list = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        echo json_encode(['status' => 'success', 'data' => $list]);
    } catch (\Throwable $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
if ($action === 'rep_create') {
    try {
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = isset($_POST['phone']) ? preg_replace('/\D+/', '', $_POST['phone']) : null;
        $email = $_POST['email'] ?? null;
        $iban = isset($_POST['iban']) ? strtoupper(preg_replace('/\s+/', '', $_POST['iban'])) : null;
        $password = $_POST['password'] ?? null;
        if ($fullName === '') { echo json_encode(['status'=>'error','message'=>'Eksik isim']); exit; }
        if (!$phone || !preg_match('/^(0\d{10}|90\d{10})$/', $phone)) { echo json_encode(['status'=>'error','message'=>'Telefon formatı geçersiz']); exit; }
        if (preg_match('/^90\d{10}$/', $phone)) { $phone = '0' . substr($phone, 2); }
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) { echo json_encode(['status'=>'error','message'=>'E-posta formatı geçersiz']); exit; }
        if (!$iban) { echo json_encode(['status'=>'error','message'=>'IBAN zorunludur']); exit; }
        if (!preg_match('/^TR\d{24}$/', $iban)) { echo json_encode(['status'=>'error','message'=>'IBAN formatı geçersiz (TR + 24 rakam)']); exit; }
        if (!$password || strlen(trim($password)) < 6) {
            $password = bin2hex(random_bytes(4));
        }
        $db = \getDbConnection();
        try { $db->exec("ALTER TABLE users ADD COLUMN rep_iban VARCHAR(34) NULL"); } catch (\Throwable $e) {}
        $ownerId = (int)($_SESSION['owner_id'] ?? 0);
        $roleId = null;
        $rs = $db->prepare("SELECT id FROM user_roles WHERE role_name='Temsilci' AND (owner_id = :owner OR owner_id IS NULL) LIMIT 1");
        $rs->execute(['owner' => $ownerId]);
        $row = $rs->fetch(\PDO::FETCH_OBJ);
        if ($row) { $roleId = (int)$row->id; } else {
            $insRole = $db->prepare("INSERT INTO user_roles (owner_id, role_name, guncellenebilir) VALUES (:owner, 'Temsilci', 1)");
            $insRole->execute(['owner' => $ownerId]);
            $roleId = (int)$db->lastInsertId();
        }
        $existsStmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $existsStmt->execute([$email]);
        if ($existsStmt->fetch()) { echo json_encode(['status'=>'error','message'=>'Bu e-posta ile kullanıcı mevcut']); exit; }
        $passHash = \App\Helper\Security::generatePassword($password);
        $ins = $db->prepare("INSERT INTO users (owner_id, full_name, email, password, phone, roles, status, rep_iban, created_at) VALUES (?,?,?,?,?,?,1,?, NOW())");
        $ok = $ins->execute([$ownerId, $fullName, $email, $passHash, $phone, $roleId, $iban]);
        echo json_encode(['status' => $ok ? 'success' : 'error']);
    } catch (\Throwable $e) {
        echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
    }
    exit;
}
if ($action === 'rep_manage') {
    try {
        $repId = isset($_POST['rep_id']) ? (int)$_POST['rep_id'] : 0;
        if ($repId <= 0) { echo json_encode(['status'=>'error','message'=>'Geçersiz temsilci']); exit; }
        $db = \getDbConnection();
        $db->exec("CREATE TABLE IF NOT EXISTS representative_site_assignments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            representative_id INT NOT NULL,
            site_id INT NOT NULL,
            commission_rate DECIMAL(5,2) NOT NULL DEFAULT 25.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY rep_site_unique (representative_id, site_id)
        )");
        $db->exec("CREATE TABLE IF NOT EXISTS representative_billing (
            id INT AUTO_INCREMENT PRIMARY KEY,
            representative_id INT NOT NULL,
            site_id INT NOT NULL,
            period VARCHAR(7) NOT NULL,
            amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            paid TINYINT(1) NOT NULL DEFAULT 0,
            paid_at DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY rep_bill_unique (representative_id, site_id, period)
        )");
        $repStmt = $db->prepare("SELECT id, full_name, phone, email, rep_iban AS iban FROM users WHERE id=?");
        $repStmt->execute([$repId]);
        $rep = $repStmt->fetch(\PDO::FETCH_OBJ);
        if (!$rep) { echo json_encode(['status'=>'error','message'=>'Temsilci bulunamadı']); exit; }
        $Siteler = new SitelerModel();
        $allSites = $Siteler->getAllWithOwners();
        $ass = $db->prepare("SELECT a.*, s.site_adi FROM representative_site_assignments a 
                             LEFT JOIN siteler s ON s.id = a.site_id
                             WHERE a.representative_id=? ORDER BY s.site_adi ASC");
        $ass->execute([$repId]);
        $assignments = [];
        while ($row = $ass->fetch(\PDO::FETCH_OBJ)) {
            $assignments[] = [
                'site_id' => (int)$row->site_id,
                'site_adi' => $row->site_adi ?? '',
                'commission_rate' => (float)$row->commission_rate
            ];
        }
        // Schedule build (last 12 months per assigned site)
        $Pricing = new UserSitePricingModel();
        $Billing = new UserSiteBillingModel();
        $Daireler = new DairelerModel();
        $schedule = [];
        foreach ($assignments as $a) {
            $siteId = (int)$a['site_id'];
            // Find site owner to resolve pricing
            $site = $Siteler->SiteBilgileri($siteId);
            if (!$site) { continue; }
            $ownerId = (int)($site->user_id ?? 0);
            $pricing = null;
            try { $pricing = $Pricing->getByUserAndSite($ownerId, $siteId); } catch (\Throwable $e) { $pricing = null; }
            $unitFee = $pricing ? (float)$pricing->unit_fee : 0.0;
            $startDate = $pricing ? ($pricing->start_date ?: null) : null;
            $aptCount = 0;
            try { $aptCount = (int)$Daireler->countBySiteId($siteId); } catch (\Throwable $e) { $aptCount = 0; }
            $monthlyTotal = $unitFee * $aptCount;
            $rate = (float)$a['commission_rate'];
            if ($startDate) {
                try {
                    $start = new \DateTime($startDate);
                    $cur = new \DateTime();
                    $periods = new \DatePeriod($start, new \DateInterval('P1M'), $cur->modify('first day of next month'));
                    $cnt=0;
                    foreach ($periods as $p) {
                        if ($cnt>=12) break;
                        $ym = $p->format('Y-m');
                        $sitePaid = false; 
                        try { $sitePaid = $Billing->isPaid($ownerId, $siteId, $ym); } catch (\Throwable $e) { $sitePaid = false; }
                        // Rep billing
                        $repPaid = 0; $paidAt = null;
                        $rb = $db->prepare("SELECT paid, paid_at FROM representative_billing WHERE representative_id=? AND site_id=? AND period=?");
                        $rb->execute([$repId, $siteId, $ym]);
                        if ($rbr = $rb->fetch(\PDO::FETCH_OBJ)) {
                            $repPaid = (int)($rbr->paid ?? 0);
                            $paidAt = $rbr->paid_at ?? null;
                        }
                        $schedule[] = [
                            'site_id' => $siteId,
                            'site_name' => $site->site_adi ?? '',
                            'period' => $ym,
                            'site_paid' => $sitePaid ? 1 : 0,
                            'amount' => round(($monthlyTotal * ($rate/100.0)),2),
                            'paid' => $repPaid,
                            'paid_at' => $paidAt
                        ];
                        $cnt++;
                    }
                } catch (\Throwable $e) {}
            }
        }
        echo json_encode(['status'=>'success','data'=>[
            'rep' => [
                'id' => (int)$rep->id, 
                'full_name' => $rep->full_name ?? '', 
                'phone' => $rep->phone ?? '',
                'email' => $rep->email ?? '',
                'iban' => $rep->iban ?? ''
            ],
            'all_sites' => array_map(function($s){ 
                return ['id'=>(int)$s->id,'site_adi'=>$s->site_adi ?? '']; 
            }, $allSites),
            'assignments' => $assignments,
            'schedule' => $schedule
        ]]);
    } catch (\Throwable $e) {
        echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
    }
    exit;
}
if ($action === 'rep_update') {
    try {
        $repId = isset($_POST['rep_id']) ? (int)$_POST['rep_id'] : 0;
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = isset($_POST['phone']) ? preg_replace('/\D+/', '', $_POST['phone']) : null;
        $email = $_POST['email'] ?? null;
        $iban = isset($_POST['iban']) ? strtoupper(preg_replace('/\s+/', '', $_POST['iban'])) : null;
        if ($repId <= 0) { echo json_encode(['status'=>'error','message'=>'Geçersiz temsilci']); exit; }
        if ($fullName === '') { echo json_encode(['status'=>'error','message'=>'Eksik isim']); exit; }
        if (!$phone || !preg_match('/^(0\d{10}|90\d{10})$/', $phone)) { echo json_encode(['status'=>'error','message'=>'Telefon formatı geçersiz']); exit; }
        if (preg_match('/^90\d{10}$/', $phone)) { $phone = '0' . substr($phone, 2); }
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) { echo json_encode(['status'=>'error','message'=>'E-posta formatı geçersiz']); exit; }
        if (!$iban) { echo json_encode(['status'=>'error','message'=>'IBAN zorunludur']); exit; }
        if (!preg_match('/^TR\d{24}$/', $iban)) { echo json_encode(['status'=>'error','message'=>'IBAN formatı geçersiz (TR + 24 rakam)']); exit; }
        $db = \getDbConnection();
        try { $db->exec("ALTER TABLE users ADD COLUMN rep_iban VARCHAR(34) NULL"); } catch (\Throwable $e) {}
        $stmt = $db->prepare("UPDATE users SET full_name=?, phone=?, email=?, rep_iban=? WHERE id=?");
        $ok = $stmt->execute([$fullName, $phone, $email, $iban, $repId]);
        echo json_encode(['status' => $ok ? 'success' : 'error']);
    } catch (\Throwable $e) {
        echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
    }
    exit;
}
if ($action === 'rep_delete') {
    try {
        $repId = isset($_POST['rep_id']) ? (int)$_POST['rep_id'] : 0;
        if ($repId <= 0) { echo json_encode(['status'=>'error','message'=>'Geçersiz temsilci']); exit; }
        $db = \getDbConnection();
        // Silme işlemleri: önce ilişkiler, sonra temsilci
        $db->prepare("DELETE FROM representative_site_assignments WHERE representative_id=?")->execute([$repId]);
        $db->prepare("DELETE FROM representative_billing WHERE representative_id=?")->execute([$repId]);
        $ok = $db->prepare("DELETE FROM users WHERE id=?")->execute([$repId]);
        echo json_encode(['status' => $ok ? 'success' : 'error']);
    } catch (\Throwable $e) {
        echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
    }
    exit;
}
if ($action === 'rep_assign_site') {
    try {
        $repId = isset($_POST['rep_id']) ? (int)$_POST['rep_id'] : 0;
        $siteId = isset($_POST['site_id']) ? (int)$_POST['site_id'] : 0;
        $rate = isset($_POST['commission_rate']) ? (float)$_POST['commission_rate'] : 25.0;
        if ($repId<=0 || $siteId<=0) { echo json_encode(['status'=>'error','message'=>'Eksik veri']); exit; }
        $db = \getDbConnection();
        $db->exec("CREATE TABLE IF NOT EXISTS representative_site_assignments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            representative_id INT NOT NULL,
            site_id INT NOT NULL,
            commission_rate DECIMAL(5,2) NOT NULL DEFAULT 25.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY rep_site_unique (representative_id, site_id)
        )");
        $stmt = $db->prepare("INSERT INTO representative_site_assignments (representative_id, site_id, commission_rate) VALUES (?,?,?) 
                              ON DUPLICATE KEY UPDATE commission_rate=VALUES(commission_rate)");
        $ok = $stmt->execute([$repId, $siteId, $rate]);
        echo json_encode(['status'=>$ok?'success':'error']);
    } catch (\Throwable $e) { echo json_encode(['status'=>'error','message'=>$e->getMessage()]); }
    exit;
}
if ($action === 'rep_unassign_site') {
    try {
        $repId = isset($_POST['rep_id']) ? (int)$_POST['rep_id'] : 0;
        $siteId = isset($_POST['site_id']) ? (int)$_POST['site_id'] : 0;
        if ($repId<=0 || $siteId<=0) { echo json_encode(['status'=>'error','message'=>'Eksik veri']); exit; }
        $db = \getDbConnection();
        $stmt = $db->prepare("DELETE FROM representative_site_assignments WHERE representative_id=? AND site_id=?");
        $ok = $stmt->execute([$repId, $siteId]);
        echo json_encode(['status'=>$ok?'success':'error']);
    } catch (\Throwable $e) { echo json_encode(['status'=>'error','message'=>$e->getMessage()]); }
    exit;
}
if ($action === 'rep_update_rate') {
    try {
        $repId = isset($_POST['rep_id']) ? (int)$_POST['rep_id'] : 0;
        $siteId = isset($_POST['site_id']) ? (int)$_POST['site_id'] : 0;
        $rate = isset($_POST['commission_rate']) ? (float)$_POST['commission_rate'] : 25.0;
        if ($repId<=0 || $siteId<=0) { echo json_encode(['status'=>'error','message'=>'Eksik veri']); exit; }
        $db = \getDbConnection();
        $stmt = $db->prepare("UPDATE representative_site_assignments SET commission_rate=? WHERE representative_id=? AND site_id=?");
        $ok = $stmt->execute([$rate, $repId, $siteId]);
        echo json_encode(['status'=>$ok?'success':'error']);
    } catch (\Throwable $e) { echo json_encode(['status'=>'error','message'=>$e->getMessage()]); }
    exit;
}
if ($action === 'rep_mark_paid') {
    try {
        $repId = isset($_POST['rep_id']) ? (int)$_POST['rep_id'] : 0;
        $siteId = isset($_POST['site_id']) ? (int)$_POST['site_id'] : 0;
        $period = $_POST['period'] ?? '';
        $paid = isset($_POST['paid']) ? (int)$_POST['paid'] : 0;
        $amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0.0;
        if ($repId<=0 || $siteId<=0 || !$period) { echo json_encode(['status'=>'error','message'=>'Eksik veri']); exit; }
        $db = \getDbConnection();
        $db->exec("CREATE TABLE IF NOT EXISTS representative_billing (
            id INT AUTO_INCREMENT PRIMARY KEY,
            representative_id INT NOT NULL,
            site_id INT NOT NULL,
            period VARCHAR(7) NOT NULL,
            amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            paid TINYINT(1) NOT NULL DEFAULT 0,
            paid_at DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY rep_bill_unique (representative_id, site_id, period)
        )");
        $stmt = $db->prepare("INSERT INTO representative_billing (representative_id, site_id, period, amount, paid, paid_at) 
                              VALUES (?,?,?,?,?, CASE WHEN ?=1 THEN NOW() ELSE NULL END)
                              ON DUPLICATE KEY UPDATE amount=VALUES(amount), paid=VALUES(paid), paid_at=CASE WHEN VALUES(paid)=1 THEN NOW() ELSE NULL END");
        $ok = $stmt->execute([$repId, $siteId, $period, $amount, $paid, $paid]);
        echo json_encode(['status'=>$ok?'success':'error']);
    } catch (\Throwable $e) { echo json_encode(['status'=>'error','message'=>$e->getMessage()]); }
    exit;
}
if ($action === 'rep_bulk_mark') {
    try {
        $repId = isset($_POST['rep_id']) ? (int)$_POST['rep_id'] : 0;
        $paid = isset($_POST['paid']) ? (int)$_POST['paid'] : 1;
        $itemsJson = $_POST['items'] ?? '[]';
        $items = json_decode($itemsJson, true);
        if ($repId<=0 || !is_array($items) || empty($items)) { echo json_encode(['status'=>'error','message'=>'Eksik veri']); exit; }
        $db = \getDbConnection();
        $db->exec("CREATE TABLE IF NOT EXISTS representative_billing (
            id INT AUTO_INCREMENT PRIMARY KEY,
            representative_id INT NOT NULL,
            site_id INT NOT NULL,
            period VARCHAR(7) NOT NULL,
            amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            paid TINYINT(1) NOT NULL DEFAULT 0,
            paid_at DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY rep_bill_unique (representative_id, site_id, period)
        )");
        $stmt = $db->prepare("INSERT INTO representative_billing (representative_id, site_id, period, amount, paid, paid_at) 
                              VALUES (?,?,?,?,?, CASE WHEN ?=1 THEN NOW() ELSE NULL END)
                              ON DUPLICATE KEY UPDATE amount=VALUES(amount), paid=VALUES(paid), paid_at=CASE WHEN VALUES(paid)=1 THEN NOW() ELSE NULL END");
        $okAll = true;
        foreach ($items as $it) {
            $siteId = (int)($it['site_id'] ?? 0);
            $period = $it['period'] ?? '';
            $amount = (float)($it['amount'] ?? 0.0);
            if ($siteId<=0 || !$period) { $okAll = false; continue; }
            $ok = $stmt->execute([$repId, $siteId, $period, $amount, $paid, $paid]);
            $okAll = $okAll && $ok;
        }
        echo json_encode(['status'=>$okAll?'success':'error']);
    } catch (\Throwable $e) { echo json_encode(['status'=>'error','message'=>$e->getMessage()]); }
    exit;
}
echo json_encode(['status' => 'error', 'message' => 'Geçersiz istek']);
