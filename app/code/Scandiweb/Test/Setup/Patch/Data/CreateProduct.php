<?php

declare(strict_types=1);

namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\State;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Api\Data\CategoryInterfaceFactory;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Framework\Exception\LocalizedException;

class CreateProduct implements DataPatchInterface
{
    /**
     * @var ProductInterfaceFactory
     */
    protected ProductInterfaceFactory $productInterfaceFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * @var State
     */
    protected State $appState;

    /**
     * @var EavSetup
     */
    protected EavSetup $eavSetup;

    /**
     * @var CategoryInterfaceFactory
     */
    protected CategoryInterfaceFactory $categoryInterfaceFactory;

    /**
     * @var CategoryLinkManagementInterface
     */
    protected CategoryLinkManagementInterface $getCategoryLinkManagement;

    /**
     * @param ProductInterfaceFactory $productInterfaceFactory
     * @param ProductRepositoryInterface $productRepository
     * @param State $appState
     * @param EavSetup $eavSetup
     * @param CategoryInterfaceFactory $categoryInterfaceFactory
     * @param CategoryLinkManagementInterface $getCategoryLinkManagement
     */
    public function __construct(
        ProductInterfaceFactory $productInterfaceFactory,
        ProductRepositoryInterface $productRepository,
        State $appState,
        EavSetup $eavSetup,
        CategoryInterfaceFactory $categoryInterfaceFactory,
        CategoryLinkManagementInterface $getCategoryLinkManagement
    ) {
        $this->appState = $appState;
        $this->productInterfaceFactory = $productInterfaceFactory;
        $this->productRepository = $productRepository;
        $this->eavSetup = $eavSetup;
        $this->categoryInterfaceFactory = $categoryInterfaceFactory;
        $this->getCategoryLinkManagement = $getCategoryLinkManagement;
    }

    public function apply()
    {
        $this->appState->emulateAreaCode('adminhtml', [$this, 'execute']);
    }

    /**
     * @throws LocalizedException
     * @return void
     */
    public function execute()
    {
        $product = $this->productInterfaceFactory->create();

        if ($product->getIdBySku('scandi-test-product')) {
            return;
        }

        $attributeSetId = $this->eavSetup->getAttributeSetId(Product::ENTITY, 'Default');
        $product->setTypeId(Type::TYPE_SIMPLE)
            ->setAttributeSetId($attributeSetId)
            ->setName('Scandi Test Product')
            ->setSku('scandi-test-product')
            ->setUrlKey('scanditestproduct')
            ->setPrice(19.99)
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStatus(Status::STATUS_ENABLED);

        $product = $this->productRepository->save($product);
        $category = $this->categoryInterfaceFactory->create()
            ->getCollection()
            ->addAttributeToFilter('name', 'Men');
        $categoryId = $category->getFirstItem()->getId();
        $this->getCategoryLinkManagement->assignProductToCategories(
            $product->getSku(),
            [$categoryId]
        );
    }

    /**
     * @return array
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public static function getDependencies(): array
    {
        return [];
    }
}
