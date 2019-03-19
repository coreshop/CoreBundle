<?php
/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2015-2019 Dominik Pfaffenbauer (https://www.pfaffenbauer.at)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 */

namespace CoreShop\Bundle\CoreBundle\Form\Extension;

use CoreShop\Bundle\OrderBundle\DTO\AddToCart;
use CoreShop\Bundle\OrderBundle\Form\Type\CartItemType;
use CoreShop\Bundle\ProductBundle\Form\Type\Unit\ProductUnitDefinitionsChoiceType;
use CoreShop\Component\Core\Model\CartItemInterface;
use CoreShop\Component\Core\Model\ProductInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final class CartItemTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

            $data = $event->getData();

            if (!$event->getForm()->getParent()->getData() instanceof AddToCart) {
                return;
            }

            if (!$data instanceof CartItemInterface) {
                return;
            }

            /** @var ProductInterface $product */
            $product = $data->getProduct();
            if (!$product instanceof ProductInterface) {
                return;
            }

            if (!$product->hasUnitDefinitions()) {
                return;
            }

            $event->getForm()->add('unitDefinition', ProductUnitDefinitionsChoiceType::class, [
                'product'  => $product,
                'required' => false,
                'label'    => null,
            ]);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return CartItemType::class;
    }
}
