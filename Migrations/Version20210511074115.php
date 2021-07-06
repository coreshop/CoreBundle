<?php

namespace CoreShop\Bundle\CoreBundle\Migrations;

use CoreShop\Component\Pimcore\Migration\SharedTranslation;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20210511074115 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        SharedTranslation::add('coreshop.order_payment.total', 'de', 'Zahlung behinhaltet %items% Einträge für Betrag %total%.');
        SharedTranslation::add('coreshop.order_payment.total', 'de_CH', 'Zahlung behinhaltet %items% Einträge für Betrag %total%.');
        SharedTranslation::add('coreshop.order_payment.total', 'en', 'Payment contains %items% item(s) for a total of %total%.');
        SharedTranslation::add('coreshop.order_payment.total', 'it', 'Il pagamento contiene %items% voce/i per un totale di %total%.');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
