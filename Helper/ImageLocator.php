<?php

namespace Ryvon\EventLog\Helper;

class ImageLocator
{
    /**
     * @var FileLocator
     */
    private $locator;

    /**
     * @param FileLocator $locator
     */
    public function __construct(FileLocator $locator)
    {
        $this->locator = $locator;
    }

    /**
     * Returns the contents of the specified image file.
     *
     * @param string $iconName
     * @return string|null
     */
    public function getIconSvg(string $iconName)
    {
        $imageName = sprintf('%s.svg', $iconName);
        return $this->locator->getPluginFileContents(
            'Ryvon_EventLog',
            'view/adminhtml/web/images',
            $imageName
        );
    }

    /**
     * Returns the contents of the specified image file.
     *
     * @param string $isoCode
     * @return string|null
     */
    public function getFlagSvg(string $isoCode)
    {
        $imageName = sprintf('%s.svg', strtolower($isoCode));
        return $this->locator->getVendorFileContents(
            'components/flag-icon-css/flags/4x3',
            $imageName
        );
    }
}
