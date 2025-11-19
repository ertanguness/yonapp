<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../configs/bootstrap.php';

class AnketModelTest extends TestCase
{
    public function testTableCreationAndCrud()
    {
        $model = new \Model\AnketModel();

        $idEnc = $model->create([
            'title' => 'Test Anket',
            'description' => 'Açıklama',
            'start_date' => null,
            'end_date' => date('Y-m-d'),
            'status' => 'Taslak',
            'options' => ['A','B','C'],
        ]);
        $this->assertNotEmpty($idEnc);

        // find by plain id
        $id = \App\Helper\Security::decrypt($idEnc);
        $row = $model->find($id);
        $this->assertEquals('Test Anket', $row->title);

        // update
        $model->updateById($id, [ 'title' => 'Güncel Anket', 'status' => 'Aktif' ]);
        $row2 = $model->find($id);
        $this->assertEquals('Güncel Anket', $row2->title);
        $this->assertEquals('Aktif', $row2->status);

        // delete with encrypted id
        $ok = $model->delete($idEnc);
        $this->assertTrue($ok);
    }
}