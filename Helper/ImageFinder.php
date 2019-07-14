<?php

namespace Ryvon\EventLog\Helper;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;

/**
 * Fetches image content from the theme files.
 */
class ImageFinder
{
    /**
     * The component name used if not specified.
     */
    const DEFAULT_COMPONENT_NAME = 'Ryvon_EventLog';

    /**
     * The image path to use if not specified.
     */
    const DEFAULT_IMAGE_PATH = 'view/adminhtml/web/images';

    /**
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * @param ComponentRegistrarInterface $componentRegistrar
     */
    public function __construct(ComponentRegistrarInterface $componentRegistrar)
    {
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * Returns the plugin root path.
     *
     * @param string $componentName
     * @return string|null
     */
    public function getRootPath($componentName = null)
    {
        return $this->componentRegistrar->getPath(
            ComponentRegistrar::MODULE,
            $componentName ?: static::DEFAULT_COMPONENT_NAME
        );
    }

    /**
     * Returns the path to the specified image, null if it does not exist.
     *
     * @param string $imageName
     * @param string|null $componentName
     * @param string|null $subPath
     * @return string|null
     */
    public function findImage($imageName, $componentName = null, $subPath = null)
    {
        $path = sprintf(
            '%s/%s/%s',
            $this->getRootPath($componentName),
            $subPath ?: static::DEFAULT_IMAGE_PATH,
            $imageName
        );

        return file_exists($path) ? $path : null;
    }

    /**
     * Returns the contents of the specified SVG file.
     *
     * @param string $imageName
     * @param string|null $componentName
     * @param string|null $subPath
     * @return string
     */
    public function getSvgContents($imageName, $componentName = null, $subPath = null): string
    {
        if (!preg_match('#\.svg$#i', $imageName)) {
            return sprintf(
                '<!-- Invalid image, SVG expected "%s" -->',
                $imageName
            );
        }

        $file = $this->findImage($imageName, $componentName, $subPath);
        if (!$file) {
            return sprintf(
                '<!-- Failed to find image "%s/%s" -->',
                $subPath ?: static::DEFAULT_IMAGE_PATH,
                $imageName
            );
        }

        return file_get_contents($file);
    }
}
