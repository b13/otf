<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 CMS-based extension "otf" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

namespace B13\Otf;

use TYPO3\CMS\Core\Type\Enumeration;

/**
 * Enumeration of the supported evaluations
 */
class SupportedEvaluations extends Enumeration
{
    public const UNIQUE = 'unique';
    public const UNIQUE_IN_PID = 'uniqueInPid';
    public const EMAIL = 'email';

    public const __default = self::UNIQUE;

    public function isSupported(string $evaluation): bool
    {
        return $this->isValid($evaluation);
    }
}
