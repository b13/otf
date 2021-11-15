<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 CMS-based extension "otf" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

namespace B13\Otf\Tests\Unit\Backend\Form;

use B13\Otf\Evaluation\EvaluationHint;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class EvaluationHintTest extends UnitTestCase
{
    /**
     * @test
     * @dataProvider constructEvaluationHintDataProvider
     */
    public function constructEvaluationHint(array $inputArgs, array $expected): void
    {
        self::assertEquals($expected, json_decode(json_encode(new EvaluationHint(...$inputArgs)), true));
    }

    public function constructEvaluationHintDataProvider(): \Generator
    {
        yield 'Only message' => [
          [
              'some message'
          ],
          [
              'severity' => 1,
              'message' => 'some message',
              'markup' => false
          ]
        ];
        yield 'Changed severity' => [
          [
              'some message',
              2
          ],
          [
              'severity' => 2,
              'message' => 'some message',
              'markup' => false
          ]
        ];
        yield 'With markup' => [
          [
              'some message',
              2,
              true
          ],
          [
              'severity' => 2,
              'message' => 'some message',
              'markup' => true
          ]
        ];
    }
}
