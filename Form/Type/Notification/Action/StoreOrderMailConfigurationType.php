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

namespace CoreShop\Bundle\CoreBundle\Form\Type\Notification\Action;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

class StoreOrderMailConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mails', CollectionType::class, [
                'allow_add' => true,
                'allow_delete' => true,
                'entry_type' => CollectionType::class,
                'entry_options' => [
                    'allow_add' => true,
                    'allow_delete' => true,
                    'entry_type' => NumberType::class,
                ],
            ])
            ->add('sendInvoices', CheckboxType::class)
            ->add('sendShipments', CheckboxType::class)
            ->add('doNotSendToDesignatedRecipient', CheckboxType::class)
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'coreshop_notification_rule_action_store_mail';
    }
}
