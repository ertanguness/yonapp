<?php
use PHPUnit\\Framework\\TestCase;

define('UNIT_TEST', true);

class APIAnketApprovalTest extends TestCase
{
    private $adminApi;
    private $userApi;

    protected function setUp(): void
    {
        $this->adminApi = __DIR__ . '/../pages/duyuru-talep/admin/api/APIAnket.php';
        $this->userApi  = __DIR__ . '/../pages/duyuru-talep/users/api/APIAnket.php';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
    }

    private function runAdminApi(): string
    {
        ob_start(); require $this->adminApi; return ob_get_clean();
    }
    private function runUserApi(): string
    {
        ob_start(); require $this->userApi; return ob_get_clean();
    }

    public function testApprovalFlow()
    {
        // Create survey
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'action' => 'survey_save',
            'title' => 'Approval Test Anket',
            'description' => 'Desc',
            'end_date' => date('Y-m-d'),
            'status' => 'Onay Bekliyor',
            'options' => ['A','B']
        ];
        $out = $this->runAdminApi(); $res = json_decode($out, true);
        $this->assertEquals('success', $res['status'] ?? null);
        $encId = $res['id']; $plainId = \App\Helper\Security::decrypt($encId);

        // Approve by user 9999
        $_SESSION['user'] = (object)['id' => 9999];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [ 'action'=>'approve', 'id' => $plainId ];
        $out2 = $this->runUserApi(); $res2 = json_decode($out2, true);
        $this->assertEquals('success', $res2['status'] ?? null);
        $this->assertEquals(1, $res2['counts']['approved'] ?? 0);

        // Reject by another user 10000
        $_SESSION['user'] = (object)['id' => 10000];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [ 'action'=>'reject', 'id' => $plainId ];
        $out3 = $this->runUserApi(); $res3 = json_decode($out3, true);
        $this->assertEquals('success', $res3['status'] ?? null);
        $this->assertEquals(1, $res3['counts']['rejected'] ?? 0);

        // List surveys to review
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = [ 'action' => 'surveys_to_review' ];
        $listOut = $this->runUserApi(); $listRes = json_decode($listOut, true);
        $this->assertEquals('success', $listRes['status'] ?? null);
        $this->assertIsArray($listRes['data'] ?? null);
        $found = false; foreach ($listRes['data'] as $row) { if (($row['id'] ?? 0) == $plainId) { $found = true; break; } }
        $this->assertTrue($found);
    }
}