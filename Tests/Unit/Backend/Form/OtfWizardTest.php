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

use B13\Otf\Backend\Form\FieldWizard\OtfWizard;
use B13\Otf\Evaluation\EvaluationRegistry;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class OtfWizardTest extends UnitTestCase
{
    /**
     * @var array
     */
    protected $data;

    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['TCA']['aTable']['ctrl']['transOrigPointerField'] = 'l10n_parent';

        $this->data = [
            'tableName' => 'aTable',
            'fieldName' => 'aField',
            'effectivePid' => 1,
            'parameterArray' => [
                'itemFormElName' => 'data[aTable][1][aField]'
            ],
            'databaseRow' => [
                'uid' => 1
            ],
            'processedTca' => [
                'columns' => [
                    'aField' => [
                        'l10n_mode' => 'exclude',
                        'config' => [
                            'fieldWizard' => [
                                'otfWizard' => [
                                    'renderType' => 'otfWizard'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }

    /**
     * @test
     */
    public function earlyReturnOnInvalidType(): void
    {
        $this->data['processedTca']['columns']['aField']['config']['type'] = 'select';
        self::assertFalse((bool)(new OtfWizard(new NodeFactory(), $this->data))->render()['html']);
    }

    /**
     * @test
     */
    public function requireJsModuleIsAdded(): void
    {
        $this->data['processedTca']['columns']['aField']['config']['type'] = 'input';
        $this->data['processedTca']['columns']['aField']['config']['eval'] = 'trim,unique';

        $evaluationRegistryProphecy = $this->prophesize(EvaluationRegistry::class);
        $evaluationRegistryProphecy->getSupportedEvaluationNames()->shouldBeCalled()->willReturn(
            ['unique']
        );
        GeneralUtility::addInstance(EvaluationRegistry::class, $evaluationRegistryProphecy->reveal());

        if (class_exists(\TYPO3\CMS\Core\Page\JavaScriptModuleInstruction::class)) {
            $expected = \TYPO3\CMS\Core\Page\JavaScriptModuleInstruction::forRequireJS('TYPO3/CMS/Otf/OtfWizard');
        } else {
            $expected = 'TYPO3/CMS/Otf/OtfWizard';
        }

        self::assertEquals(
            $expected,
            (new OtfWizard(new NodeFactory(), $this->data))->render()['requireJsModules'][0]
        );
    }

    /**
     * @test
     * @dataProvider renderTestDataProvider
     */
    public function renderTest(array $databaseRow, array $fieldConfig, string $expectedOutput): void
    {
        $this->data['databaseRow'] = array_replace_recursive($this->data['databaseRow'], $databaseRow);
        $this->data['processedTca']['columns']['aField']['config'] = array_replace_recursive(
            $this->data['processedTca']['columns']['aField']['config'],
            $fieldConfig
        );

        $evaluationRegistryProphecy = $this->prophesize(EvaluationRegistry::class);
        $evaluationRegistryProphecy->getSupportedEvaluationNames()->shouldBeCalled()->willReturn(
            ['unique', 'uniqueInPid', 'email']
        );
        GeneralUtility::addInstance(EvaluationRegistry::class, $evaluationRegistryProphecy->reveal());

        self::assertEquals($expectedOutput, (new OtfWizard(new NodeFactory(), $this->data))->render()['html']);
    }

    public function renderTestDataProvider(): \Generator
    {
        yield 'No supported eval' => [
            [],
            [
                'type' => 'input',
                'eval' => 'trim,required'
            ],
            ''
        ];
        yield 'Translation with l10n_mode=exclude' => [
            [
                'l10n_parent' => 2
            ],
            [
                'type' => 'input',
                'eval' => 'trim,required,unique'
            ],
            ''
        ];
        yield 'Valid field with one eval' => [
            [
                'l10n_parent' => 0
            ],
            [
                'type' => 'input',
                'eval' => 'trim,required,unique'
            ],
            '<typo3-formengine-otf-wizard table="aTable" field="aField" uid="1" pid="1" element="data[aTable][1][aField]" evaluations="{&quot;2&quot;:&quot;unique&quot;}"></typo3-formengine-otf-wizard>'
        ];
        yield 'Valid field with multiple evals' => [
            [
                'l10n_parent' => 0
            ],
            [
                'type' => 'input',
                'eval' => 'trim,required,uniqueInPid,email'
            ],
            '<typo3-formengine-otf-wizard table="aTable" field="aField" uid="1" pid="1" element="data[aTable][1][aField]" evaluations="{&quot;2&quot;:&quot;uniqueInPid&quot;,&quot;3&quot;:&quot;email&quot;}"></typo3-formengine-otf-wizard>'
        ];
    }
}
