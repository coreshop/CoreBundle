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

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Version20191122074930 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function up(Schema $schema): void
    {
        if (!$schema->getTable('coreshop_carrier')->hasColumn('taxCalculationStrategy')) {
            $this->addSql('ALTER TABLE coreshop_carrier ADD `taxCalculationStrategy` VARCHAR(255) DEFAULT NULL AFTER logo;');
            $this->addSql("UPDATE coreshop_carrier SET `taxCalculationStrategy` = 'taxRule' WHERE `taxRuleGroupId` IS NOT NULL;");

            $this->container->get('pimcore.cache.core.handler')->clearTag('doctrine_pimcore_cache');
        }
    }

    public function down(Schema $schema): void
    {
        // do nothing due to potential data loss
    }
}
