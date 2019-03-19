<?php

namespace Ryvon\EventLog\Helper;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;

class EmailEmogrifier
{
    /**
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * @var \Pelago\Emogrifier
     */
    private $emogrifier;

    public function __construct(ComponentRegistrarInterface $componentRegistrar, \Pelago\Emogrifier $emogrifier)
    {
        $this->componentRegistrar = $componentRegistrar;
        $this->emogrifier = $emogrifier;
    }

    /**
     * @param string $html
     * @param string $css
     * @return string
     */
    public function emogrify(string $html): string
    {
        $this->emogrifier->setCss($this->loadCss());
        $this->emogrifier->setHtml($html);
        $this->emogrifier->setDebug(true);

        return $this->emogrifier->emogrify();
    }

    /**
     * @return string
     */
    private function loadCss(): string
    {
        $styles = '';

        foreach (['styles.css', 'email.css'] as $style) {
            $path = $this->getCssPath() . '/' . $style;
            if (file_exists($path) && is_readable($path)) {
                $styles .= file_get_contents($path);
            }
        }

        return $styles;
    }

    /**
     * @return string
     */
    private function getCssPath(): string
    {
        $componentPath = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, 'Ryvon_EventLog');
        return sprintf('%s/view/adminhtml/web/css', $componentPath);
    }
}
