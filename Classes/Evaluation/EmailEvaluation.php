<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 CMS-based extension "otf" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

namespace B13\Otf\Evaluation;

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Evaluation for "email"
 */
class EmailEvaluation extends AbstractEvaluation
{
    protected $supportedEvaluationNames = ['email'];

    public function __invoke(EvaluationSettings $evaluationSettings): ?EvaluationHint
    {
        $value = (string)$evaluationSettings->getParameter('value', '');

        if ($value === '' || GeneralUtility::validEmail($value)) {
            return null;
        }

        return new EvaluationHint(
            sprintf(
                $this->getLanguageService()->sL('LLL:EXT:otf/Resources/Private/Language/locallang.xlf:evaluationHint.email'),
                $value
            ),
            EvaluationHint::ERROR
        );
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
