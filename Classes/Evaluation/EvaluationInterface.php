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
 * Interface for evaluation services. This must be implemented
 * to get the services automatically tagged and registered.
 */
interface EvaluationInterface
{
    /**
     * Evaluation of a field, either returns an evaluation hint or NULL
     */
    public function __invoke(EvaluationSettings $evaluationSettings): ?EvaluationHint;

    /**
     * Whether the evaluation service can handle the given evaluation name
     */
    public function canHandle(string $evaluation): bool;

    /**
     * Returns the supported evaluation names for this service
     *
     * @return string[]
     */
    public function getSupportedEvaluationNames(): array;
}
