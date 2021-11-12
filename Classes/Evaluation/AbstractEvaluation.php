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

/**
 * Abstract for evaluation services
 */
abstract class AbstractEvaluation implements EvaluationInterface
{
    /**
     * @var array
     */
    protected $supportedEvaluationNames;

    public function canHandle(string $evaluation): bool
    {
        return in_array($evaluation, $this->supportedEvaluationNames, true);
    }

    public function getSupportedEvaluationNames(): array
    {
        return $this->supportedEvaluationNames;
    }
}
