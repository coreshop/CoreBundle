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
use CoreShop\Component\Currency\Formatter\MoneyFormatterInterface;
use CoreShop\Component\Locale\Context\LocaleContextInterface;
use CoreShop\Component\Order\OrderSaleStates;
use CoreShop\Component\Resource\Repository\PimcoreRepositoryInterface;
use CoreShop\Component\Resource\Repository\RepositoryInterface;
use Doctrine\DBAL\Connection;
use Pimcore\Model\DataObject\ClassDefinition;
use Symfony\Component\HttpFoundation\ParameterBag;

class ManufacturerReport implements ReportInterface
{
    private int $totalRecords = 0;

    public function __construct(
        private RepositoryInterface $storeRepository,
        private Connection $db,
        private MoneyFormatterInterface $moneyFormatter,
        private LocaleContextInterface $localeService,
        private PimcoreRepositoryInterface $manufacturerRepository,
        private PimcoreRepositoryInterface $orderRepository,
        private PimcoreRepositoryInterface $orderItemRepository,
    ) {
    }

    public function getReportData(ParameterBag $parameterBag): array
    {
        $fromFilter = $parameterBag->get('from', strtotime(date('01-m-Y')));
        $toFilter = $parameterBag->get('to', strtotime(date('t-m-Y')));
        $storeId = $parameterBag->get('store', null);
        $from = Carbon::createFromTimestamp($fromFilter);
        $to = Carbon::createFromTimestamp($toFilter);

        $page = $parameterBag->get('page', 1);
        $limit = $parameterBag->get('limit', 25);
        $offset = $parameterBag->get('offset', $page === 1 ? 0 : ($page - 1) * $limit);

        $orderClassId = $this->orderRepository->getClassId();
        $manufacturerClassId = $this->manufacturerRepository->getClassId();
        $orderItemClassId = $this->orderItemRepository->getClassId();
        $orderStateFilter = $parameterBag->get('orderState');
        if ($orderStateFilter) {
            $orderStateFilter = \json_decode($orderStateFilter, true);
        }

        if (!is_array($orderStateFilter) || !$orderStateFilter) {
            $orderStateFilter = null;
        }

        if (null === $storeId) {
            return [];
        }

        $store = $this->storeRepository->find($storeId);
        if (!$store instanceof StoreInterface) {
            return [];
        }

        $nameIsLocalized = false;
        $manufacturerClass = ClassDefinition::getById($manufacturerClassId);
        if (!$manufacturerClass->getFieldDefinition('name')) {
            $localizedFields = $manufacturerClass->getFieldDefinition('localizedfields');
            if ($localizedFields instanceof ClassDefinition\Data\Localizedfields && $localizedFields->getFieldDefinition('name')) {
                $nameIsLocalized = true;
            }
        }

        $query = '
            SELECT SQL_CALC_FOUND_ROWS
              `manufacturers`.oo_id as manufacturerId,
              `manufacturers`.name as manufacturerName,
              `manufacturers`.key as manufacturerKey,
              `orders`.store,
              SUM(orderItems.totalGross) AS sales,
              SUM((orderItems.itemRetailPriceNet - orderItems.itemWholesalePrice) * orderItems.quantity) AS profit,
              SUM(orderItems.quantity) AS `quantityCount`,
              COUNT(orderItems.product__id) AS `orderCount`
            FROM ' . ($nameIsLocalized ? 'object_localized_' . $manufacturerClassId . '_' . $this->localeService->getLocaleCode() : 'object_' . $manufacturerClassId) . " AS manufacturers
            INNER JOIN dependencies AS manProductDependencies ON manProductDependencies.targetId = manufacturers.oo_id AND manProductDependencies.targettype = \"object\" 
            INNER JOIN object_query_$orderItemClassId AS orderItems ON orderItems.product__id = manProductDependencies.sourceid
            INNER JOIN object_relations_$orderClassId AS orderRelations ON orderRelations.dest_id = orderItems.oo_id AND orderRelations.fieldname = \"items\"
            INNER JOIN object_query_$orderClassId AS `orders` ON `orders`.oo_id = orderRelations.src_id
            WHERE orders.store = $storeId" . (($orderStateFilter !== null) ? ' AND `orders`.orderState IN (' . rtrim(str_repeat('?,', count($orderStateFilter)), ',') . ')' : '') . " AND orders.orderDate > ? AND orders.orderDate < ? AND orderItems.product__id IS NOT NULL AND saleState='" . OrderSaleStates::STATE_ORDER . "'
            GROUP BY manufacturers.oo_id
            ORDER BY quantityCount DESC
            LIMIT $offset,$limit";

        $queryParameters = [];
        if ($orderStateFilter !== null) {
            array_push($queryParameters, ...$orderStateFilter);
        }
        $queryParameters[] = $from->getTimestamp();
        $queryParameters[] = $to->getTimestamp();
        $results = $this->db->fetchAllAssociative($query, $queryParameters);

        $this->totalRecords = (int) $this->db->fetchOne('SELECT FOUND_ROWS()');

        $data = [];
        foreach ($results as $result) {
            $name = !empty($result['manufacturerName']) ? $result['manufacturerName'] : $result['manufacturerKey'];
            $data[] = [
                'name' => sprintf('%s (Id: %d)', $name, $result['manufacturerId']),
                'manufacturerName' => $name,
                'sales' => $result['sales'],
                'profit' => $result['profit'],
                'quantityCount' => $result['quantityCount'],
                'orderCount' => $result['orderCount'],
                'salesFormatted' => $this->moneyFormatter->format((int) $result['sales'], $store->getCurrency()->getIsoCode(), $this->localeService->getLocaleCode()),
                'profitFormatted' => $this->moneyFormatter->format((int) $result['profit'], $store->getCurrency()->getIsoCode(), $this->localeService->getLocaleCode()),
            ];
        }

        return $data;
    }

    /**
     * {@@inheritdoc}
     */
    public function getTotal(): int
    {
        return $this->totalRecords;
    }
}
