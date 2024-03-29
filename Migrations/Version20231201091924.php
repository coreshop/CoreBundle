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

final class Version20231201091924 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            '
            CREATE TABLE coreshop_cart_price_rule_voucher_code_customer (id INT AUTO_INCREMENT NOT NULL, customerId INT NOT NULL, uses INT NOT NULL, voucherCodeId INT DEFAULT NULL, INDEX IDX_7F7BC3AAFDD3BBCD (voucherCodeId), INDEX customerId_idx (customerId), UNIQUE INDEX voucherCodeId_customerId (voucherCodeId, customerId), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8MB4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB;
            ALTER TABLE coreshop_cart_price_rule_voucher_code_customer ADD CONSTRAINT FK_7F7BC3AAFDD3BBCD FOREIGN KEY (voucherCodeId) REFERENCES coreshop_cart_price_rule_voucher_code (id);
        ',
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
