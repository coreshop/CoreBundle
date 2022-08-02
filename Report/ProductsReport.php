<?php
/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) CoreShop GmbH (https://www.coreshop.org)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 */

declare(strict_types=1);

namespace CoreShop\Bundle\CoreBundle\Report;

use Carbon\Carbon;
use CoreShop\Bundle\ResourceBundle\Pimcore\Repository\StackRepository;
use CoreShop\Component\Core\Model\StoreInterface;
use CoreShop\Component\Core\Report\ExportReportInterface;
use CoreShop\Component\Core\Report\ReportInterface;
use CoreShop\Component\Currency\Formatter\MoneyFormatterInterface;
use CoreShop\Component\Locale\Context\LocaleContextInterface;
use CoreShop\Component\Order\OrderStates;
use CoreShop\Component\Resource\Repository\PimcoreRepositoryInterface;
use CoreShop\Component\Resource\Repository\RepositoryInterface;
use Doctrine\DBAL\Connection;
use Pimcore\Bundle\EcommerceFrameworkBundle\Model\AbstractOrder;
use Symfony\Component\HttpFoundation\ParameterBag;

class ProductsReport implements ReportInterface, ExportReportInterface
{
    private int $totalRecords = 0;

    public function __construct(private RepositoryInterface $storeRepository, private Connection $db, private MoneyFormatterInterface $moneyFormatter, private LocaleContextInterface $localeContext, private PimcoreRepositoryInterface $orderRepository, private PimcoreRepositoryInterface $orderItemRepository, private StackRepository $productStackRepository)
    {
    }

    public function getReportData(ParameterBag $parameterBag): array
    {
        $fromFilter = $parameterBag->get('from', strtotime(date('01-m-Y')));
        $toFilter = $parameterBag->get('to', strtotime(date('t-m-Y')));
        $objectTypeFilter = $parameterBag->get('objectType', 'all');
        $orderStateFilter = $parameterBag->get('orderState');
        if($orderStateFilter) {
            $orderStateFilter = \json_decode($orderStateFilter, true);
        }

        if (!is_array($orderStateFilter) || !$orderStateFilter || in_array('all', $orderStateFilter, true)) {
            $orderStateFilter = null;
        }

        $storeId = $parameterBag->get('store');

        if (null === $storeId) {
            return [];
        }

        $from = Carbon::createFromTimestamp($fromFilter);
        $to = Carbon::createFromTimestamp($toFilter);

        $page = $parameterBag->get('page', 1);
        $limit = $parameterBag->get('limit', 50);
        $offset = $parameterBag->get('offset', $page === 1 ? 0 : ($page - 1) * $limit);

        $orderClassId = $this->orderRepository->getClassId();
        $orderItemClassId = $this->orderItemRepository->getClassId();

        $locale = $this->localeContext->getLocaleCode();

        $store = $this->storeRepository->find($storeId);
        if (!$store instanceof StoreInterface) {
            return [];
        }

        $queryParameters = [];
        if ($objectTypeFilter === 'container') {
            $unionData = [];
            foreach ($this->productStackRepository->getClassIds() as $id) {
                $unionData[] = 'SELECT `o_id`, `name`, `o_type` FROM object_localized_' . $id . '_' . $locale;
            }

            $union = implode(' UNION ALL ', $unionData);

            $query = "
              SELECT SQL_CALC_FOUND_ROWS
                products.o_id as productId,
                products.`name` as productName,
                SUM(orderItems.totalGross) AS sales, 
                AVG(orderItems.totalGross) AS salesPrice,
                SUM((orderItems.itemRetailPriceNet - orderItems.itemWholesalePrice) * orderItems.quantity) AS profit,
                SUM(orderItems.quantity) AS `quantityCount`,
                COUNT(`order`.oo_id) AS `orderCount`
                FROM ($union) AS products
                INNER JOIN object_query_$orderItemClassId AS orderItems ON products.o_id = orderItems.mainObjectId
                INNER JOIN object_relations_$orderClassId AS orderRelations ON orderRelations.dest_id = orderItems.oo_id AND orderRelations.fieldname = \"items\"
                INNER JOIN object_query_$orderClassId AS `order` ON `order`.oo_id = orderRelations.src_id
                WHERE products.o_type = 'object' AND `order`.store = $storeId".(($orderStateFilter !== null) ? " AND `order`.orderState IN (".rtrim(str_repeat('?,', count($orderStateFilter)), ',').")":"")." AND `order`.orderDate > ? AND `order`.orderDate < ?
                GROUP BY products.o_id
            LIMIT $offset,$limit";
        } else {
            $productTypeCondition = '1=1';
            if ($objectTypeFilter === 'object') {
                $productTypeCondition = 'orderItems.mainObjectId = NULL';
            } elseif ($objectTypeFilter === 'variant') {
                $productTypeCondition = 'orderItems.mainObjectId IS NOT NULL';
            }

            $query = "
                SELECT SQL_CALC_FOUND_ROWS
                  orderItems.objectId as productId,
                  orderItemsTranslated.name AS `productName`,
                  
                  SUM(orderItems.totalGross) AS sales, 
                  AVG(orderItems.totalGross) AS salesPrice,
                  SUM((orderItems.itemRetailPriceNet - orderItems.itemWholesalePrice) * orderItems.quantity) AS profit,
                  
                  SUM(orderItems.quantity) AS `quantityCount`,
                  COUNT(orderItems.objectId) AS `orderCount`
                FROM object_query_$orderClassId AS orders
                INNER JOIN object_relations_$orderClassId AS orderRelations ON orderRelations.src_id = orders.oo_id AND orderRelations.fieldname = \"items\"
                INNER JOIN object_query_$orderItemClassId AS orderItems ON orderRelations.dest_id = orderItems.oo_id
                INNER JOIN object_localized_query_" . $orderItemClassId . '_' . $locale . " AS orderItemsTranslated ON orderItems.oo_id = orderItemsTranslated.ooo_id
                WHERE `orders`.store = $storeId AND $productTypeCondition".(($orderStateFilter !== null) ? " AND `orders`.orderState IN (".rtrim(str_repeat('?,', count($orderStateFilter)),',').")" : "")." AND `orders`.orderDate > ? AND `orders`.orderDate < ?
                GROUP BY orderItems.objectId
                ORDER BY orderCount DESC
                LIMIT $offset,$limit";
        }
        if ($orderStateFilter !== null) {
            array_push($queryParameters, ...$orderStateFilter);
        }
        $queryParameters[] = $from->getTimestamp();
        $queryParameters[] = $to->getTimestamp();


        $productSales = $this->db->fetchAllAssociative($query, $queryParameters);

        $this->totalRecords = (int)$this->db->fetchOne('SELECT FOUND_ROWS()');

        foreach ($productSales as &$sale) {
            $sale['salesPriceFormatted'] = $this->moneyFormatter->format((int)$sale['salesPrice'], $store->getCurrency()->getIsoCode(), $locale);
            $sale['salesFormatted'] = $this->moneyFormatter->format((int)$sale['sales'], $store->getCurrency()->getIsoCode(), $locale);
            $sale['profitFormatted'] = $this->moneyFormatter->format((int)$sale['profit'], $store->getCurrency()->getIsoCode(), $locale);
            $sale['name'] = $sale['productName'] . ' (Id: ' . $sale['productId'] . ')';
        }

        return array_values($productSales);
    }

    public function getExportReportData(ParameterBag $parameterBag): array
    {
        $data = $this->getReportData($parameterBag);

        foreach ($data as &$entry) {
            unset($entry['salesPrice'], $entry['sales'], $entry['profit']);
        }

        return $data;
    }

    public function getTotal(): int
    {
        return $this->totalRecords;
    }
}
