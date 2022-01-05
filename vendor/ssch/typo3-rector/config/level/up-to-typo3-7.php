<?php

declare (strict_types=1);
namespace RectorPrefix20220105;

use Ssch\TYPO3Rector\Set\Typo3SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(\Ssch\TYPO3Rector\Set\Typo3SetList::TYPO3_76);
};
