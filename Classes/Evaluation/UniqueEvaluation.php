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

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Evaluation for "unique" and "uniqueInPid"
 */
class UniqueEvaluation extends AbstractEvaluation
{
    protected $supportedEvaluationNames = ['unique', 'uniqueInPid'];

    /**
     * @var UriBuilder
     */
    protected $uriBuilder;

    public function __construct(UriBuilder $uriBuilder)
    {
        $this->uriBuilder = $uriBuilder;
    }

    public function __invoke(EvaluationSettings $evaluationSettings): ?EvaluationHint
    {
        $evaluation = $evaluationSettings->getEvaluation();
        $value = (string)($evaluationSettings->getParameter('value') ?? '');
        $table = (string)($evaluationSettings->getParameter('table') ?? '');
        $field = (string)($evaluationSettings->getParameter('field') ?? '');
        $uid = (int)($evaluationSettings->getParameter('uid') ?? 0);

        if ($value === ''
            || !in_array($evaluation, ['unique', 'uniqueInPid'], true)
            || !$this->canBeEvaluated($table, $field, $uid, $evaluation)
        ) {
            return null;
        }

        $newValue = $originalValue = $value;
        $conflict = null;
        $queryBuilder = $this->getUniqueStatement($newValue, $table, $field, $uid);

        // Add pid constraint if given and uniqueInPid evaluation
        $pid = $evaluationSettings->getParameter('pid');
        if ($evaluation === 'uniqueInPid' && $pid !== null) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createPositionalParameter((int)$pid, \PDO::PARAM_INT))
            );
        }

        // Execute statement to find possible conflicts
        $statement = $queryBuilder->execute();
        if ($row = $statement->fetch()) {
            $counter = 0;
            $isUnique = false;
            $conflict = (int)$row['uid'];
            // Execute the query with an incremented counter until the next valid value has been found
            while ($isUnique === false) {
                $newValue = $value . $counter;
                if (class_exists(\Doctrine\DBAL\ForwardCompatibility\Result::class) && $statement instanceof \Doctrine\DBAL\ForwardCompatibility\Result) {
                    $statement = $statement->getIterator();
                }
                $statement->bindValue(1, $newValue);
                $statement->execute();
                if (!$statement->fetch()) {
                    $isUnique = true;
                }
                $counter++;
            }
        }

        // Return evaluation hint in case the current value is not unique
        if ($originalValue !== $newValue && $conflict) {
            $lang = $this->getLanguageService();
            $conflictingRecordLink = (bool)($this->getBackendUser()->getTSConfig()['tx_otf.']['conflictingRecordLink'] ?? true);

            if ($conflictingRecordLink) {
                return new EvaluationHint(
                    htmlspecialchars(sprintf($lang->sL('LLL:EXT:otf/Resources/Private/Language/locallang.xlf:evaluationHint.' . $evaluation), $newValue)) . '&nbsp;' .
                    '<a href="' . htmlspecialchars($this->getEditConflictingRecordLink($table, $conflict, $evaluationSettings->getReturnUrl())) . '" style="color: #fff">'
                        . htmlspecialchars(sprintf($lang->sL('LLL:EXT:otf/Resources/Private/Language/locallang.xlf:evaluationHint.editConflictingRecord'), $conflict)) .
                    '</a>',
                    EvaluationHint::WARNING,
                    true
                );
            }

            return new EvaluationHint(
                sprintf($lang->sL('LLL:EXT:otf/Resources/Private/Language/locallang.xlf:evaluationHint.' . $evaluation), $newValue)
            );
        }

        return null;
    }

    /**
     * Check whether the field can be evaluated
     */
    protected function canBeEvaluated(string $table, string $field, int $uid, string $evaluation): bool
    {
        // Check whether the field is configured in TCA and the current eval is included
        if ($table === ''
            || $field === ''
            || !is_array($GLOBALS['TCA'][$table]['columns'][$field] ?? false)
            || !strpos($GLOBALS['TCA'][$table]['columns'][$field]['config']['eval'] ?? '', $evaluation)
        ) {
            return false;
        }

        // Check if the current record is a translation and l10n_mode=exclude is set
        $currentRecord = BackendUtility::getRecordWSOL($table, $uid);
        if ($currentRecord !== null
            && ($GLOBALS['TCA'][$table]['columns'][$field]['l10n_mode'] ?? '') === 'exclude'
            && (int)($currentRecord[$GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField'] ?? null] ?? 0) > 0
        ) {
            return false;
        }

        return true;
    }

    protected function getUniqueStatement(
        string $value,
        string $table,
        string $field,
        int $uid
    ): QueryBuilder {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $queryBuilder
            ->select('uid')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq($field, $queryBuilder->createPositionalParameter($value)),
                $queryBuilder->expr()->neq('uid', $queryBuilder->createPositionalParameter($uid, \PDO::PARAM_INT))
            );

        if (($GLOBALS['TCA'][$table]['columns'][$field]['l10n_mode'] ?? '') === 'exclude'
            && ($GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField'] ?? '') !== ''
            && ($GLOBALS['TCA'][$table]['ctrl']['languageField'] ?? '') !== '') {
            $queryBuilder
                ->andWhere(
                    $queryBuilder->expr()->orX(
                    // records without l10n_parent must be taken into account (in any language)
                        $queryBuilder->expr()->eq(
                            $GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField'],
                            $queryBuilder->createPositionalParameter(0, \PDO::PARAM_INT)
                        ),
                        // translations of other records must be taken into account
                        $queryBuilder->expr()->neq(
                            $GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField'],
                            $queryBuilder->createPositionalParameter($uid, \PDO::PARAM_INT)
                        )
                    )
                );
        }

        return $queryBuilder->setMaxResults(1);
    }

    protected function getEditConflictingRecordLink(string $table, int $uid, string $returnUrl = ''): string
    {
        $parameters = [
            'edit' => [
                $table => [
                    $uid => 'edit',
                ],
            ],
        ];

        if ($returnUrl !== '') {
            $parameters['returnUrl'] = $returnUrl;
        }

        return (string)$this->uriBuilder->buildUriFromRoute('record_edit', $parameters);
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
