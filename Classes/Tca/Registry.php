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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Registry to add the OtfWizard to the requested fields
 */
class Registry
{
    private const FIELD_WIZARD_CONFIGURATION = [
        'otfWizard' => [
            'renderType' => 'otfWizard'
        ]
    ];

    public function registerFields(Configuration $configuration): self
    {
        $table = $configuration->getTable();
        if ($table === '') {
            return $this;
        }

        $fields = $configuration->getFields();
        if ($fields === []) {
            return $this;
        }

        $tableColumns = $GLOBALS['TCA'][$table]['columns'] ?? null;
        if (!is_array($tableColumns) || $tableColumns === []) {
            return $this;
        }

        foreach ($fields as $field) {
            $fieldName = $field->getName();
            if (!is_array($tableColumns[$fieldName]['config'] ?? null) || $tableColumns[$fieldName]['config'] === []) {
                continue;
            }
            // Add the OtfWizard to the column
            $tableColumns[$fieldName]['config']['fieldWizard'] = array_replace_recursive(
                $tableColumns[$fieldName]['config']['fieldWizard'] ?? [],
                self::FIELD_WIZARD_CONFIGURATION
            );
            // Add evaluations
            if ($field->getEvaluationsToAdd() !== []) {
                $tableColumns[$fieldName]['config']['eval'] = implode(',', array_unique(array_merge(
                    GeneralUtility::trimExplode(',', $tableColumns[$fieldName]['config']['eval'] ?? '', true),
                    $field->getEvaluationsToAdd()
                )));
            }
            // Remove evaluations
            if ($field->getEvaluationsToRemove() !== []) {
                $evaluations = GeneralUtility::trimExplode(',', $tableColumns[$fieldName]['config']['eval'] ?? '', true);
                foreach ($field->getEvaluationsToRemove() as $evaluation) {
                    if (($key = array_search($evaluation, $evaluations)) !== false) {
                        unset($evaluations[$key]);
                    }
                }
                if ($evaluations === []) {
                    unset($tableColumns[$fieldName]['config']['eval']);
                } else {
                    $tableColumns[$fieldName]['config']['eval'] = implode(',', $evaluations);
                }
            }
        }

        // Write back to TCA
        $GLOBALS['TCA'][$table]['columns'] = $tableColumns;

        return $this;
    }
}
