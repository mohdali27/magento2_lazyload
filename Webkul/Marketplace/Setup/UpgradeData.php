<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Webkul\Marketplace\Model\ControllersRepository;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * Upgrade Data script
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var ControllersRepository
     */
    private $controllersRepository;
    
    /**
        * @var \Magento\Framework\Filesystem\Io\File
        */
    protected $_filesystemFile;

    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param ControllersRepository                 $controllersRepository
     * @param \Magento\Framework\Filesystem\Io\File $filesystemFile
     * @param EavSetupFactory                       $eavSetupFactory
     */
    public function __construct(
        ControllersRepository $controllersRepository,
        \Magento\Framework\Filesystem\Io\File $filesystemFile,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->controllersRepository = $controllersRepository;
        $this->_filesystemFile = $filesystemFile;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->moveDirToMediaDir();
        $setup->startSetup();
        /**
         * insert marketplace controller's data
         */
        $data = [];
        if (!count($this->controllersRepository->getByPath('marketplace/account/dashboard'))) {
            $data[] = [
                'module_name' => 'Webkul_Marketplace',
                'controller_path' => 'marketplace/account/dashboard',
                'label' => 'Marketplace Dashboard',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        if (!count($this->controllersRepository->getByPath('marketplace/account/editprofile'))) {
            $data[] = [
                'module_name' => 'Webkul_Marketplace',
                'controller_path' => 'marketplace/account/editprofile',
                'label' => 'Seller Profile',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        if (!count($this->controllersRepository->getByPath('marketplace/product_attribute/new'))) {
            $data[] = [
                'module_name' => 'Webkul_Marketplace',
                'controller_path' => 'marketplace/product_attribute/new',
                'label' => 'Create Attribute',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        if (!count($this->controllersRepository->getByPath('marketplace/product/add'))) {
            $data[] = [
                'module_name' => 'Webkul_Marketplace',
                'controller_path' => 'marketplace/product/add',
                'label' => 'New Products',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        if (!count($this->controllersRepository->getByPath('marketplace/product/productlist'))) {
            $data[] = [
                'module_name' => 'Webkul_Marketplace',
                'controller_path' => 'marketplace/product/productlist',
                'label' => 'My Products List',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        if (!count($this->controllersRepository->getByPath('marketplace/account/customer'))) {
            $data[] = [
                'module_name' => 'Webkul_Marketplace',
                'controller_path' => 'marketplace/account/customer',
                'label' => 'My Customer List',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        if (!count($this->controllersRepository->getByPath('marketplace/account/review'))) {
            $data[] = [
                'module_name' => 'Webkul_Marketplace',
                'controller_path' => 'marketplace/account/review',
                'label' => 'My Review List',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        if (!count($this->controllersRepository->getByPath('marketplace/transaction/history'))) {
            $data[] = [
                'module_name' => 'Webkul_Marketplace',
                'controller_path' => 'marketplace/transaction/history',
                'label' => 'My Transaction List',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        if (!count($this->controllersRepository->getByPath('marketplace/order/shipping'))) {
            $data[] = [
                'module_name' => 'Webkul_Marketplace',
                'controller_path' => 'marketplace/order/shipping',
                'label' => 'Manage Print PDF Header Info',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        if (!count($this->controllersRepository->getByPath('marketplace/order/history'))) {
            $data[] = [
                'module_name' => 'Webkul_Marketplace',
                'controller_path' => 'marketplace/order/history',
                'label' => 'My Order History',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        if (count($data)) {
            $setup->getConnection()
                ->insertMultiple($setup->getTable('marketplace_controller_list'), $data);
        }

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'mp_product_cart_limit',
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Product Purchase Limit for Customer',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to'     => 'simple,configurable,bundle',
                'frontend_class'=>'validate-zero-or-greater',
                'note' => 'Not applicable on downloadable and virtual product.'
            ]
        );

        $setup->endSetup();
    }

    private function moveDirToMediaDir()
    {
        /** @var \Magento\Framework\ObjectManagerInterface $objManager */
        $objManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\Module\Dir\Reader $reader */
        $reader = $objManager->get('Magento\Framework\Module\Dir\Reader');

        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = $objManager->get('Magento\Framework\Filesystem');

        $mediaAvatarFullPath = $filesystem->getDirectoryRead(
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
        )->getAbsolutePath('avatar');
        if (!$this->_filesystemFile->fileExists($mediaAvatarFullPath)) {
            $this->_filesystemFile->mkdir($mediaAvatarFullPath, 0777, true);
            $avatarBannerImage = $reader->getModuleDir(
                '',
                'Webkul_Marketplace'
            ).'/view/base/web/images/avatar/banner-image.png';
            $this->_filesystemFile->cp(
                $avatarBannerImage,
                $mediaAvatarFullPath.'/banner-image.png'
            );
            $avatarNoImage = $reader->getModuleDir(
                '',
                'Webkul_Marketplace'
            ).'/view/base/web/images/avatar/noimage.png';
            $this->_filesystemFile->cp($avatarNoImage, $mediaAvatarFullPath.'/noimage.png');
        }

        $mediaMarketplaceFullPath = $filesystem->getDirectoryRead(
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
        )->getAbsolutePath('marketplace');
        if (!$this->_filesystemFile->fileExists($mediaMarketplaceFullPath)) {
            $this->_filesystemFile->mkdir($mediaMarketplaceFullPath, 0777, true);
        }

        $mediaMarketplaceBannerFullPath = $filesystem->getDirectoryRead(
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
        )->getAbsolutePath('marketplace/banner');
        if (!$this->_filesystemFile->fileExists($mediaMarketplaceBannerFullPath)) {
            $this->_filesystemFile->mkdir($mediaMarketplaceBannerFullPath, 0777, true);
            $marketplaceBannerImage = $reader->getModuleDir(
                '',
                'Webkul_Marketplace'
            ).'/view/base/web/images/marketplace/banner/sell-page-banner.png';
            $this->_filesystemFile->cp(
                $marketplaceBannerImage,
                $mediaMarketplaceBannerFullPath.'/sell-page-banner.png'
            );
            // for landing page layout 2
            $marketplaceBannerImage = $reader->getModuleDir(
                '',
                'Webkul_Marketplace'
            ).'/view/base/web/images/landingpage1/banner/sell-page-1-hero-banner.jpg';
            $this->_filesystemFile->cp(
                $marketplaceBannerImage,
                $mediaMarketplaceBannerFullPath.'/sell-page-1-hero-banner.jpg'
            );
            // for landing page layout 3
            $marketplaceBannerImage = $reader->getModuleDir(
                '',
                'Webkul_Marketplace'
            ).'/view/base/web/images/landingpage2/banner/sell-page-2-hero-banner.jpg';
            $this->_filesystemFile->cp(
                $marketplaceBannerImage,
                $mediaMarketplaceBannerFullPath.'/sell-page-2-hero-banner.jpg'
            );
        }

        $mediaMarketplaceIconFullPath = $filesystem->getDirectoryRead(
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
        )->getAbsolutePath('marketplace/icon');
        if (!$this->_filesystemFile->fileExists($mediaMarketplaceIconFullPath)) {
            $this->_filesystemFile->mkdir($mediaMarketplaceIconFullPath, 0777, true);
            $icon1BannerImage = $reader->getModuleDir(
                '',
                'Webkul_Marketplace'
            ).'/view/base/web/images/marketplace/icon/icon-add-products.png';
            $this->_filesystemFile->cp(
                $icon1BannerImage,
                $mediaMarketplaceIconFullPath.'/icon-add-products.png'
            );

            $icon2BannerImage = $reader->getModuleDir(
                '',
                'Webkul_Marketplace'
            ).'/view/base/web/images/marketplace/icon/icon-collect-revenues.png';
            $this->_filesystemFile->cp(
                $icon2BannerImage,
                $mediaMarketplaceIconFullPath.'/icon-collect-revenues.png'
            );

            $icon3BannerImage = $reader->getModuleDir(
                '',
                'Webkul_Marketplace'
            ).'/view/base/web/images/marketplace/icon/icon-register-yourself.png';
            $this->_filesystemFile->cp(
                $icon3BannerImage,
                $mediaMarketplaceIconFullPath.'/icon-register-yourself.png'
            );

            $icon4BannerImage = $reader->getModuleDir(
                '',
                'Webkul_Marketplace'
            ).'/view/base/web/images/marketplace/icon/icon-start-selling.png';
            $this->_filesystemFile->cp(
                $icon4BannerImage,
                $mediaMarketplaceIconFullPath.'/icon-start-selling.png'
            );

            // for landing page layout 3
            $iconBannerImage = $reader->getModuleDir(
                '',
                'Webkul_Marketplace'
            ).'/view/base/web/images/landingpage2/icon/sell-page-2-setup-1.png';
            $this->_filesystemFile->cp(
                $iconBannerImage,
                $mediaMarketplaceIconFullPath.'/sell-page-2-setup-1.png'
            );
            $iconBannerImage = $reader->getModuleDir(
                '',
                'Webkul_Marketplace'
            ).'/view/base/web/images/landingpage2/icon/sell-page-2-setup-2.png';
            $this->_filesystemFile->cp(
                $iconBannerImage,
                $mediaMarketplaceIconFullPath.'/sell-page-2-setup-2.png'
            );
            $iconBannerImage = $reader->getModuleDir(
                '',
                'Webkul_Marketplace'
            ).'/view/base/web/images/landingpage2/icon/sell-page-2-setup-3.png';
            $this->_filesystemFile->cp(
                $iconBannerImage,
                $mediaMarketplaceIconFullPath.'/sell-page-2-setup-3.png'
            );
            $iconBannerImage = $reader->getModuleDir(
                '',
                'Webkul_Marketplace'
            ).'/view/base/web/images/landingpage2/icon/sell-page-2-setup-4.png';
            $this->_filesystemFile->cp(
                $iconBannerImage,
                $mediaMarketplaceIconFullPath.'/sell-page-2-setup-4.png'
            );
            $iconBannerImage = $reader->getModuleDir(
                '',
                'Webkul_Marketplace'
            ).'/view/base/web/images/landingpage2/icon/sell-page-2-setup-5.png';
            $this->_filesystemFile->cp(
                $iconBannerImage,
                $mediaMarketplaceIconFullPath.'/sell-page-2-setup-5.png'
            );
        }

        $mediaPlaceholderFullPath = $filesystem->getDirectoryRead(
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
        )->getAbsolutePath('placeholder');
        if (!$this->_filesystemFile->fileExists($mediaPlaceholderFullPath)) {
            $this->_filesystemFile->mkdir($mediaPlaceholderFullPath, 0777, true);
            $placeholderImage = $reader->getModuleDir(
                '',
                'Webkul_Marketplace'
            ).'/view/base/web/images/placeholder/image.jpg';
            $this->_filesystemFile->cp(
                $placeholderImage,
                $mediaMarketplaceIconFullPath.'/image.jpg'
            );
        }
    }
}
