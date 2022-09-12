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

namespace CoreShop\Bundle\CoreBundle\Migrations;

use CoreShop\Component\Pimcore\DataObject\ClassInstallerInterface;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Pimcore\Model\DataObject\CoreShopWishlist;
use Pimcore\Model\DataObject\CoreShopWishlistItem;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

final class Version20220824065814 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $classInstaller = $this->container->get(ClassInstallerInterface::class);
        $kernel = $this->container->get('kernel');

        if (!$classInstaller instanceof ClassInstallerInterface) {
            return;
        }

        if (!$kernel) {
            return;
        }

        if (!class_exists(CoreShopWishlist::class)) {
            $classInstaller->createClass($kernel->locateResource('@CoreShopCoreBundle/Resources/install/pimcore/classes/CoreShopWishlistBundle/CoreShopWishlist.json'), 'CoreShopWishlist');
        }

        if (!class_exists(CoreShopWishlistItem::class)) {
            $classInstaller->createClass($kernel->locateResource('@CoreShopWishlistBundle/Resources/install/pimcore/classes/CoreShopWishlistItem.json'), 'CoreShopWishlistItem');
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
