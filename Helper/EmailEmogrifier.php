<?php

namespace Ryvon\EventLog\Helper;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem\Io\File;
use Pelago\Emogrifier\CssInliner;

/**
 * Converts the stylesheets to inline styles for the email.
 */
class EmailEmogrifier
{
    /**
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * @var CssHelper
     */
    private $cssHelper;

    /**
     * @var File
     */
    private $file;

    /**
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param CssHelper $cssHelper
     * @param File $file
     */
    public function __construct(
        ComponentRegistrarInterface $componentRegistrar,
        CssHelper $cssHelper,
        File $file
    ) {
        $this->componentRegistrar = $componentRegistrar;
        $this->cssHelper = $cssHelper;
        $this->file = $file;
    }

    /**
     * Emogrifies the specified HTML using the event log CSS.
     *
     * @param string $html
     * @return string
     */
    public function emogrify(string $html): string
    {
        return CssInliner::fromHtml($html)->inlineCss($this->loadCss())->renderBodyContent();
    }

    /**
     * Loads the CSS files.
     *
     * @return string
     */
    private function loadCss(): string
    {
        $styles = '';

        $cssFiles = array_merge($this->cssHelper->getEmailCss(), $this->cssHelper->getCommonCss());
        foreach ($cssFiles as $file) {
            $parts = explode('::', $file);
            if (count($parts) !== 2) {
                continue;
            }

            $path = sprintf('%s/%s', $this->getWebPath($parts[0]), $parts[1]);
            if ($this->file->fileExists($path)) {
                $styles .= $this->file->read($path);
            }
        }

        return $styles;
    }

    /**
     * Returns the path of the specified plugin's web files.
     *
     * @param string $pluginName
     * @return string
     */
    private function getWebPath(string $pluginName): string
    {
        $componentPath = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $pluginName);
        return sprintf('%s/view/adminhtml/web', $componentPath);
    }
}
