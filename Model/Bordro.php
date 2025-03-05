<?php
//tanımlı değilse tanımla
!defined("ROOT") ? define("ROOT" ,$_SERVER["DOCUMENT_ROOT"]) : false;
require_once 'BaseModel.php';
require_once 'Bordro.php';
require_once 'Puantaj.php';
require_once 'Persons.php';
require_once ROOT . '/App/Helper/helper.php';
require_once ROOT . '/App/Helper/date.php';

use App\Helper\Date;
use App\Helper\Helper;

class Bordro extends Model
{
    protected $table = 'maas_gelir_kesinti';
    protected $sql_table = 'sqlmaas_gelir_kesinti';

    protected $sql_table_puantaj_toplam ="sqlmaas_gelir_kesinti_puantaj_toplam";

    public function __construct()
    {
        parent::__construct($this->table);
    }
    // getAll() metodu ile tüm kayıtları çeker

    public function getAll()
    {
        $sql = $this->db->query("SELECT * FROM $this->sql_table");
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    // get() metodu ile id'ye göre kayıt çeker
    public function getPersonWorkTransactions($id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->sql_table_puantaj_toplam WHERE person_id = :id AND tutar > 0 ORDER BY created_at DESC");
        $sql->execute([':id' => $id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    public function getPersonSalaryAndWageCut($person_id, $start_date, $end_date, $wage_tpe = 2)
    {
        $query = $this->db->prepare('
    SELECT 
        (SELECT SUM(tutar) FROM ' . $this->sql_table . ' mgk WHERE mgk.person_id = :person_id AND (kategori = :gelir or kategori = :maas or kategori = :puantaj or kategori= :maas) AND CAST(gun AS UNSIGNED) >= :start_date AND CAST(gun AS UNSIGNED) <= :end_date) AS gelir,
        (SELECT SUM(tutar) FROM ' . $this->sql_table . ' mgk WHERE mgk.person_id = :person_id AND kategori = :kesinti  AND CAST(gun AS UNSIGNED) >= :start_date AND CAST(gun AS UNSIGNED) <= :end_date) AS kesinti,
        (SELECT SUM(tutar) FROM ' . $this->sql_table . ' mgk WHERE mgk.person_id = :person_id AND kategori = :odeme  AND CAST(gun AS UNSIGNED) >= :start_date AND CAST(gun AS UNSIGNED) <= :end_date) AS odeme
');

        $query->execute([
            'gelir' => 1,
            'kesinti' => 15,
            'odeme' => 7,
            'maas' => 16,
            'puantaj' => 14,
            'wage_type' => $wage_tpe,
            ':person_id' => $person_id,
            ':start_date' => $start_date,
            ':end_date' => $end_date
        ]);
        return $query->fetch(PDO::FETCH_OBJ);
    }



    //Devreden Bakiye Hesaplama
    public function getCarryOverBalance($person_id, $start_date = null)
    {

        //start_date boş ise içinde olduğumuz ayın son günü al
        $start_date = $start_date ?? Date::lastDay(Date::getMonth(), Date::getYear());
        $query = $this->db->prepare('SELECT (
                        -COALESCE((
                            SELECT SUM(tutar) FROM maas_gelir_kesinti mgkk
                            WHERE mgkk.person_id = :person_id
                            AND mgkk.kategori IN (2, 7)
                            AND mgkk.gun < :start_date  -- Kesinti Toplamı
                        ), 0) +
                        COALESCE((
                            SELECT SUM(tutar) FROM maas_gelir_kesinti mgkg
                            WHERE mgkg.person_id = :person_id
                            AND mgkg.kategori IN (1, 16)
                            AND mgkg.gun < :start_date  -- Maaş veya diğer ödemeler toplamı
                        ), 0) +
                        COALESCE((
                            SELECT SUM(tutar) FROM puantaj p
                            WHERE p.person = :person_id  
                            AND p.gun < :start_date  -- puantaj çalışmaları toplamı
                        ), 0)
                    ) AS toplam;');

        $query->execute(
            [
                ':person_id' => $person_id,
                ':start_date' => $start_date
            ]
        );
        return $query->fetch(PDO::FETCH_OBJ);
    }

    public function getPersonIncomeExpenseInfo($person_id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE person_id = :person_id");
        $sql->execute([':person_id' => $person_id]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    // kayıt id'sine göre sorulama yapar
    public function getPersonIncomeExpensePayment($id)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE id = :id");
        $sql->execute([':id' => $id]);
        return $sql->fetch(PDO::FETCH_OBJ);
    }



    // Personelin toplam gelir, gider ve ödeme bilgilerini döndürür
    /* 
    1: Gelir
    14: Puantaj Çalışma
    16: Maaş
    15: Kesinti
    7: Ödeme
    */
    function sumAllIncomeExpense($person_id)
    {
        $sql = $this->db->prepare('SELECT 
                                                COALESCE(SUM(CASE WHEN (kategori = 1 or kategori = 16 or kategori = 14) THEN tutar END), 0) AS total_income,
                                                COALESCE(SUM(CASE WHEN kategori = 15 THEN tutar END), 0) AS total_expense,
                                                COALESCE(SUM(CASE WHEN kategori = 7 THEN tutar END), 0) AS total_payment

                                            FROM sqlmaas_gelir_kesinti 
                                            WHERE person_id = :person_id
                                            ORDER BY created_at desc;');
        $sql->execute(['person_id' => $person_id]);
        return $sql->fetch(PDO::FETCH_OBJ);
    }

    //Formatlanmış Personel Gelir ve Gider bilgilerini döndürür
    function sumAllIncomeExpenseFormatted($person_id)
    {
        $result = $this->sumAllIncomeExpense($person_id);
        $result->balance = Helper::formattedMoney($result->total_income - $result->total_expense - $result->total_payment);
        $result->total_income = Helper::formattedMoney($result->total_income);
        $result->total_expense = Helper::formattedMoney($result->total_expense);
        $result->total_payment = Helper::formattedMoney($result->total_payment);
        return $result;
    }

    //Personelin Bakiyesini döndürür
    function getBalance($person_id)
    {
        $result = $this->sumAllIncomeExpense($person_id);
        return $result->total_income - $result->total_expense - $result->total_payment;
    }

    //Personelin maaşı eklenmiş mi kontrol eder
    public function isPersonMonthlyIncomeAdded($person_id, $month, $year)
    {
        $sql = $this->db->prepare("SELECT * FROM $this->table WHERE 
                                            person_id = :person_id AND 
                                            ay = :month AND 
                                            yil = :year AND 
                                            kategori = :kategori");
                                        
                                        $sql->execute([
                                            ':person_id' => $person_id, 
                                            ':month' => $month, 
                                            ':year' => $year,
                                            ':kategori' => 16 //Maaş
                                        ]);
        return $sql->fetch(PDO::FETCH_OBJ);
    }

    //Personelin Maaşını ekler
    public function addPersonMonthlyIncome($person_id, $month, $year, $tutar, $turu)
    {
        $Persons = new Persons();

        $person = $Persons->find($person_id);
        $gun = sprintf('%2d%02d01', $year, $month);
        $job_start_date = Date::ymd($person->job_start_date);
        $job_end_date = Date::ymd($person->job_end_date);
        // ayın son gününe kadar olan gün sayısı
        $last_day = Date::lastDay($month, $year);

        // O ayın gün sayısı
        $month_day = Date::getDay($last_day);

        // personelin işe başladığı tarihten itibaren geçen gün sayısı
        //eğer personelin işten ayrılması ayın son gününden önceyse işten ayrıldığı tarihe kadar olan gün sayısını al
        if (!empty($job_end_date) && $job_end_date < $last_day) {
            $last_day = $job_end_date;
        }
        $work_day = $last_day - $job_start_date + 1;

        // personelin işe başladığı tarihten itibaren geçen gün sayısı ayın gün sayısından küçükse,
        // o ayın maaşını günlük olarak hesapla
        if ($work_day < $month_day) {
            $tutar = $tutar * $work_day / $month_day;
            $description = "$work_day günlük Maaş";
        } else {
            $description = 'Aylık Maaş';
        }

        $sql = $this->db->prepare("INSERT INTO $this->table SET person_id = :person_id, gun = :gun, ay = :month, yil = :year, tutar = :tutar, kategori = 16 , turu = :turu , aciklama = :aciklama");
        $sql->execute([':person_id' => $person_id, ':gun' => $gun, ':month' => $month, ':year' => $year, ':tutar' => $tutar, ':turu' => $turu, ':aciklama' => $description]);
        return $this->db->lastInsertId();
    }

    function updatePersonMonthlyIncome($person_id, $montly_income_id, $month, $year)
    {
        $Persons = new Persons();
        $person = $Persons->find($person_id);
        $gun = sprintf('%2d%02d01', $year, $month);
        $job_start_date = Date::ymd($person->job_start_date);
        $job_end_date = Date::ymd($person->job_end_date);
        // ayın son gününe kadar olan gün sayısı
        $last_day = Date::lastDay($month, $year);

        // O ayın gün sayısı
        $month_day = Date::getDay($last_day);

        // personelin işe başladığı tarihten itibaren geçen gün sayısı
        //işten ayrılma tarihi dolu ve o ayın son gününden küçükse
        //işten ayrıldığı tarihe kadar olan gün sayısını al
        if (!empty($job_end_date) && $job_end_date < $last_day) {
            $last_day = $job_end_date;
        }

        $work_day = $last_day - $job_start_date + 1;

        //tutar bilgisini getir
        $tutar = $person->daily_wages;


        // personelin işe başladığı tarihten itibaren geçen gün sayısı ayın gün sayısından küçükse,
        // o ayın maaşını günlük olarak hesapla
        if ($work_day < $month_day) {
            $tutar = $tutar * $work_day / $month_day;
            $description = "$work_day günlük Maaş";
        } else {
            $description = 'Aylık Maaş';
        }
        $sql = $this->db->prepare("UPDATE $this->table SET tutar = :tutar,aciklama = :description WHERE id = :id");
        $sql->execute([':tutar' => $tutar, ':id' => $montly_income_id, ':description' => $description]);
    }


    //Personelin işe başlama tarihinden önceki tüm maaşları sil
    public function deleteAllSalaries($person_id, $job_start_date)
    {
        $job_start_date = Date::Ymd($job_start_date);
        $sql = $this->db->prepare("DELETE FROM $this->table WHERE person_id = :person_id AND gun <= :job_start_date");
        $sql->execute([':person_id' => $person_id, ':job_start_date' => $job_start_date]);
    }

    //Personelin gelir bilgieri getirilir
    public function getPersonIncome($person_id, $ay, $yil)
    {
        $sql = $this->db->prepare("SELECT 
                                                yil,
                                                ay,
                                                sum(tutar) AS tutar,
                                                turu, saat,
                                                kategori,
                                                puantaj_turu
                                            FROM sqlmaas_gelir_kesinti 
                                            WHERE person_id = :person_id 
                                             AND tutar > 0 
                                            and ay = :ay and yil = :yil
                                            AND kategori IN(1,16,14)
                                            GROUP BY puantaj_turu,turu");
        $sql->execute([
            ':person_id' => $person_id,
            ':ay' => $ay,
            ':yil' => $yil
        ]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    //Personelin Kesinti bilgieri getirilir
    public function getPersonExpense($person_id,$ay,$yil)
    {
        $sql = $this->db->prepare("SELECT 
                                                yil,
                                                ay,
                                                sum(tutar) AS tutar,
                                                turu, saat,
                                                kategori,
                                                puantaj_turu
                                            FROM sqlmaas_gelir_kesinti 
                                            WHERE person_id = :person_id
                                            and ay = :ay and yil = :yil
                                            AND kategori IN(7,15)
                                            GROUP BY puantaj_turu,turu");
        $sql->execute([
            ':person_id' => $person_id,
            ':ay' => $ay,
            ':yil' => $yil
        ]);
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }
}
