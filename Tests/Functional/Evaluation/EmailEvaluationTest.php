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

use B13\Otf\Evaluation\EmailEvaluation;
use B13\Otf\Evaluation\EvaluationHint;
use B13\Otf\Evaluation\EvaluationSettings;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class EmailEvaluationTest extends FunctionalTestCase
{
    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/otf'
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpBackendUserFromFixture(1);
        Bootstrap::initializeLanguageObject();
    }

    /**
     * @test
     * @dataProvider validateEmailDataProvider
     */
    public function validateEmail(EvaluationSettings $input, bool $showHint): void
    {
        self::assertEquals($showHint, (new EmailEvaluation())($input) instanceof EvaluationHint);
    }

    public function validateEmailDataProvider(): \Generator
    {
        yield 'Empty value' => [
            new EvaluationSettings('email', []),
            false
        ];
        yield 'Unsupported eval' => [
            new EvaluationSettings('unique', ['value' => 'no-email', 'table' => 'fe_users', 'field' => 'email']),
            false
        ];
        yield 'Valid email' => [
            new EvaluationSettings('email', ['value' => 'some@email.com', 'table' => 'fe_users', 'field' => 'email']),
            false
        ];
        yield 'Invalid value' => [
            new EvaluationSettings('email', ['value' => 'no-email', 'table' => 'fe_users', 'field' => 'email']),
            true
        ];
    }
}
