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

namespace CoreShop\Bundle\CoreBundle\Migrations\Data\ORM;

use CoreShop\Component\Core\Model\CurrencyInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Okvpn\Bundle\MigrationBundle\Fixture\VersionedFixtureInterface;
use Rinvex\Country\Country;
use Rinvex\Country\CountryLoader;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Intl\Intl;

class CurrencyFixture extends AbstractFixture implements ContainerAwareInterface, VersionedFixtureInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '2.0';
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $countries = CountryLoader::countries(true, true);
        $currencies = [];

        foreach ($countries as $country) {
            if ($country instanceof Country) {
                $currency = $country->getCurrency();

                if ($currency) {
                    $isoCode = $currency['iso_4217_code'];

                    if ($isoCode) {
                        if (!array_key_exists($isoCode, $currencies)) {
                            $currencies[$isoCode] = $currency;
                        }
                    }
                }
            }
        }

        foreach ($currencies as $iso => $c) {
            /**
             * @var CurrencyInterface
             */
            $currency = $this->container->get('coreshop.factory.currency')->createNew();
            $currency->setName($c['iso_4217_name']);
            $currency->setIsoCode($iso);
            $currency->setNumericIsoCode($c['iso_4217_numeric']);
            $currency->setSymbol(Intl::getCurrencyBundle()->getCurrencySymbol($iso));
            $currency->setExchangeRate(0);

            $manager->persist($currency);
        }

        $manager->flush();
    }
}
