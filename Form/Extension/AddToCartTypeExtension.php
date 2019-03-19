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

use CoreShop\Bundle\OrderBundle\DTO\AddToCartInterface;
use CoreShop\Bundle\OrderBundle\Form\Type\AddToCartType;
use CoreShop\Bundle\ProductBundle\Form\Type\Unit\ProductUnitDefinitionsChoiceType;
use CoreShop\Component\Core\Model\CartItemInterface;
use CoreShop\Component\Core\Model\ProductInterface;
use CoreShop\Component\Product\Model\ProductUnitDefinitionInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final class AddToCartTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

            $data = $event->getData();

            if (!$data instanceof AddToCartInterface) {
                return;
            }

            /** @var ProductInterface $product */
            $product = $data->getCartItem()->getProduct();
            if (!$product instanceof ProductInterface) {
                return;
            }

            if (!$product->hasUnitDefinitions()) {
                return;
            }

            $event->getForm()->add('unitDefinition', ProductUnitDefinitionsChoiceType::class, [
                'product'  => $product,
                'mapped'   => false,
                'required' => false,
                'label'    => null,
            ]);
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {

            if (!$event->getForm()->has('unitDefinition')) {
                return;
            }

            if (!$event->getForm()->get('unitDefinition')->getData() instanceof ProductUnitDefinitionInterface) {
                return;
            }

            $unitDefinition = $event->getForm()->get('unitDefinition')->getData();

            /** @var CartItemInterface $cartItem */
            $cartItem = $event->getData()->getCartItem();

            $cartItem->setUnitDefinition($unitDefinition);

            /** @var AddToCartInterface $data */
            $data = $event->getData();

            $data->setCartItem($cartItem);

            $event->setData($data);

        });
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return AddToCartType::class;
    }
}
