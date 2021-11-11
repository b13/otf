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

use TYPO3\CMS\Core\Messaging\AbstractMessage;

/**
 * The evaluation hint, which will be displayed in FormEngine
 */
class EvaluationHint extends AbstractMessage
{
    public function __construct(string $message, int $severity = self::WARNING)
    {
        $this->message = $message;
        $this->severity = $severity;
    }
}
