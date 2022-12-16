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

use CoreShop\Component\Core\Model\CustomerInterface;
use CoreShop\Component\Core\Model\UserInterface;
use CoreShop\Component\Pimcore\BatchProcessing\DataObjectBatchListing;
use CoreShop\Component\Pimcore\DataObject\ClassUpdate;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Pimcore\Model\DataObject\Service;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Version20200206155318 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function up(Schema $schema): void
    {
        $this->write('Create User Class');

        $jsonFile = $this->container->get('kernel')->locateResource('@CoreShopCoreBundle/Resources/install/pimcore/classes/CoreShopUserBundle/CoreShopUser.json');
        $this->container->get('coreshop.class_installer')->createClass($jsonFile, 'CoreShopUser');

        $userField = [
            'fieldtype' => 'coreShopRelation',
            'stack' => 'coreshop.user',
            'relationType' => true,
            'objectsAllowed' => true,
            'assetsAllowed' => false,
            'documentsAllowed' => false,
            'width' => null,
            'assetUploadPath' => null,
            'assetTypes' => [],
            'documentTypes' => [],
            'classes' => [],
            'pathFormatterClass' => '',
            'name' => 'user',
            'title' => 'coreshop.customer.user',
            'tooltip' => '',
            'mandatory' => false,
            'noteditable' => false,
            'index' => false,
            'locked' => null,
            'style' => '',
            'permissions' => null,
            'datatype' => 'data',
            'invisible' => false,
            'visibleGridView' => false,
            'visibleSearch' => false,
        ];

        $this->write('Update Customer Class');
        $classUpdater = new ClassUpdate($this->container->getParameter('coreshop.model.customer.pimcore_class_name'));

        if (!$classUpdater->hasField('user')) {
            $classUpdater->insertFieldAfter('localeCode', $userField);
        }

        if ($classUpdater->hasField('password')) {
            $classUpdater->replaceFieldProperties('password', [
                'noteditable' => true,
            ]);
        }

        if ($classUpdater->hasField('passwordResetHash')) {
            $classUpdater->replaceFieldProperties('passwordResetHash', [
                'noteditable' => true,
            ]);
        }

        if ($classUpdater->hasField('isGuest')) {
            $classUpdater->replaceFieldProperties('isGuest', [
                'noteditable' => true,
            ]);
        }

        $classUpdater->save();

        $this->write('Create Users and Update Customers');
        $customerRepository = $this->container->get('coreshop.repository.customer');

        $customerList = $customerRepository->getList();
        $batchList = new DataObjectBatchListing($customerList, 100);

        $doneUsers = [];

        /**
         * @var CustomerInterface $customer
         */
        foreach ($batchList as $customer) {
            if ($customer->getIsGuest()) {
                continue;
            }

            $loginIdentifier = $this->container->getParameter('coreshop.customer.security.login_identifier') === 'username' && method_exists($customer, 'getUsername') ?
                $customer->getUsername() :
                $customer->getEmail();

            if (in_array($loginIdentifier, $doneUsers, true)) {
                continue;
            }

            /**
             * @var UserInterface $user
             */
            $user = $this->container->get('coreshop.factory.user')->createNew();
            $user->setLoginIdentifier($loginIdentifier);
            $user->setPassword($customer->getPassword());
            $user->setParent(Service::createFolderByPath(sprintf(
                '/%s/%s',
                $customer->getFullPath(),
                $this->container->getParameter('coreshop.folder.user'),
            )));
            $user->setCustomer($customer);
            $user->setKey(Service::getValidKey($customer->getEmail(), 'object'));
            $user->save();

            $doneUsers[] = $loginIdentifier;

            $customer->setUser($user);
            $customer->save();
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
