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
use B13\Otf\Tca\Registry;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class RegistryTest extends UnitTestCase
{
    /**
     * @var Registry
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Registry();
    }

    /**
     * @test
     * @dataProvider registerFieldsDataProvider
     */
    public function registerFields(array $inputTca, Configuration $configuration, array $expectedTca): void
    {
        $GLOBALS['TCA']['aTable']['columns'] = $inputTca;
        $this->subject->registerFields($configuration);
        self::assertEquals($expectedTca, $GLOBALS['TCA']['aTable']['columns']);
    }

    public function registerFieldsDataProvider(): \Generator
    {
        yield 'Add wizard to single field' => [
            [
                'aField' => [
                    'config' => [
                        'eval' => 'trim,required'
                    ]
                ]
            ],
            new Configuration('aTable', new Field('aField')),
            [
                'aField' => [
                    'config' => [
                        'eval' => 'trim,required',
                        'fieldWizard' => [
                            'otfWizard' => [
                                'renderType' => 'otfWizard'
                            ]
                        ]
                    ]
                ]
            ],
        ];
        yield 'Add wizard to multiple field' => [
            [
                'aField' => [
                    'config' => [
                        'eval' => 'trim,required'
                    ]
                ],
                'anotherField' => [
                    'config' => [
                        'eval' => 'trim,required',
                        'fieldWizard' => [
                            'someWizard' => [
                                'renderType' => 'someWizard'
                            ]
                        ]
                    ]
                ]
            ],
            new Configuration(
                'aTable',
                new Field('aField'),
                new Field('anotherField')
            ),
            [
                'aField' => [
                    'config' => [
                        'eval' => 'trim,required',
                        'fieldWizard' => [
                            'otfWizard' => [
                                'renderType' => 'otfWizard'
                            ]
                        ]
                    ]
                ],
                'anotherField' => [
                    'config' => [
                        'eval' => 'trim,required',
                        'fieldWizard' => [
                            'someWizard' => [
                                'renderType' => 'someWizard'
                            ],
                            'otfWizard' => [
                                'renderType' => 'otfWizard'
                            ]
                        ]
                    ]
                ]
            ],
        ];
        yield 'Add wizard to single field with additional eval' => [
            [
                'aField' => [
                    'config' => [
                        'eval' => 'trim,required'
                    ]
                ]
            ],
            new Configuration(
                'aTable',
                (new Field('aField'))->addEvaluations('unique')
            ),
            [
                'aField' => [
                    'config' => [
                        'eval' => 'trim,required,unique',
                        'fieldWizard' => [
                            'otfWizard' => [
                                'renderType' => 'otfWizard'
                            ]
                        ]
                    ]
                ]
            ],
        ];
        yield 'Add wizard to single field with multiple additional evals' => [
            [
                'aField' => [
                    'config' => [
                        'eval' => 'trim,required'
                    ]
                ]
            ],
            new Configuration(
                'aTable',
                (new Field('aField'))->addEvaluations('unique', 'email')
            ),
            [
                'aField' => [
                    'config' => [
                        'eval' => 'trim,required,unique,email',
                        'fieldWizard' => [
                            'otfWizard' => [
                                'renderType' => 'otfWizard'
                            ]
                        ]
                    ]
                ]
            ],
        ];
        yield 'Add wizard to single field and remove eval' => [
            [
                'aField' => [
                    'config' => [
                        'eval' => 'trim,required'
                    ]
                ]
            ],
            new Configuration(
                'aTable',
                (new Field('aField'))->removeEvaluations('trim')
            ),
            [
                'aField' => [
                    'config' => [
                        'eval' => 'required',
                        'fieldWizard' => [
                            'otfWizard' => [
                                'renderType' => 'otfWizard'
                            ]
                        ]
                    ]
                ]
            ],
        ];
        yield 'Add wizard to single field and remove all evals' => [
            [
                'aField' => [
                    'config' => [
                        'eval' => 'trim,required'
                    ]
                ]
            ],
            new Configuration(
                'aTable',
                (new Field('aField'))->removeEvaluations('trim', 'required')
            ),
            [
                'aField' => [
                    'config' => [
                        'fieldWizard' => [
                            'otfWizard' => [
                                'renderType' => 'otfWizard'
                            ]
                        ]
                    ]
                ]
            ],
        ];
        yield 'Add wizard to single field - add and remove evals' => [
            [
                'aField' => [
                    'config' => [
                        'eval' => 'trim,required'
                    ]
                ]
            ],
            new Configuration(
                'aTable',
                (new Field('aField'))
                    ->addEvaluations('email', 'unique')
                    ->removeEvaluations('trim', 'required')
            ),
            [
                'aField' => [
                    'config' => [
                        'fieldWizard' => [
                            'otfWizard' => [
                                'renderType' => 'otfWizard'
                            ],
                        ],
                        'eval' => 'email,unique'
                    ]
                ]
            ],
        ];
        yield 'Add wizard to multiple field with added and removed evals' => [
            [
                'aField' => [
                    'config' => [
                        'eval' => 'trim,int'
                    ]
                ],
                'anotherField' => [
                    'config' => [
                        'eval' => 'uniqueInPid,required',
                        'fieldWizard' => [
                            'someWizard' => [
                                'renderType' => 'someWizard'
                            ]
                        ]
                    ]
                ]
            ],
            new Configuration(
                'aTable',
                (new Field('aField'))->addEvaluations('unique', 'email', 'is_in')->removeEvaluations('trim', 'is_in'),
                (new Field('anotherField'))->addEvaluations('unique')->removeEvaluations('uniqueInPid', 'required')
            ),
            [
                'aField' => [
                    'config' => [
                        'eval' => 'int,unique,email',
                        'fieldWizard' => [
                            'otfWizard' => [
                                'renderType' => 'otfWizard'
                            ]
                        ]
                    ]
                ],
                'anotherField' => [
                    'config' => [
                        'eval' => 'unique',
                        'fieldWizard' => [
                            'someWizard' => [
                                'renderType' => 'someWizard'
                            ],
                            'otfWizard' => [
                                'renderType' => 'otfWizard'
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }
}
