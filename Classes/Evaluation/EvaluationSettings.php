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
 * DTO for evaluation settings, passed to the evaluation class
 */
class EvaluationSettings
{
    /**
     * @var string
     */
    protected $evaluation;

    /**
     * @var array
     */
    protected $parameters;

    public function __construct(string $evaluation, array $parameters)
    {
        $this->evaluation = $evaluation;
        $this->parameters = $parameters;
    }

    public function getEvaluation(): string
    {
        return $this->evaluation;
    }

    public function getParameter(string $name, $default = null)
    {
        return $this->parameters[$name] ?? $default;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
