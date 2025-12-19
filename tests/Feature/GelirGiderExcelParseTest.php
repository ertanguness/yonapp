<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class GelirGiderExcelParseTest extends TestCase
{
    public function testNormalizeType(): void
    {
        require_once __DIR__ . '/../../configs/bootstrap.php';
        require_once __DIR__ . '/../../pages/finans-yonetimi/gelir-gider/upload/upload-from-xls.php';

        // upload-from-xls.php içindeki helper fonksiyonları global scope'ta.
        // Bu test minimal: aynı normalize mantığını burada tekrar etmiyoruz.
        // (Prod kodu fonksiyonel olarak doğrulamak için ileride servis katmanına taşınabilir.)

        $fn = new ReflectionFunction('ggNormalizeType');
        $this->assertSame('Gelir', $fn->invoke('Gelir'));
        $this->assertSame('Gelir', $fn->invoke('  gelir '));
        $this->assertSame('Gider', $fn->invoke('Gider'));
        $this->assertNull($fn->invoke('x'));
    }
}
