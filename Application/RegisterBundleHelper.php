<?php
/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2015-2017 Dominik Pfaffenbauer (https://www.pfaffenbauer.at)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
*/

namespace CoreShop\Bundle\CoreBundle\Application;

use Pimcore\HttpKernel\BundleCollection\BundleCollection;

class RegisterBundleHelper
{
    /**
     * @param BundleCollection $collection
     */
    public static function registerBundles(BundleCollection $collection) {
        $collection->addBundle(new \JMS\SerializerBundle\JMSSerializerBundle(), 3900);
        $collection->addBundle(new \CoreShop\Bundle\ResourceBundle\CoreShopResourceBundle(), 3800);
        $collection->addBundle(new \CoreShop\Bundle\FixtureBundle\CoreShopFixtureBundle(), 3700);
        $collection->addBundle(new \CoreShop\Bundle\MoneyBundle\CoreShopMoneyBundle(), 3600);
        $collection->addBundle(new \CoreShop\Bundle\RuleBundle\CoreShopRuleBundle(), 3500);
        $collection->addBundle(new \CoreShop\Bundle\LocaleBundle\CoreShopLocaleBundle(), 3400);
        $collection->addBundle(new \CoreShop\Bundle\ConfigurationBundle\CoreShopConfigurationBundle(), 3300);
        $collection->addBundle(new \CoreShop\Bundle\OrderBundle\CoreShopOrderBundle(), 3200);
        $collection->addBundle(new \CoreShop\Bundle\CustomerBundle\CoreShopCustomerBundle(), 3100);
        $collection->addBundle(new \CoreShop\Bundle\InventoryBundle\CoreShopInventoryBundle(), 3000);
        $collection->addBundle(new \CoreShop\Bundle\ProductBundle\CoreShopProductBundle(), 2900);
        $collection->addBundle(new \CoreShop\Bundle\AddressBundle\CoreShopAddressBundle(), 2800);
        $collection->addBundle(new \CoreShop\Bundle\CurrencyBundle\CoreShopCurrencyBundle(), 2700);
        $collection->addBundle(new \CoreShop\Bundle\TaxationBundle\CoreShopTaxationBundle(), 2600);
        $collection->addBundle(new \CoreShop\Bundle\StoreBundle\CoreShopStoreBundle(), 2500);
        $collection->addBundle(new \CoreShop\Bundle\IndexBundle\CoreShopIndexBundle(), 2400);
        $collection->addBundle(new \CoreShop\Bundle\ShippingBundle\CoreShopShippingBundle(),2300);
        $collection->addBundle(new \CoreShop\Bundle\PaymentBundle\CoreShopPaymentBundle(), 2200);
        $collection->addBundle(new \CoreShop\Bundle\SequenceBundle\CoreShopSequenceBundle(), 2100);
        $collection->addBundle(new \CoreShop\Bundle\NotificationBundle\CoreShopNotificationBundle(), 2000);
        $collection->addBundle(new \CoreShop\Bundle\TrackingBundle\CoreShopTrackingBundle(), 1900);
        $collection->addBundle(new \CoreShop\Bundle\FrontendBundle\CoreShopFrontendBundle(), 1800);
        $collection->addBundle(new \CoreShop\Bundle\PayumBundle\CoreShopPayumBundle(), 1700);
        $collection->addBundle(new \CoreShop\Bundle\CoreBundle\CoreShopCoreBundle(), 1600);
        $collection->addBundle(new \FOS\RestBundle\FOSRestBundle(), 1500);
        $collection->addBundle(new \Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle(), 1400);
        $collection->addBundle(new \Payum\Bundle\PayumBundle\PayumBundle(), 1300);
        $collection->addBundle(new \Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(), 1200);
        $collection->addBundle(new \Liip\ThemeBundle\LiipThemeBundle(), 1100);
        $collection->addBundle(new \EmailizrBundle\EmailizrBundle(), 1000);
    }
}