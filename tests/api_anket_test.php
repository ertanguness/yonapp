<?php
use PHPUnit\Framework\TestCase;

define('UNIT_TEST', true);

class APIAnketTest extends TestCase
{
    private $apiPath;
    protected function setUp(): void
    {
        $this->apiPath = __DIR__ . '/../pages/duyuru-talep/admin/api/APIAnket.php';
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    private function runApi(): string
    {
        ob_start();
        require $this->apiPath;
        return ob_get_clean();
    }

    public function testCreateListGetUpdateDelete()
    {
        // Create
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'action' => 'survey_save',
            'title' => 'UnitTest Anket',
            'description' => 'Desc',
            'end_date' => date('Y-m-d'),
            'status' => 'Aktif',
            'options' => ['X','Y']
        ];
        $out = $this->runApi();
        $res = json_decode($out, true);
        $this->assertEquals('success', $res['status'] ?? null);
        $encId = $res['id'] ?? null;
        $this->assertNotEmpty($encId);

        // List
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = [ 'action' => 'list' ];
        $listOut = $this->runApi();
        $rows = json_decode($listOut, true);
        $this->assertIsArray($rows);
        $this->assertGreaterThanOrEqual(1, count($rows));

        // Get
        $plainId = \App\Helper\Security::decrypt($encId);
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = [ 'action' => 'get', 'id' => $plainId ];
        $getOut = $this->runApi();
        $getRes = json_decode($getOut, true);
        $this->assertEquals('success', $getRes['status'] ?? null);
        $this->assertEquals('UnitTest Anket', $getRes['data']['title'] ?? null);

        // Update
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [ 'action' => 'update', 'id' => $plainId, 'title' => 'UnitTest Anket 2' ];
        $updOut = $this->runApi();
        $updRes = json_decode($updOut, true);
        $this->assertEquals('success', $updRes['status'] ?? null);

        // Delete
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [ 'action' => 'delete', 'id' => $encId ];
        $delOut = $this->runApi();
        $delRes = json_decode($delOut, true);
        $this->assertEquals('success', $delRes['status'] ?? null);
    }
}