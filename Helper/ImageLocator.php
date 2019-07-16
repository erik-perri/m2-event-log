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
}
