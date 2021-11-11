<?php

defined('TYPO3') or die();

call_user_func(
    static function () {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][] = [
            'nodeName' => 'otfWizard',
            'priority' => 50,
            'class' => \B13\Otf\Backend\Form\FieldWizard\OtfWizard::class
        ];
    }
);
