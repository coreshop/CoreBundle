<?php

namespace CoreShop\Bundle\CoreBundle\Migrations;

use CoreShop\Component\Core\Model\CountryInterface;
use CoreShop\Component\Pimcore\ClassUpdate;
use CoreShop\Component\Resource\Repository\RepositoryInterface;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Db;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Version20180323160716 extends AbstractPimcoreMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Schema $schema
     * @throws \CoreShop\Component\Pimcore\ClassDefinitionNotFoundException
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema)
    {
        $this->container->get('coreshop.resource.installer.shared_translations')->installResources(new NullOutput(), 'coreshop');

        $fieldDefinition = [
            'fieldtype'       => 'input',
            'width'           => null,
            'queryColumnType' => 'varchar',
            'columnType'      => 'varchar',
            'columnLength'    => 190,
            'phpdocType'      => 'string',
            'regex'           => '',
            'unique'          => false,
            'name'            => 'salutation',
            'title'           => 'Salutation',
            'tooltip'         => '',
            'mandatory'       => false,
            'noteditable'     => false,
            'index'           => false,
            'locked'          => null,
            'style'           => '',
            'permissions'     => null,
            'datatype'        => 'data',
            'relationType'    => false,
            'invisible'       => false,
            'visibleGridView' => false,
            'visibleSearch'   => false,
        ];

        $addressClass = $this->container->getParameter('coreshop.model.address.pimcore_class_name');
        $classUpdate = new ClassUpdate($addressClass);

        if (!$classUpdate->hasField('salutation')) {
            $classUpdate->insertFieldBefore('firstname', $fieldDefinition);
            $classUpdate->save();
        }

        $customerClass = $this->container->getParameter('coreshop.model.customer.pimcore_class_name');
        $classUpdate = new ClassUpdate($customerClass);

        if (!$classUpdate->hasField('salutation')) {
            $classUpdate->insertFieldBefore('firstname', $fieldDefinition);
            $classUpdate->save();
        }

        //add country salutation prefix
        if ($schema->hasTable('coreshop_country')) {
            if (!$schema->getTable('coreshop_country')->hasColumn('salutations')) {
                Db::get()->executeQuery('ALTER TABLE coreshop_country ADD salutations LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\';');
            }
        }

        //add default salutation prefixes
        /** @var RepositoryInterface $countryRepository */
        $countryRepository = $this->container->get('coreshop.repository.country');
        $manager = $this->container->get('doctrine.orm.entity_manager');

        $defaultSalutations = ['mrs', 'mr'];

        /** @var CountryInterface $country */
        foreach ($countryRepository->findAll() as $country) {

            //set salutation
            $country->setSalutations($defaultSalutations);

            //update address format
            $addressFormat = $country->getAddressFormat();
            if (strpos($addressFormat, '%Text(firstname);') !== false && strpos($addressFormat, '%Text(salutation);') === false) {
                $addressFormat = str_replace('%Text(firstname);', '%Text(salutation); %Text(firstname);', $addressFormat);
                $country->setAddressFormat($addressFormat);
            }

            $manager->persist($country);

        }

        $manager->flush();

        $this->container->get('pimcore.cache.core.handler')->clearTag('doctrine_pimcore_cache');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {

    }
}