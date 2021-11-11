<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 CMS-based extension "otf" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

namespace B13\Otf\Backend\Form\FieldWizard;

use B13\Otf\SupportedEvaluations;
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * OtfWizard renders a custom element, handing the on-the-fly evaluation hints
 */
class OtfWizard extends AbstractNode
{
    /**
     * @return array Result array
     */
    public function render(): array
    {
        $result = $this->initializeResultArray();

        $row = $this->data['databaseRow'];
        $table = $this->data['tableName'];
        $fieldName = $this->data['fieldName'];
        $fieldConfig = $this->data['processedTca']['columns'][$fieldName];

        // On-the-fly evaluation hints only work for input fields
        if ($fieldConfig['config']['type'] !== 'input') {
            return $result;
        }

        // Remove all unsupported evaluations
        $evaluations = array_intersect(
            GeneralUtility::trimExplode(',', $fieldConfig['config']['eval'], true),
            SupportedEvaluations::getConstants()
        );

        // Check whether evaluation hints can be displayed for the current record
        foreach ($evaluations as $key => $evaluation) {
            switch ($evaluation) {
                case 'unique':
                case 'uniqueInPid':
                    // In case the current record is a translation and l10n_mode=exclude is set, skip unique evaluation
                    if ((($fieldConfig['l10n_mode'] ?? '') === 'exclude'
                        && (int)($row[$GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField'] ?? null] ?? 0) > 0)
                    ) {
                        unset($evaluations[$key]);
                    }
            }
        }

        if ($evaluations === []) {
            // No valid evaluation found
            return $result;
        }

        $attributes = [
            'table' => $table,
            'field' => $fieldName,
            'uid' => $row['uid'],
            'pid' => $this->data['effectivePid'],
            'element' => $this->data['parameterArray']['itemFormElName'],
            'evaluations' => json_encode($evaluations)
        ];

        if (class_exists(\TYPO3\CMS\Core\Page\JavaScriptModuleInstruction::class)) {
            $result['requireJsModules'][] = \TYPO3\CMS\Core\Page\JavaScriptModuleInstruction::forRequireJS('TYPO3/CMS/Otf/OtfWizard');
        } else {
            $result['requireJsModules'][] = 'TYPO3/CMS/Otf/OtfWizard';
        }

        $result['html'] = '<typo3-formengine-otf-wizard ' . GeneralUtility::implodeAttributes($attributes, true) . '></typo3-formengine-otf-wizard>';

        return $result;
    }
}
