<?php

namespace Ryvon\EventLog\Helper;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;

class SvgHelper
{
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
     * @return string
     */
    protected function getFontAwesomeSvgPath(): string
    {
        $componentPath = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, 'Ryvon_EventLog');
        return sprintf('%s/view/assets/node_modules/@fortawesome/fontawesome-free/svgs', $componentPath);
    }

    /**
     * @param string $type
     * @param string $id
     * @return string
     */
    protected function getSvg($type, $id): string
    {
        $path = sprintf(
            '%s/%s/%s.svg',
            $this->getFontAwesomeSvgPath(),
            $type,
            $id
        );

        if (!file_exists($path)) {
            return sprintf('<!-- Missing icon "%s/%s" -->', $type, $id);
        }

        return str_replace(["\r", "\n"], '', file_get_contents($path));
    }

    /**
     * @param string $id
     * @return string
     */
    public function getBrandSvg($id): string
    {
        return $this->getSvg('brands', $id);
    }

    /**
     * @param string $id
     * @return string
     */
    public function getRegularSvg($id): string
    {
        return $this->getSvg('regular', $id);
    }

    /**
     * @param string $id
     * @return string
     */
    public function getSolidSvg($id): string
    {
        return $this->getSvg('solid', $id);
    }

    /**
     * @return string
     */
    public function getStoreSvg(): string
    {
        return $this->getSolidSvg('store');
    }

    /**
     * @return string
     */
    public function getSearchLocationSvg(): string
    {
        return $this->getSolidSvg('search-location');
    }
}
