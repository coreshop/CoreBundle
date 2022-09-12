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

namespace CoreShop\Bundle\CoreBundle\Report;

use Carbon\Carbon;
use CoreShop\Component\Core\Model\StoreInterface;
use CoreShop\Component\Core\Report\ReportInterface;
use CoreShop\Component\Resource\Repository\PimcoreRepositoryInterface;
use CoreShop\Component\Resource\Repository\RepositoryInterface;
use CoreShop\Component\Shipping\Model\CarrierInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\ParameterBag;

class CarriersReport implements ReportInterface
{
    private int $totalRecords = 0;

    public function __construct(
        private RepositoryInterface $storeRepository,
        private Connection $db,
        private RepositoryInterface $carrierRepository,
        private PimcoreRepositoryInterface $orderRepository,
    ) {
    }

    public function getReportData(ParameterBag $parameterBag): array
    {
        $fromFilter = $parameterBag->get('from', strtotime(date('01-m-Y')));
        $toFilter = $parameterBag->get('to', strtotime(date('t-m-Y')));
        $storeId = $parameterBag->get('store', null);

        $from = Carbon::createFromTimestamp($fromFilter);
        $to = Carbon::createFromTimestamp($toFilter);
        $fromTimestamp = $from->getTimestamp();
        $toTimestamp = $to->getTimestamp();

        if (null === $storeId) {
            return [];
        }

        $store = $this->storeRepository->find($storeId);
        if (!$store instanceof StoreInterface) {
            return [];
        }

        $tableName = 'object_query_' . $this->orderRepository->getClassId();
        $sql = "
              SELECT carrier, 
                    COUNT(1) as total, 
                    COUNT(1) / t.cnt * 100 as `percentage` 
              FROM $tableName as `order` 
              INNER JOIN objects as o 
                ON o.o_id = `order`.oo_id 
              CROSS JOIN 
                (
                  SELECT COUNT(1) as cnt 
                  FROM $tableName as `order` 
                  INNER JOIN objects as o 
                    ON o.o_id = `order`.oo_id  
                  WHERE store = $storeId AND o_creationDate > $fromTimestamp AND o_creationDate < $toTimestamp
                ) t 
              WHERE store = $storeId AND carrier IS NOT NULL AND o_creationDate > $fromTimestamp AND o_creationDate < $toTimestamp GROUP BY carrier";

        $results = $this->db->fetchAllAssociative($sql);
        $data = [];

        foreach ($results as $result) {
            $carrier = null;

            if ($result['carrier']) {
                $carrier = $this->carrierRepository->find($result['carrier']);
            }

            $data[] = [
                'carrier' => $carrier instanceof CarrierInterface ? $carrier->getIdentifier() : $result['carrier'],
                'data' => (float) $result['percentage'],
            ];
        }

        return $data;
    }

    public function getTotal(): int
    {
        return $this->totalRecords;
    }
}
