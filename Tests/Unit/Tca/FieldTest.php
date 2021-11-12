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

use B13\Otf\Tca\Field;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class FieldTest extends UnitTestCase
{
    /**
     * @var Field
     */
    protected $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = new Field('aField');
    }

    /**
     * @test
     */
    public function getNameTest(): void
    {
        self::assertEquals('aField', $this->subject->getName());
    }

    /**
     * @test
     */
    public function addEvaluationsTest(): void
    {
        $this->subject->addEvaluations('eval1', 'eval2');
        // Values get added
        self::assertEquals(['eval1', 'eval2'], $this->subject->getEvaluationsToAdd());
        // New values get appended
        $this->subject->addEvaluations('eval3', 'eval4');
        self::assertEquals(['eval1', 'eval2', 'eval3', 'eval4'], $this->subject->getEvaluationsToAdd());
        // Array is kept unique
        $this->subject->addEvaluations('eval1');
        self::assertEquals(['eval1', 'eval2', 'eval3', 'eval4'], $this->subject->getEvaluationsToAdd());
    }

    /**
     * @test
     */
    public function removeEvaluationsTest(): void
    {
        $this->subject->removeEvaluations('eval1', 'eval2');
        // Values get added
        self::assertEquals(['eval1', 'eval2'], $this->subject->getEvaluationsToRemove());
        $this->subject->removeEvaluations('eval3', 'eval4');
        // New values get appended
        self::assertEquals(['eval1', 'eval2', 'eval3', 'eval4'], $this->subject->getEvaluationsToRemove());
        // Array is kept unique
        $this->subject->removeEvaluations('eval1');
        self::assertEquals(['eval1', 'eval2', 'eval3', 'eval4'], $this->subject->getEvaluationsToRemove());
    }
}
