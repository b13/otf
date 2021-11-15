<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 CMS-based extension "otf" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

namespace B13\Otf\Tests\Unit\Backend\Form;

use B13\Otf\Backend\Controller\OtfAjaxController;
use B13\Otf\Evaluation\EvaluationHint;
use B13\Otf\Evaluation\EvaluationRegistry;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class OtfAjaxControllerTest extends FunctionalTestCase
{
    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/otf'
    ];

    /**
     * @var OtfAjaxController
     */
    protected $subject;

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpBackendUserFromFixture(1);
        Bootstrap::initializeLanguageObject();

        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/otf/Tests/Functional/Fixtures/fe_users.xml');

        $this->subject = new OtfAjaxController(
            $this->getContainer()->get(ResponseFactoryInterface::class),
            $this->getContainer()->get(StreamFactoryInterface::class),
            $this->getContainer()->get(EvaluationRegistry::class)
        );

        $this->request = (new ServerRequest())
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE);
    }

    /**
     * @test
     */
    public function methodPostIsEnforced(): void
    {
        $response = $this->parseResponse($this->subject->processRequest($this->request));
        self::assertEquals(405, $response['statusCode']);
        self::assertEquals('Method Not Allowed', $response['reasonPhrase']);
    }

    /**
     * @test
     */
    public function processRequestReturnsOnMissingEvaluations(): void
    {
        $response = $this->parseResponse($this->subject->processRequest(
            $this->request->withMethod('POST')
        ));

        self::assertEquals(200, $response['statusCode']);
        self::assertTrue($response['success']);
        self::assertEmpty($response['evaluationHint']);
    }

    /**
     * @test
     */
    public function processRequestReturnsOnInvalidEvaluationFormat(): void
    {
        $response = $this->parseResponse($this->subject->processRequest(
            $this->request->withMethod('POST')->withParsedBody([
                'evaluations' => '{}'
           ])
        ));

        self::assertEquals(200, $response['statusCode']);
        self::assertTrue($response['success']);
        self::assertEmpty($response['evaluationHint']);
    }

    /**
     * @test
     */
    public function processRequestReturnsEvaluationHintForInvalidEmail(): void
    {
        $response = $this->parseResponse($this->subject->processRequest(
            $this->request->withMethod('POST')->withParsedBody([
                'value' => 'invalid',
                'table' => 'fe_users',
                'field' => 'email',
                'evaluations' => '{"1":"email"}',
           ])
        ));

        self::assertEquals(200, $response['statusCode']);
        self::assertTrue($response['success']);

        self::assertFalse($response['evaluationHint']['markup']);
        self::assertEquals('invalid is not a valid e-mail address and will therefore be rejected on saving.', $response['evaluationHint']['message']);
        self::assertEquals(EvaluationHint::ERROR, $response['evaluationHint']['severity']);
    }

    /**
     * @test
     */
    public function processRequestReturnsEvaluationHintForUniqueInPid(): void
    {
        $expectedReplacement = 'oli1';
        $returnUrl = Environment::getPublicPath() . '/typo3/record/edit?params=2';

        $response = $this->parseResponse($this->subject->processRequest(
            $this->request->withMethod('POST')->withParsedBody([
                'value' => 'oli',
                'table' => 'fe_users',
                'field' => 'username',
                'uid' => 'NEW12626234532',
                'pid' => '0',
                'evaluations' => '{"1":"uniqueInPid"}',
                'returnUrl' => $returnUrl
           ])->withUri(new Uri(Environment::getPublicPath() . '/typo3/record/edit?param=1'))
        ));

        self::assertEquals(200, $response['statusCode']);
        self::assertTrue($response['success']);

        self::assertTrue($response['evaluationHint']['markup']);
        self::assertStringContainsString('The field has to be unique on this page and will therefore be changed to ' . $expectedReplacement . ' on saving.', $response['evaluationHint']['message']);
        self::assertEquals(EvaluationHint::WARNING, $response['evaluationHint']['severity']);

        // Edit link is added to message
        self::assertStringContainsString('Edit conflicting record [1]', $response['evaluationHint']['message']);
        // Return uri is added to edit link
        self::assertStringContainsString(rawurlencode($returnUrl), $response['evaluationHint']['message']);
    }

    /**
     * @test
     */
    public function processRequestReturnsEvaluationHintForUnique(): void
    {
        $expectedReplacement = 'oli2';
        $returnUrl = Environment::getPublicPath() . '/typo3/record/edit?params=2';

        $response = $this->parseResponse($this->subject->processRequest(
            $this->request->withMethod('POST')->withParsedBody([
                'value' => 'oli',
                'table' => 'fe_users',
                'field' => 'username',
                'uid' => 'NEW12626234532',
                'pid' => '0',
                'evaluations' => '{"1":"unique"}',
                'returnUrl' => $returnUrl
           ])->withUri(new Uri(Environment::getPublicPath() . '/typo3/record/edit?param=1'))
        ));

        self::assertEquals(200, $response['statusCode']);
        self::assertTrue($response['success']);

        self::assertTrue($response['evaluationHint']['markup']);
        self::assertStringContainsString('The field has to be unique and will therefore be changed to ' . $expectedReplacement . ' on saving.', $response['evaluationHint']['message']);
        self::assertEquals(EvaluationHint::WARNING, $response['evaluationHint']['severity']);

        // Edit link is added to message
        self::assertStringContainsString('Edit conflicting record [1]', $response['evaluationHint']['message']);
        // Return uri is added to edit link
        self::assertStringContainsString(rawurlencode($returnUrl), $response['evaluationHint']['message']);
    }

    protected function parseResponse(ResponseInterface $response): array
    {
        $response->getBody()->rewind();
        $responseBody = json_decode($response->getBody()->getContents(), true);

        return [
            'statusCode' => $response->getStatusCode(),
            'reasonPhrase' => $response->getReasonPhrase(),
            'success' => (bool)($responseBody['success'] ?? false),
            'evaluationHint' => (array)($responseBody['evaluationHint'] ?? [])
        ];
    }
}
