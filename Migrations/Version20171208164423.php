<?php

namespace CoreShop\Bundle\CoreBundle\Migrations;

use CoreShop\Component\Pimcore\ClassUpdate;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Version20171208164423 extends AbstractPimcoreMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Schema $schema
     * @throws \CoreShop\Component\Pimcore\ClassDefinitionFieldNotFoundException
     */
    public function up(Schema $schema)
    {
         $commentField = [
            'fieldtype'       => 'textarea',
            'width'           => 350,
            'height'          => '',
            'queryColumnType' => 'longtext',
            'columnType'      => 'longtext',
            'phpdocType'      => 'string',
            'name'            => 'comment',
            'title'           => 'Comment',
            'tooltip'         => '',
            'mandatory'       => FALSE,
            'noteditable'     => FALSE,
            'index'           => FALSE,
            'locked'          => NULL,
            'style'           => '',
            'permissions'     => NULL,
            'datatype'        => 'data',
            'relationType'    => FALSE,
            'invisible'       => FALSE,
            'visibleGridView' => FALSE,
            'visibleSearch'   => FALSE
        ];

        $cartClass = $this->container->getParameter('coreshop.model.cart.pimcore_class_name');
        $classUpdater = new ClassUpdate($cartClass);
        if (!$classUpdater->hasField('comment')) {
            $classUpdater->insertFieldAfter('currency', $commentField);
            $classUpdater->save();
        }

        $orderClass = $this->container->getParameter('coreshop.model.order.pimcore_class_name');
        $classUpdater = new ClassUpdate($orderClass);
        if (!$classUpdater->hasField('comment')) {
            $classUpdater->insertFieldAfter('paymentProvider', $commentField);
            $classUpdater->save();
        }

        $quoteClass = $this->container->getParameter('coreshop.model.quote.pimcore_class_name');
        $classUpdater = new ClassUpdate($quoteClass);
        if (!$classUpdater->hasField('comment')) {
            $classUpdater->insertFieldAfter('store', $commentField);
            $classUpdater->save();
        }

        //update translations
        $this->container->get('coreshop.resource.installer.shared_translations')->installResources(new NullOutput(), 'coreshop');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
