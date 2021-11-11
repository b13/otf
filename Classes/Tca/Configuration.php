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
 * Configuration DTO for TCA table<->field combinations
 */
class Configuration
{
    /**
     * @var string
     */
    protected $table;

    /**
     * @var Field[]
     */
    protected $fields;

    public function __construct(string $table, array $fields)
    {
        $this->table = $table;
        $this->fields = $fields;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getFields(): array
    {
        return $this->fields;
    }
}
