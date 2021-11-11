<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 CMS-based extension "otf" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

namespace B13\Otf\Tca;

/**
 * DTO for a single TCA field
 */
class Field
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string[]
     */
    protected $evaluationsToAdd = [];

    /**
     * @var string[]
     */
    protected $evaluationsToRemove = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function addEvaluations(array $evaluations): self
    {
        $this->evaluationsToAdd = array_unique(array_merge($this->evaluationsToAdd, $evaluations));
        return $this;
    }

    public function getEvaluationsToAdd(): array
    {
        return $this->evaluationsToAdd;
    }

    public function removeEvaluations(array $evaluations): self
    {
        $this->evaluationsToRemove = array_unique(array_merge($this->evaluationsToRemove, $evaluations));
        return $this;
    }

    public function getEvaluationsToRemove(): array
    {
        return $this->evaluationsToRemove;
    }
}
