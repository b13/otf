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
use B13\Otf\Evaluation\EvaluationInterface;
use B13\Otf\Evaluation\EvaluationRegistry;
use B13\Otf\Evaluation\EvaluationSettings;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class EvaluationRegistryTest extends FunctionalTestCase
{
    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/otf'
    ];

    /**
     * @var EvaluationRegistry
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = $this->getContainer()->get(EvaluationRegistry::class);
    }

    /**
     * @test
     */
    public function registerEvaluationTest(): void
    {
        $class = new class() implements EvaluationInterface {
            public function __invoke(EvaluationSettings $evaluationSettings): ?EvaluationHint
            {
                return null;
            }

            public function canHandle(string $evaluation): bool
            {
                return $evaluation === 'eval';
            }

            public function getSupportedEvaluationNames(): array
            {
                return ['eval'];
            }
        };

        $aEvaluation = new $class();

        $this->subject->registerEvaluation('aEvaluation', $aEvaluation);

        self::assertEquals($aEvaluation, $this->subject->getEvaluations()['aEvaluation']);
        self::assertEquals($aEvaluation, $this->subject->getEvaluationByName('eval'));
        self::assertEquals(['email', 'unique', 'uniqueInPid', 'eval'], $this->subject->getSupportedEvaluationNames());
    }
}
