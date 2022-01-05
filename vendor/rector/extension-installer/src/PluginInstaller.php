<?php

declare (strict_types=1);
namespace Rector\RectorInstaller;

use RectorPrefix20220105\Composer\Installer\InstallationManager;
use RectorPrefix20220105\Composer\IO\IOInterface;
use RectorPrefix20220105\Composer\Package\PackageInterface;
use RectorPrefix20220105\Composer\Repository\InstalledRepositoryInterface;
use RectorPrefix20220105\Composer\Util\Filesystem as ComposerFilesystem;
/**
 * @see \Rector\RectorInstaller\Tests\PluginInstallerTest
 */
final class PluginInstaller
{
    /**
     * @var string
     */
    public const RECTOR_EXTENSION_TYPE = 'rector-extension';
    /**
     * @var string
     */
    public const RECTOR_EXTRA_KEY = 'rector';
    /**
     * @var string
     */
    private static $generatedFileTemplate = <<<'CODE_SAMPLE'
<?php

declare(strict_types = 1);

namespace Rector\RectorInstaller;

/**
 * This class is generated by rector/extension-installer.
 * @internal
 */
final class GeneratedConfig
{
    public const EXTENSIONS = %s;

    private function __construct()
    {
    }
}

CODE_SAMPLE;
    /**
     * @var \Rector\RectorInstaller\Filesystem
     */
    private $filesystem;
    /**
     * @var \Composer\Repository\InstalledRepositoryInterface
     */
    private $localRepository;
    /**
     * @var \Composer\IO\IOInterface
     */
    private $io;
    /**
     * @var \Composer\Installer\InstallationManager
     */
    private $installationManager;
    /**
     * @var ComposerFilesystem
     */
    private $composerFilesystem;
    /**
     * @var string
     */
    private $configurationFile;
    public function __construct(\Rector\RectorInstaller\Filesystem $filesystem, \RectorPrefix20220105\Composer\Repository\InstalledRepositoryInterface $localRepository, \RectorPrefix20220105\Composer\IO\IOInterface $io, \RectorPrefix20220105\Composer\Installer\InstallationManager $installationManager, \RectorPrefix20220105\Composer\Util\Filesystem $composerFilesystem, string $configurationFile)
    {
        $this->filesystem = $filesystem;
        $this->localRepository = $localRepository;
        $this->io = $io;
        $this->installationManager = $installationManager;
        $this->composerFilesystem = $composerFilesystem;
        $this->configurationFile = $configurationFile;
    }
    public function install() : void
    {
        $oldGeneratedConfigFileHash = null;
        if ($this->filesystem->isFile($this->configurationFile)) {
            $oldGeneratedConfigFileHash = $this->filesystem->hashFile($this->configurationFile);
        }
        $installedPackages = [];
        $data = [];
        foreach ($this->localRepository->getPackages() as $package) {
            if ($this->shouldSkip($package)) {
                continue;
            }
            $absoluteInstallPath = $this->installationManager->getInstallPath($package);
            $data[$package->getName()] = ['install_path' => $absoluteInstallPath, 'relative_install_path' => $this->composerFilesystem->findShortestPath(\dirname($this->configurationFile), $absoluteInstallPath, \true), 'extra' => $package->getExtra()[self::RECTOR_EXTRA_KEY] ?? null, 'version' => $package->getFullPrettyVersion()];
            $installedPackages[$package->getName()] = \true;
        }
        \ksort($data);
        \ksort($installedPackages);
        $generatedConfigFileContents = \sprintf(self::$generatedFileTemplate, \var_export($data, \true), \true);
        if ($this->filesystem->hashEquals((string) $oldGeneratedConfigFileHash, $generatedConfigFileContents)) {
            return;
        }
        $this->filesystem->writeFile($this->configurationFile, $generatedConfigFileContents);
        $this->io->write('<info>rector/rector-installer:</info> Extensions installed');
        foreach (\array_keys($installedPackages) as $name) {
            $this->io->write(\sprintf('> <info>%s:</info> installed', $name));
        }
    }
    private function shouldSkip(\RectorPrefix20220105\Composer\Package\PackageInterface $package) : bool
    {
        if ($package->getType() === self::RECTOR_EXTENSION_TYPE) {
            return \false;
        }
        if (isset($package->getExtra()[self::RECTOR_EXTRA_KEY])) {
            return \false;
        }
        return \true;
    }
}
