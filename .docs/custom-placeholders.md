# Custom Placeholders

This is the preferred way to add links or other dynamic content to the log entry.  The
example below will replace `{banner}` with a link to a banner edit page.  We prefer to
use a placeholder so the message can be translated later.  It also offers the benefit of
being able to load the current banner (using the `banner-id` context) which allows the
link to always use the most up to date name, or no link can be returned if the banner
no longer exists.

*See Helper/Placeholder/\*Placeholder.php for more examples.*


## Create placeholder class

The placeholder class must implement `PlaceholderInterface`.

```php
<?php

namespace ExampleCompany\ExamplePlugin\EventLog\Placeholder;

// ...

use Ryvon\EventLog\Helper\Placeholder\LinkPlaceholderTrait;
use Ryvon\EventLog\Helper\Placeholder\PlaceholderInterface;

class BannerPlaceholder implements PlaceholderInterface
{
    use LinkPlaceholderTrait;

    // ...

    /**
     * Returns the search string the placeholder is for (excluding brackets).
     *
     * @return string
     */
    public function getSearchString(): string
    {
        return 'banner';
    }

    /**
     * Returns the replacement string of the placeholder.
     *
     * @param \Magento\Framework\DataObject $context
     * @return string|null
     */
    public function getReplaceString($context)
    {
        // The default return should generally match the search string since that is what
        // will be displayed if this placeholder is not found
        $bannerName = $context->getData('banner');
        if (!$bannerName) {
            return false;
        }

        // If no banner id was provided we can't link to edit it (we could search by name
        // but this theoretical banner component may not require unique names, or the name
        // may have changed since this log event was created)
        $bannerId = $context->getData('banner-id');
        if (!$bannerId) {
            return $bannerName;
        }

        // Ensure the banner still exists to link to
        $banner = $this->findBannerById($bannerId);
        if (!$banner) {
            return $bannerName;
        }

        return $this->buildLinkTag([
            'text' => $banner->getName(),
            'title' => 'Edit this banner in the admin',
            'href' => $this->urlBuilder->getUrl('banners/banner/edit', [
                'banner_id' => $bannerId,
            ]),
            'target' => '_blank',
        ]);
    }
}
```

## Add placeholder to the replacer

In your plugin's di.xml add the placeholder to the `PlaceholderReplacer` constructor.

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">

    <!-- ... -->

    <!-- Add banner placeholder to the placeholder replacer -->
    <type name="Ryvon\EventLog\Helper\PlaceholderReplacer">
        <arguments>
            <argument name="placeholders" xsi:type="array">
                <item name="banner" xsi:type="object">
                    ExampleCompany\ExamplePlugin\EventLog\Placeholder\BannerPlaceholder
                </item>
            </argument>
        </arguments>
    </type>
</config>
```
