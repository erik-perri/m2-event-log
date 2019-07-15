<?php

namespace Ryvon\EventLog\Helper;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;

/**
 * Locates files in the plugin or vendor files.
 */
class FileLocator
{
    /**
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * @var string
     */
    private $rootPath;

    /**
     * @var File
     */
    private $file;

    /**
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param DirectoryList $directoryList
     * @param File $file
     */
    public function __construct(
        ComponentRegistrarInterface $componentRegistrar,
        DirectoryList $directoryList,
        File $file
    ) {
        $this->componentRegistrar = $componentRegistrar;
        $this->rootPath = $directoryList->getRoot();
        $this->file = $file;
    }

    /**
     * Returns the plugin root path.
     *
     * @param string $moduleName
     * @return string|null
     */
    private function getPluginPath($moduleName)
    {
        return $this->componentRegistrar->getPath(
            ComponentRegistrar::MODULE,
            $moduleName
        );
    }

    /**
     * Checks the plugin files for the specified file
     *
     * @param string $componentName
     * @param string $subPath
     * @param string $fileName
     * @return string|null
     */
    public function locatePluginFile($componentName, $subPath, string $fileName)
    {
        $path = sprintf(
            '%s/%s/%s',
            $this->getPluginPath($componentName),
            $subPath,
            $fileName
        );

        return $this->file->fileExists($path) ? $path : null;
    }

    /**
     * Returns the contents of the specified file.
     *
     * @param string $componentName
     * @param string $subPath
     * @param string $fileName
     * @return string|null
     */
    public function getPluginFileContents(string $componentName, string $subPath, string $fileName): string
    {
        $file = $this->locatePluginFile($componentName, $subPath, $fileName);

        return $file ? $this->file->read($file) : null;
    }

    /**
     * Checks the vendor files in the specified path for the specified files.
     *
     * @param string $vendorSubPath The relative path to the image from the vendor directory.
     * @param string $fileName The file name, including extension.
     * @return string|null
     */
    public function locateVendorFile(string $vendorSubPath, string $fileName)
    {
        $path = sprintf(
            '%s/vendor/%s/%s',
            $this->rootPath,
            $vendorSubPath,
            $fileName
        );

        return $this->file->fileExists($path) ? $path : null;
    }

    /**
     * Returns the contents of the specified file.
     *
     * @param string $vendorSubPath
     * @param string $fileName
     * @return string|null
     */
    public function getVendorFileContents(string $vendorSubPath, string $fileName): string
    {
        $file = $this->locateVendorFile($vendorSubPath, $fileName);

        return $file ? $this->file->read($file) : null;
    }
}
