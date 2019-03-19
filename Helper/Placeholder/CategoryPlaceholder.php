<?php

namespace Ryvon\EventLog\Helper\Placeholder;

use Magento\Backend\Model\UrlInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Ryvon\EventLog\Helper\SvgHelper;

class CategoryPlaceholder implements PlaceholderInterface
{
    use LinkPlaceholderTrait;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var SvgHelper
     */
    private $svgHelper;

    /**
     * @param UrlInterface $urlBuilder
     * @param CategoryRepository $categoryRepository
     * @param SvgHelper $svgHelper
     */
    public function __construct(
        UrlInterface $urlBuilder,
        CategoryRepository $categoryRepository,
        SvgHelper $svgHelper
    )
    {
        $this->urlBuilder = $urlBuilder;
        $this->categoryRepository = $categoryRepository;
        $this->svgHelper = $svgHelper;
    }

    /**
     * @return string
     */
    public function getSearchString()
    {
        return 'category';
    }

    /**
     * @param DataObject $context
     * @return string|null
     */
    public function getReplaceString($context)
    {
        $categoryName = $context->getData('category');
        if (!$categoryName) {
            return null;
        }

        $categoryId = $context->getData('category-id');
        if (!$categoryId) {
            return $categoryName;
        }

        $category = $this->findCategoryById($categoryId);
        if (!$category) {
            return $categoryName;
        }

        $return = $this->buildLinkTag([
            'text' => $category->getName(),
            'title' => 'Edit this category in the admin',
            'href' => $this->urlBuilder->getUrl('catalog/category/edit', [
                'id' => $category->getId(),
            ]),
            'target' => '_blank',
        ]);

        $frontendUrl = $category->getUrl();
        if ($frontendUrl) {
            $return .= $this->buildLinkTag([
                'html' => $this->svgHelper->getStoreSvg(),
                'title' => 'View this category on the frontend',
                'href' => $frontendUrl,
                'target' => '_blank',
                'class' => 'icon',
            ]);
        }

        return $return;
    }

    /**
     * @param $id
     * @return Category|null
     */
    protected function findCategoryById($id)
    {
        try {
            return $this->categoryRepository->get($id);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
}
