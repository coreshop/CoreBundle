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
use Pimcore\Model\User\Permission\Definition;

final class Version20240421093551 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $permission = Definition::getByKey('coreshop_permission_messenger');

        if (null === $permission) {
            $permission = new Definition();
            $permission->setKey('coreshop_permission_messenger');
            $permission->setCategory('coreshop_permission_group_coreshop');
            $permission->save();
        }
    }

    public function down(Schema $schema): void
    {
    }
}
