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

namespace CoreShop\Bundle\CoreBundle\Doctrine\ORM;

use CoreShop\Bundle\PaymentBundle\Doctrine\ORM\PaymentProviderRepository as BasePaymentProviderRepository;
use CoreShop\Component\Core\Repository\PaymentProviderRepositoryInterface;
use CoreShop\Component\Store\Model\StoreInterface;

class PaymentProviderRepository extends BasePaymentProviderRepository implements PaymentProviderRepositoryInterface
{
    public function findActiveForStore(StoreInterface $store): array
    {
        return $this->createQueryBuilder('o')
            ->innerJoin('o.stores', 's')
            ->andWhere('o.active = true')
            ->andWhere('s.id = :storeId')
            ->addOrderBy('o.position')
            ->setParameter('storeId', $store->getId())
            ->getQuery()
            ->getResult()
        ;
    }
}
