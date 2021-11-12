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
 * Registry for evaluations
 */
class EvaluationRegistry
{
    /**
     * @var EvaluationInterface[]
     */
    protected $evaluations;

    public function registerEvaluation(string $serviceName, EvaluationInterface $evaluation): void
    {
        $this->evaluations[$serviceName] = $evaluation;
    }

    public function getEvaluations(): array
    {
        return $this->evaluations;
    }

    public function getEvaluationByName(string $evaluationName): ?EvaluationInterface
    {
        foreach ($this->evaluations as $evaluation) {
            if ($evaluation->canHandle($evaluationName)) {
                return $evaluation;
            }
        }

        return null;
    }

    public function getSupportedEvaluationNames(): array
    {
        $supportedEvaluations = [];
        foreach ($this->evaluations as $evaluation) {
            $supportedEvaluations = array_merge($supportedEvaluations, $evaluation->getSupportedEvaluationNames());
        }
        return array_unique($supportedEvaluations);
    }
}
