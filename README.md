# OTF - A TYPO3 extension to display on-the-fly evaluation hints in FormEngine

This TYPO3 extension allows to add a FormEngine FieldWizard to specific
TCA fields. The FieldWizard checks the corresponding fields for their
`eval` configuration. If one or multiple supported evaluations are found,
the FieldWizard displays on-the-fly evaluation hints in the backend form.

An example use case is the `username` field of `fe_users`, which is
configured as `unqiueInPid` and would therefore add a hint, as soon
as an already existing username is entered.

## Installation

Install this extension via `composer req b13/otf`.

You can also download the extension from the
[TYPO3 Extension Repository](https://extensions.typo3.org/extension/otf/) and
activate it in the Extension Manager of your TYPO3 installation.

Note: This extension is compatible with TYPO3 v10 and v11.

## Configuration

The FieldWizard can be added to any TCA field of type ``input``.

The following example adds the FieldWizard to the `username` and `email`
fields of the TYPO3 `fe_users` table.

```php
<?php

defined('TYPO3') or die();

\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\B13\Otf\Tca\Registry::class)
    ->registerFields(
        new \B13\Otf\Tca\Configuration('fe_users',
            new \B13\Otf\Tca\Field('username'),
            new \B13\Otf\Tca\Field('email')
        )
    );
```

In case you want to add the FieldWizard to a field, which does not yet
define any supported evaluation, you can simply add the evaluation to
the `Field`.

````php
<?php

defined('TYPO3') or die();

\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\B13\Otf\Tca\Registry::class)
    ->registerFields(
        new \B13\Otf\Tca\Configuration('fe_users',
            (new \B13\Otf\Tca\Field('email'))->addEvaluations('email', 'uniqueInPid')
        )
    );
````

It's also possible to remove existing evaluations with the
`->removeEvaluations()` method.

The above examples are for the use in `TCA/Overrides` files. In your own
TCA, simply add the FieldWizard to your field's configuration directly:

````php
'aField' => [
    'label' => 'aField',
    'config' => [
        'type' => 'input',
        'eval' => 'trim,required,unique',
        'fieldWizard' => [
            'otfWizard' => [
                'renderType' => 'otfWizard'
            ]
        ]
    ]
]
````


### Supported evaluations

Currently, following evaluations are supported:

* `unique`
* `uniqueInPid`
* `email`

### Administration

The behaviour of the on-the-fly evaluation hints can be confgiured with
User TSconfig. Following options are available:

* `tx_otf.conflictingRecordLink` - Whether a link to the confliciting record should be displayed (Default: `1`)

### Registration API

You can register your own evaluation services to handle additional `eval`'s.
Therefore, create a new evaluation service class which implements the
`EvaluationInterface`. The class will then automatically be tagged and
registered. Additionally, you can extend `AbstractEvaluation`, which
already implements some required methods.

## Credits

This extension was created by Oliver Bartsch in 2021 for [b13 GmbH, Stuttgart](https://b13.com).

[Find more TYPO3 extensions we have developed](https://b13.com/useful-typo3-extensions-from-b13-to-you)
that help us deliver value in client projects. As part of the way we work,
we focus on testing and best practices to ensure long-term performance,
reliability, and results in all our code.
