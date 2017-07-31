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

namespace CoreShop\Bundle\CoreBundle\EventListener;

use CoreShop\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use CoreShop\Component\Core\Configuration\ConfigurationServiceInterface;
use CoreShop\Component\Order\Model\PurchasableInterface;
use CoreShop\Component\Rule\Model\RuleInterface;
use Pimcore\Event\Model\ObjectEvent;
use Webmozart\Assert\Assert;

final class ProductUpdateEventListener
{
    /**
     * @var ConfigurationServiceInterface
     */
    private $configurationService;

    /**
     * @param ConfigurationServiceInterface $configurationService
     */
    public function __construct(ConfigurationServiceInterface $configurationService)
    {
        $this->configurationService = $configurationService;
    }

    /**
     * @param ObjectEvent $event
     */
    public function storeConfigurationThatProductChanged(ObjectEvent $event)
    {
        $object = $event->getObject();

        if (!$object instanceof PurchasableInterface) {
            return;
        }

        $this->configurationService->set('SYSTEM.PRICE_RULE.UPDATE', time());
    }
}