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
use B13\Otf\Evaluation\EvaluationSettings;
use B13\Otf\Evaluation\UniqueEvaluation;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class UniqueEvaluationTest extends FunctionalTestCase
{
    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/otf'
    ];

    protected $backendUserFixture = ORIGINAL_ROOT . 'typo3conf/ext/otf/Tests/Functional/Fixtures/be_users.xml';

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpBackendUserFromFixture(1);
        Bootstrap::initializeLanguageObject();

        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/otf/Tests/Functional/Fixtures/sys_category.xml');
        $GLOBALS['TCA']['sys_category']['columns']['title']['l10n_mode'] = 'exclude';
    }

    /**
     * @test
     * @dataProvider validateUniqueDataProvider
     */
    public function validateUnique(EvaluationSettings $input, bool $showHint): void
    {
        $GLOBALS['TCA']['sys_category']['columns']['title']['config']['eval'] = 'trim,required,unique';
        self::assertEquals(
            $showHint,
            (new UniqueEvaluation($this->getContainer()->get(UriBuilder::class)))($input) instanceof EvaluationHint
        );
    }

    public function validateUniqueDataProvider(): \Generator
    {
        yield 'Empty value' => [
            new EvaluationSettings('unique', []),
            false
        ];
        yield 'Unsupported eval' => [
            new EvaluationSettings(
                'email',
                ['value' => 'cat', 'table' => 'sys_category', 'field' => 'title', 'uid' => 'NEW1234', 'pid' => '0']
            ),
            false
        ];
        yield 'Missing information' => [
            new EvaluationSettings(
                'unique',
                ['value' => 'cat', 'field' => 'title', 'uid' => 'NEW1234', 'pid' => '0']
            ),
            false
        ];
        yield 'L10n_parent > 0' => [
            new EvaluationSettings(
                'unique',
                ['value' => 'cat', 'table' => 'sys_category', 'field' => 'title', 'uid' => '2', 'pid' => '0']
            ),
            false
        ];
        yield 'Non-unique value' => [
            new EvaluationSettings(
                'unique',
                ['value' => 'cat', 'table' => 'sys_category', 'field' => 'title', 'uid' => 'NEW123', 'pid' => '0']
            ),
            true
        ];
    }

    /**
     * @test
     * @dataProvider validateUniqueInPidDataProvider
     */
    public function validateUniqueInPid(EvaluationSettings $input, bool $showHint): void
    {
        $GLOBALS['TCA']['sys_category']['columns']['title']['config']['eval'] = 'trim,required,uniqueInPid';
        self::assertEquals(
            $showHint,
            (new UniqueEvaluation($this->getContainer()->get(UriBuilder::class)))($input) instanceof EvaluationHint
        );
    }

    public function validateUniqueInPidDataProvider(): \Generator
    {
        yield 'Empty value' => [
            new EvaluationSettings('uniqueInPid', []),
            false
        ];
        yield 'Unsupported eval' => [
            new EvaluationSettings(
                'email',
                ['value' => 'cat', 'table' => 'sys_category', 'field' => 'title', 'uid' => 'NEW1234', 'pid' => '0']
            ),
            false
        ];
        yield 'Missing information' => [
            new EvaluationSettings(
                'uniqueInPid',
                ['value' => 'cat', 'field' => 'title', 'uid' => 'NEW1234', 'pid' => '0']
            ),
            false
        ];
        yield 'L10n_parent > 0' => [
            new EvaluationSettings(
                'uniqueInPid',
                ['value' => 'cat', 'table' => 'sys_category', 'field' => 'title', 'uid' => '2', 'pid' => '0']
            ),
            false
        ];
        yield 'Non-unique value - but in another PID' => [
            new EvaluationSettings(
                'uniqueInPid',
                ['value' => 'cat', 'table' => 'sys_category', 'field' => 'title', 'uid' => 'NEW123', 'pid' => '123']
            ),
            false
        ];
        yield 'Non-unique value' => [
            new EvaluationSettings(
                'uniqueInPid',
                ['value' => 'cat', 'table' => 'sys_category', 'field' => 'title', 'uid' => 'NEW123', 'pid' => '0']
            ),
            true
        ];
    }

    /**
     * @test
     */
    public function editAccessIsCheckedForConflictLink(): void
    {
        $GLOBALS['TCA']['sys_category']['columns']['title']['config']['eval'] = 'trim,required,unique';

        $settings = new EvaluationSettings(
            'unique',
            ['value' => 'cat', 'table' => 'sys_category', 'field' => 'title', 'uid' => 'NEW123', 'pid' => '0']
        );
        $hint = (new UniqueEvaluation($this->getContainer()->get(UriBuilder::class)))($settings);

        // edit access - link is added to the hint
        self::assertStringContainsString('Edit conflicting record', $hint->getMessage());

        // Now use the non-admin user, which does not have access to pid=0
        GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('be_users')
            ->truncate('be_users');
        $this->setUpBackendUserFromFixture(2);

        $hint = (new UniqueEvaluation($this->getContainer()->get(UriBuilder::class)))($settings);

        // no edit access - link is NOT added to the hint
        self::assertStringNotContainsString('Edit conflicting record', $hint->getMessage());
    }

    /**
     * @test
     */
    public function userTSconfigIsCheckedForConflictLink(): void
    {
        $GLOBALS['TCA']['sys_category']['columns']['title']['config']['eval'] = 'trim,required,unique';

        $settings = new EvaluationSettings(
            'unique',
            ['value' => 'cat', 'table' => 'sys_category', 'field' => 'title', 'uid' => 'NEW123', 'pid' => '0']
        );
        $hint = (new UniqueEvaluation($this->getContainer()->get(UriBuilder::class)))($settings);

        // edit access - link is added to the hint
        self::assertStringContainsString('Edit conflicting record', $hint->getMessage());

        // Now use a user, which disabled the edit link in user TSconfig
        GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('be_users')
            ->truncate('be_users');
        $this->setUpBackendUserFromFixture(3);

        $hint = (new UniqueEvaluation($this->getContainer()->get(UriBuilder::class)))($settings);

        // no edit link is added
        self::assertStringNotContainsString('Edit conflicting record', $hint->getMessage());
    }
}
