<?php

declare(strict_types=1);

/*
 * CoreShop
 *
 * This source file is available under two different licenses:
 *  - GNU General Public License version 3 (GPLv3)
 *  - CoreShop Commercial License (CCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) CoreShop GmbH (https://www.coreshop.org)
 * @license    https://www.coreshop.org/license     GPLv3 and CCL
 *
 */

namespace CoreShop\Bundle\CoreBundle\Pimcore\Repository;

use CoreShop\Component\Core\Wishlist\Repository\WishlistRepositoryInterface;
use CoreShop\Component\Customer\Model\CustomerInterface;
use CoreShop\Component\Store\Model\StoreInterface;
use CoreShop\Component\Wishlist\Model\WishlistInterface;
use CoreShop\Bundle\WishlistBundle\Pimcore\Repository\WishlistRepository as BaseWishlistRepository;
class WishlistRepository extends BaseWishlistRepository implements WishlistRepositoryInterface
{
    public function findLatestByStoreAndCustomer(
        StoreInterface $store,
        CustomerInterface $customer,
    ): ?WishlistInterface {
        $list = $this->getList();
        $list->setCondition('customer__id = ? AND store = ?', [$customer->getId(), $store->getId()]);
        $list->setOrderKey('o_creationDate');
        $list->setOrder('DESC');
        $list->load();

        $objects = $list->getObjects();

        if (count($objects) > 0 && $objects[0] instanceof WishlistInterface) {
            return $objects[0];
        }

        return null;
    }
}
