<?php

namespace Ryvon\EventLog\Helper\Placeholder;

use Magento\Store\Model\StoreManagerInterface;
use Ryvon\EventLog\Helper\ImageFinder;
use Magento\Backend\Model\UrlInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;

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
     * @var ImageFinder
     */
    private $imageFinder;

    /**
     * @var array
     */
    private $rootCategories = [];

    /**
     * @param UrlInterface $urlBuilder
     * @param CategoryRepository $categoryRepository
     * @param StoreManagerInterface $storeManager
     * @param ImageFinder $imageFinder
     */
    public function __construct(
        UrlInterface $urlBuilder,
        CategoryRepository $categoryRepository,
        StoreManagerInterface $storeManager,
        ImageFinder $imageFinder
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->imageFinder = $imageFinder;
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

        if ($category->getIsActive() && !$this->isRootCategory($category)) {
            $frontendUrl = $category->getUrl();
            if ($frontendUrl) {
                $return .= $this->buildLinkTag([
                    'html' => $this->imageFinder->getSvgContents('store.svg'),
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
