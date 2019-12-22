<?php

namespace EasyDoc\Tests;

use EasyDoc\EasyDoc;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \EasyDoc\EasyDoc
 */
class EasyDocTest extends TestCase
{
    /**
     * @covers ::setVerbose
     * @covers ::isVerbose
     */
    public function testSetVerbose()
    {
        $doc = new EasyDoc();

        $this->assertFalse($doc->isVerbose());

        $doc->setVerbose(true);

        $this->assertTrue($doc->isVerbose());

        $doc->setVerbose(false);

        $this->assertFalse($doc->isVerbose());
    }
}
