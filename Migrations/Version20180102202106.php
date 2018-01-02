<?php

namespace CoreShop\Bundle\CoreBundle\Migrations;

use CoreShop\Component\Core\Model\OrderItemInterface;
use CoreShop\Component\Pimcore\ClassUpdate;
use CoreShop\Component\Product\Model\ProductInterface;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;
use Pimcore\Model\DataObject\CoreShopProduct;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180102202106 extends AbstractPimcoreMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Schema $schema
     * @throws \CoreShop\Component\Pimcore\ClassDefinitionFieldNotFoundException
     * @throws \Exception
     */
    public function up(Schema $schema)
    {
        $mainObjectIdField = [
            'fieldtype'        => 'numeric',
            'width'            => '',
            'defaultValue'     => null,
            'queryColumnType'  => 'double',
            'columnType'       => 'double',
            'phpdocType'       => 'float',
            'integer'          => false,
            'unsigned'         => false,
            'minValue'         => null,
            'maxValue'         => null,
            'unique'           => false,
            'decimalSize'      => null,
            'decimalPrecision' => null,
            'name'             => 'mainObjectId',
            'title'            => 'Main Object Id',
            'tooltip'          => '',
            'mandatory'        => false,
            'noteditable'      => true,
            'index'            => false,
            'locked'           => null,
            'style'            => '',
            'permissions'      => null,
            'datatype'         => 'data',
            'relationType'     => false,
            'invisible'        => false,
            'visibleGridView'  => false,
            'visibleSearch'    => false,
        ];

        $customer = $this->container->getParameter('coreshop.model.order_item.pimcore_class_name');
        $classUpdater = new ClassUpdate($customer);
        if (!$classUpdater->hasField('mainObjectId')) {
            $classUpdater->insertFieldAfter('product', $mainObjectIdField);
            $classUpdater->save();
        }

        \Pimcore::collectGarbage();

        //update existing orders
        $orderItems = $this->container->get('coreshop.repository.order_item')->getList();

        /** @var OrderItemInterface $object */
        foreach ($orderItems as $object) {

            $productObject = $object->getProduct();
            if (!$productObject instanceof ProductInterface) {
                continue;
            }

            $mainObjectId = null;
            if ($productObject->getType() === 'variant') {
                $mainProduct = $productObject;
                while ($mainProduct->getType() === 'variant') {
                    $mainProduct = $mainProduct->getParent();
                }

                $object->setMainObjectId($mainProduct->getId());
            }

            $object->save();
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
