<?php
/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) CoreShop GmbH (https://www.coreshop.org)
 * @license    https://www.coreshop.org/license     GPLv3 and CCL
 */

declare(strict_types=1);

namespace CoreShop\Bundle\CoreBundle\Doctrine\ORM;

use CoreShop\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use CoreShop\Component\Core\Repository\CarrierRepositoryInterface;
use CoreShop\Component\Store\Model\StoreInterface;

class CarrierRepository extends EntityRepository implements CarrierRepositoryInterface
{
    public function findForStore(StoreInterface $store): array
    {
        return $this->createQueryBuilder('o')
            ->innerJoin('o.stores', 's')
            ->andWhere('s.id = :store')
            ->andWhere('o.hideFromCheckout = 0')
            ->setParameter('store', [$store])
            ->getQuery()
            ->getResult();
    }
}
