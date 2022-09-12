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

namespace CoreShop\Bundle\CoreBundle\Twig;

use CoreShop\Component\Address\Model\AddressInterface;
use CoreShop\Component\Core\Model\ProductInterface;
use CoreShop\Component\Core\Model\QuantityRangeInterface;
use CoreShop\Component\Core\Product\ProductTaxCalculatorFactoryInterface;
use CoreShop\Component\Core\Provider\DefaultTaxAddressProviderInterface;
use CoreShop\Component\Core\Taxation\TaxApplicatorInterface;
use CoreShop\Component\Order\Calculator\PurchasableCalculatorInterface;
use CoreShop\Component\Order\Model\PurchasableInterface;
use CoreShop\Component\ProductQuantityPriceRules\Detector\QuantityReferenceDetectorInterface;
use CoreShop\Component\Taxation\Calculator\TaxCalculatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ProductQuantityPriceRuleRangesPriceExtension extends AbstractExtension
{
    public function __construct(
        private QuantityReferenceDetectorInterface $quantityReferenceDetector,
        private PurchasableCalculatorInterface $purchasableCalculator,
        private DefaultTaxAddressProviderInterface $defaultTaxAddressProvider,
        private ProductTaxCalculatorFactoryInterface $taxCalculatorFactory,
        private TaxApplicatorInterface $taxApplicator,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('coreshop_quantity_price_rule_range_price', [$this, 'getQuantityPriceRuleRangePrice']),
        ];
    }

    public function getQuantityPriceRuleRangePrice(
        QuantityRangeInterface $range,
        ProductInterface $product,
        array $context,
        bool $withTax = true,
    ): int {
        $realItemPrice = $this->purchasableCalculator->getPrice($product, $context);
        $price = $this->quantityReferenceDetector->detectRangePrice($product, $range, $realItemPrice, $context);

        $taxCalculator = $this->getTaxCalculator($product, $context);

        if ($taxCalculator instanceof TaxCalculatorInterface) {
            return $this->taxApplicator->applyTax($price, $context, $taxCalculator, $withTax);
        }

        return $price;
    }

    protected function getTaxCalculator(PurchasableInterface $product, array $context): ?TaxCalculatorInterface
    {
        return $this->taxCalculatorFactory->getTaxCalculator($product, $this->getDefaultAddress($context), $context);
    }

    protected function getDefaultAddress(array $context): ?AddressInterface
    {
        return $this->defaultTaxAddressProvider->getAddress($context);
    }
}
