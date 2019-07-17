# Custom Placeholders

This is the only way to add links or other dynamic content to the log entry.  The
example below will replace `{banner}` with a link to a banner edit page.  We prefer to
use a placeholder so the message can be translated later.  It also offers the benefit of
being able to load the current banner (using the `banner-id` context) which allows the
link to always use the most up to date name, or no link can be returned if the banner
no longer exists.

*See Placeholder/Handler/\*Handler.php for more examples.*


## Create handler class

The handler class must implement `HandlerInterface`.

```php
<?php

namespace ExampleCompany\ExamplePlugin\EventLog\Placeholder;

// ...

use Ryvon\EventLog\Placeholder\Handler\LinkPlaceholderTrait;
use Ryvon\EventLog\Placeholder\Handler\HandlerInterface;

class BannerHandler implements HandlerInterface
{
    use LinkPlaceholderTrait;

    // ...

    /**
     * @inheritDoc
     */
    public function handle(\Magento\Framework\DataObject $context)
    {
        $bannerId = $context->getData('id');
        $bannerName = $context->getData('text');

        // When invalid context is found we return null which tells the placeholder to
        // use (and escape) the text context.
        if (!$bannerId || !$bannerName) {
            return null;
        }

        // Ensure the banner still exists to link to.
        $banner = $this->findBannerById($bannerId);
        if (!$banner) {
            return null;
        }

        // Return a link to the banner.
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

## Add handler to the replacer

In your plugin's di.xml add the handler to the `PlaceholderProcessor` constructor.

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">

    <!-- ... -->

    <!-- Add the handler to the placeholder processor -->
    <type name="Ryvon\EventLog\Placeholder\PlaceholderProcessor">
        <arguments>
            <argument name="handlers" xsi:type="array">
                <!-- The name attribute should be set to the search string (the text between the curly-brackets). -->
                <item name="banner" xsi:type="object">
                    ExampleCompany\ExamplePlugin\EventLog\Placeholder\BannerHandler
                </item>
            </argument>
        </arguments>
    </type>
</config>
```
