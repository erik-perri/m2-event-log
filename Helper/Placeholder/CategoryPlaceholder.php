<?php

namespace Ryvon\EventLog\Helper\Placeholder;

use Magento\Backend\Model\UrlInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Ryvon\EventLog\Helper\ImageLocator;

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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ImageLocator
     */
    private $imageLocator;

    /**
     * @var array
     */
    private $rootCategories = [];

    /**
     * @param UrlInterface $urlBuilder
     * @param CategoryRepository $categoryRepository
     * @param StoreManagerInterface $storeManager
     * @param ImageLocator $imageLocator
     */
    public function __construct(
        UrlInterface $urlBuilder,
        CategoryRepository $categoryRepository,
        StoreManagerInterface $storeManager,
        ImageLocator $imageLocator
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->imageLocator = $imageLocator;
    }

    /**
     * @return string
     */
    public function getSearchString(): string
    {
        return 'category';
    }

    /**
     * @param DataObject $context
     * @return string|null
     */
    public function getReplaceString($context)
    {
        $categoryId = $context->getData('category-id');
        $categoryName = $context->getData('category');
        if (!$categoryId || !$categoryName) {
            return null;
        }

        $category = $this->findCategoryById($categoryId);
        if (!$category) {
            return null;
        }

        $return = $this->buildLinkTag([
            'text' => $category->getName(),
            'title' => 'Edit this category in the admin',
            'href' => $this->urlBuilder->getUrl('catalog/category/edit', [
                'id' => $category->getId(),
            ]),
            'target' => '_blank',
        ]);

        if ($category->getIsActive() && !$this->isRootCategory($category)) {
            $frontendUrl = $category->getUrl();
            if ($frontendUrl) {
                $return .= $this->buildLinkTag([
                    'html' => $this->imageLocator->getIconSvg('store') ?: '[Frontend]',
                    'title' => 'View this category on the frontend',
                    'href' => $frontendUrl,
                    'target' => '_blank',
                    'class' => 'icon',
                ]);
            }
        }

        return $return;
    }

    /**
     * @param Category $category
     * @return bool
     */
    private function isRootCategory($category): bool
    {
        if (!isset($this->rootCategories[$category->getStoreId()])) {
            try {
                $store = $this->storeManager->getStore($category->getStoreId());
            } catch (NoSuchEntityException $e) {
                return false;
            }
            /** @noinspection PhpUndefinedMethodInspection */
            $this->rootCategories[$category->getStoreId()] = $store->getRootCategoryId();
        }

        return (int)$this->rootCategories[$category->getStoreId()] === (int)$category->getId();
    }

    /**
     * @param $id
     * @return Category|null
     */
    private function findCategoryById($id)
    {
        try {
            return $this->categoryRepository->get($id);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
}
