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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Compiler pass for auto-tagged evaluation services
 */
final class EvaluationPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $tagName;

    /**
     * @param string $tagName
     */
    public function __construct(string $tagName)
    {
        $this->tagName = $tagName;
    }

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(EvaluationRegistry::class)) {
            return;
        }

        $evaluationRegistryDefinition = $container->getDefinition(EvaluationRegistry::class);

        foreach ($container->findTaggedServiceIds($this->tagName) as $serviceName => $tags) {
            $definition = $container->findDefinition($serviceName);
            if (!$definition->isAutoconfigured() || $definition->isAbstract()) {
                continue;
            }

            $definition->setPublic(true);
            $evaluationRegistryDefinition->addMethodCall('registerEvaluation', [$serviceName, $definition]);
        }
    }
}
