<?php

declare (strict_types=1);
namespace RectorPrefix20220503;

use Rector\Config\RectorConfig;
use Ssch\TYPO3Rector\Rector\v9\v4\AdditionalFieldProviderRector;
use Ssch\TYPO3Rector\Rector\v9\v4\BackendUtilityShortcutExistsRector;
use Ssch\TYPO3Rector\Rector\v9\v4\CallEnableFieldsFromPageRepositoryRector;
use Ssch\TYPO3Rector\Rector\v9\v4\ConstantsToEnvironmentApiCallRector;
use Ssch\TYPO3Rector\Rector\v9\v4\DocumentTemplateAddStyleSheetRector;
use Ssch\TYPO3Rector\Rector\v9\v4\GeneralUtilityGetHostNameToGetIndpEnvRector;
use Ssch\TYPO3Rector\Rector\v9\v4\RefactorDeprecatedConcatenateMethodsPageRendererRector;
use Ssch\TYPO3Rector\Rector\v9\v4\RefactorExplodeUrl2ArrayFromGeneralUtilityRector;
use Ssch\TYPO3Rector\Rector\v9\v4\RemoveInitMethodGraphicalFunctionsRector;
use Ssch\TYPO3Rector\Rector\v9\v4\RemoveInitMethodTemplateServiceRector;
use Ssch\TYPO3Rector\Rector\v9\v4\RemoveInitTemplateMethodCallRector;
use Ssch\TYPO3Rector\Rector\v9\v4\RemoveMethodsFromEidUtilityAndTsfeRector;
use Ssch\TYPO3Rector\Rector\v9\v4\SystemEnvironmentBuilderConstantsRector;
use Ssch\TYPO3Rector\Rector\v9\v4\TemplateGetFileNameToFilePathSanitizerRector;
use Ssch\TYPO3Rector\Rector\v9\v4\UseAddJsFileInsteadOfLoadJavascriptLibRector;
use Ssch\TYPO3Rector\Rector\v9\v4\UseClassSchemaInsteadReflectionServiceMethodsRector;
use Ssch\TYPO3Rector\Rector\v9\v4\UseContextApiForVersioningWorkspaceIdRector;
use Ssch\TYPO3Rector\Rector\v9\v4\UseContextApiRector;
use Ssch\TYPO3Rector\Rector\v9\v4\UseGetMenuInsteadOfGetFirstWebPageRector;
use Ssch\TYPO3Rector\Rector\v9\v4\UseLanguageAspectForTsfeLanguagePropertiesRector;
use Ssch\TYPO3Rector\Rector\v9\v4\UseRootlineUtilityInsteadOfGetRootlineMethodRector;
use Ssch\TYPO3Rector\Rector\v9\v4\UseSignalAfterExtensionInstallInsteadOfHasInstalledExtensionsRector;
use Ssch\TYPO3Rector\Rector\v9\v4\UseSignalTablesDefinitionIsBeingBuiltSqlExpectedSchemaServiceRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v4\RefactorDeprecatedConcatenateMethodsPageRendererRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v4\CallEnableFieldsFromPageRepositoryRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v4\ConstantsToEnvironmentApiCallRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v4\RemoveInitTemplateMethodCallRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v4\UseContextApiRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v4\RefactorExplodeUrl2ArrayFromGeneralUtilityRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v4\SystemEnvironmentBuilderConstantsRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v4\UseContextApiForVersioningWorkspaceIdRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v4\DocumentTemplateAddStyleSheetRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v4\UseLanguageAspectForTsfeLanguagePropertiesRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v4\BackendUtilityShortcutExistsRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v4\UseSignalTablesDefinitionIsBeingBuiltSqlExpectedSchemaServiceRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v4\UseGetMenuInsteadOfGetFirstWebPageRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v4\RemoveInitMethodGraphicalFunctionsRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v4\RemoveInitMethodTemplateServiceRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v4\UseAddJsFileInsteadOfLoadJavascriptLibRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v4\UseRootlineUtilityInsteadOfGetRootlineMethodRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v4\TemplateGetFileNameToFilePathSanitizerRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v4\UseSignalAfterExtensionInstallInsteadOfHasInstalledExtensionsRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v4\UseClassSchemaInsteadReflectionServiceMethodsRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v4\RemoveMethodsFromEidUtilityAndTsfeRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v4\AdditionalFieldProviderRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v4\GeneralUtilityGetHostNameToGetIndpEnvRector::class);
};
