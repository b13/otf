<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 CMS-based extension "otf" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

namespace B13\Otf\Tests\Unit\Tca;

use B13\Otf\Tca\Configuration;
use B13\Otf\Tca\Field;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ConfigurationTest extends UnitTestCase
{
    /**
     * @test
     */
    public function getTableTest(): void
    {
        $configuration = new Configuration('aTable');
        self::assertEquals('aTable', $configuration->getTable());
    }

    /**
     * @test
     */
    public function addFieldsTest(): void
    {
        // Single field
        $aField = new Field('aField');
        $configuration = new Configuration('aTable', $aField);
        self::assertEquals([$aField], $configuration->getFields());

        // Multiple field
        $aField = new Field('aField');
        $bField = new Field('bField');
        $configuration = new Configuration('aTable', $aField, $bField);
        self::assertEquals([$aField, $bField], $configuration->getFields());
    }
}
