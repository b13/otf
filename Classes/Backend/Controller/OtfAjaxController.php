<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 CMS-based extension "otf" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

namespace B13\Otf\Backend\Controller;

use B13\Otf\Evaluation\EvaluationHint;
use B13\Otf\Evaluation\EvaluationRegistry;
use B13\Otf\Evaluation\EvaluationSettings;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Ajax endpoint for generating on-the-fly evaluation hints.
 *
 * Basically this controller checks the requested evaluations,
 * searches for the corresponding evaluation services, which
 * then might return an on-the-fly evaluation hint.
 */
class OtfAjaxController
{
    /**
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * @var StreamFactoryInterface
     */
    protected $streamFactory;

    /**
     * @var EvaluationRegistry
     */
    protected $evaluationRegistry;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        EvaluationRegistry $evaluationRegistry
    ) {
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
        $this->evaluationRegistry = $evaluationRegistry;
    }

    public function processRequest(ServerRequestInterface $request): ResponseInterface
    {
        // Compatibility with TYPO3 v10
        if ($request->getMethod() !== 'POST') {
            return $this->responseFactory->createResponse(405);
        }

        $evaluations = (string)($request->getParsedBody()['evaluations'] ?? '');
        if ($evaluations === '') {
            return $this->createJsonResponse();
        }

        $evaluations = json_decode($evaluations, true) ?? [];
        if (!is_array($evaluations) || $evaluations === []) {
            return $this->createJsonResponse();
        }

        foreach ($evaluations as $evaluation) {
            $evaluationService = $this->evaluationRegistry->getEvaluationByName($evaluation);
            if ($evaluationService === null) {
                // Skip, if no service can handle the evaluation
                continue;
            }
            // Call the evaluation service with the current evaluation settings
            $evaluationHint = $evaluationService(
                new EvaluationSettings(
                    $evaluation,
                    $request->getParsedBody(),
                    GeneralUtility::sanitizeLocalUrl($request->getParsedBody()['returnUrl'] ?? '')
                )
            );
            if ($evaluationHint !== null) {
                // Return the created evaluation hint
                return $this->createJsonResponse($evaluationHint);
            }
        }

        return $this->createJsonResponse();
    }

    protected function createJsonResponse(?EvaluationHint $evaluationHint = null, bool $success = true): ResponseInterface
    {
        $data = [
            'success' => $success
        ];

        if ($evaluationHint !== '') {
            $data['evaluationHint'] = $evaluationHint;
        }

        return $this->responseFactory
            ->createResponse()
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withBody($this->streamFactory->createStream(
                json_encode($data)
            ));
    }
}
