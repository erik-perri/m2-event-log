<?php

namespace Ryvon\EventLog\Block\Adminhtml;

use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;

class TemplateBlock extends \Magento\Backend\Block\Template
{
    /**
     * @param string $type
     * @param string $name
     * @param array $arguments
     * @return TemplateBlock
     * @throws LocalizedException
     * @throws \InvalidArgumentException
     */
    private function createBlock($type, $name = '', $arguments = []): \Magento\Backend\Block\Template
    {
        if ($type !== \Magento\Backend\Block\Template::class &&
            !is_subclass_of($type, \Magento\Backend\Block\Template::class)) {
            throw new \InvalidArgumentException(sprintf('Expected %s to extend Template', $type));
        }

        /** @var TemplateBlock $block */
        $block = $this->getLayout()->createBlock($type, $name, $arguments);

        // We need to set the area on the block or Magento will set it to crontab
        // and fail to find the templates when running this code through the cron.
        $block->setData('area', Area::AREA_ADMINHTML);
        return $block;
    }

    /**
     * @param string $templateFile
     * @param string $blockClass
     * @param array $data
     * @return string
     */
    public function renderBlock(string $templateFile, string $blockClass = TemplateBlock::class, array $data = []): string
    {
        try {
            $block = $this->createBlock($blockClass);
        } catch (\Exception $e) {
            return 'Failed to render block. ' . $e->getMessage();
        }

        // createBlock sets its own data so we can't use setData(array) or it will be overwritten.
        foreach ($data as $key => $value) {
            $block->setData($key, $value);
        }

        return $block->setTemplate($templateFile)->toHtml();
    }
}
