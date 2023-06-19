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

namespace CoreShop\Bundle\CoreBundle\Fixtures\Data\Application;

use CoreShop\Component\Core\Configuration\ConfigurationServiceInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class ConfigurationFixture extends Fixture implements FixtureGroupInterface
{
    public function __construct(
        private ConfigurationServiceInterface $configurationService,
    ) {
    }

    public static function getGroups(): array
    {
        return ['application'];
    }

    public function load(ObjectManager $manager): void
    {
        $configurations = [
            'system.guest.checkout' => true,
            'system.category.list.mode' => 'list',
            'system.category.list.per_page' => [12, 24, 36],
            'system.category.list.per_page.default' => 12,
            'system.category.grid.per_page' => [5, 10, 15, 20, 25],
            'system.category.grid.per_page.default' => 10,
            'system.category.variant_mode' => 'hide',
            'system.order.prefix' => 'O',
            'system.order.suffix' => '',
            'system.quote.prefix' => 'Q',
            'system.quote.suffix' => '',
            'system.invoice.prefix' => 'IN',
            'system.invoice.suffix' => '',
            'system.invoice.wkhtml' => '-T 40mm -B 15mm -L 10mm -R 10mm --header-spacing 5 --footer-spacing 5',
            'system.shipment.prefix' => 'SH',
            'system.shipment.suffix' => '',
            'system.shipment.wkhtml' => '-T 40mm -B 15mm -L 10mm -R 10mm --header-spacing 5 --footer-spacing 5',
        ];

        foreach ($configurations as $key => $value) {
            $this->configurationService->set($key, $value);
        }
    }
}
