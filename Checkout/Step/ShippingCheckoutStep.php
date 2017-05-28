<?php
/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2015-2017 Dominik Pfaffenbauer (https://www.pfaffenbauer.at)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
*/

namespace CoreShop\Bundle\CoreBundle\Checkout\Step;

use CoreShop\Component\Currency\Formatter\MoneyFormatterInterface;
use CoreShop\Bundle\ShippingBundle\Calculator\CarrierPriceCalculatorInterface;
use CoreShop\Bundle\ShippingBundle\Checker\CarrierShippingRuleCheckerInterface;
use CoreShop\Bundle\ShippingBundle\Processor\CartCarrierProcessorInterface;
use CoreShop\Component\Core\Model\CarrierInterface;
use CoreShop\Component\Currency\Context\CurrencyContextInterface;
use CoreShop\Component\Order\Checkout\CheckoutException;
use CoreShop\Component\Order\Checkout\CheckoutStepInterface;
use CoreShop\Component\Order\Model\CartInterface;
use CoreShop\Component\Shipping\Model\ShippingRuleGroupInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Valid;

class ShippingCheckoutStep implements CheckoutStepInterface
{
    /**
     * @var CartCarrierProcessorInterface
     */
    private $cartCarrierProcessor;

    /**
     * @var CarrierPriceCalculatorInterface
     */
    private $carrierPriceCalculator;

    /**
     * @var CarrierShippingRuleCheckerInterface
     */
    private $carrierShippingRuleChecker;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var CurrencyContextInterface
     */
    private $currencyContext;

    /**
     * @var MoneyFormatterInterface
     */
    private $moneyFormatter;

    /**
     * @param CartCarrierProcessorInterface       $cartCarrierProcessor
     * @param CarrierPriceCalculatorInterface     $carrierPriceCalculator
     * @param CarrierShippingRuleCheckerInterface $carrierShippingRuleChecker
     * @param FormFactoryInterface                $formFactory
     * @param CurrencyContextInterface            $currencyContext
     * @param MoneyFormatterInterface             $moneyFormatter
     */
    public function __construct(
        CartCarrierProcessorInterface $cartCarrierProcessor,
        CarrierPriceCalculatorInterface $carrierPriceCalculator,
        CarrierShippingRuleCheckerInterface $carrierShippingRuleChecker,
        FormFactoryInterface $formFactory,
        CurrencyContextInterface $currencyContext,
        MoneyFormatterInterface $moneyFormatter
    ) {
        $this->cartCarrierProcessor = $cartCarrierProcessor;
        $this->carrierPriceCalculator = $carrierPriceCalculator;
        $this->carrierShippingRuleChecker = $carrierShippingRuleChecker;
        $this->formFactory = $formFactory;
        $this->currencyContext = $currencyContext;
        $this->moneyFormatter = $moneyFormatter;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'shipping';
    }

    /**
     * {@inheritdoc}
     */
    public function doAutoForward()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(CartInterface $cart)
    {
        return
            $cart->getCarrier() instanceof CarrierInterface &&
            $this->carrierShippingRuleChecker->isShippingRuleValid($cart->getCarrier(), $cart, $cart->getShippingAddress()) instanceof ShippingRuleGroupInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function commitStep(CartInterface $cart, Request $request)
    {
        $form = $this->createForm($this->getCarriers($cart));

        $form->handleRequest($request);
        $formData = $form->getData();

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $cart->setCarrier($formData['carrier']->carrier);
                $cart->save();

                return true;
            } else {
                throw new CheckoutException('Shipping Form is invalid', 'coreshop_checkout_shipping_form_invalid');
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareStep(CartInterface $cart)
    {
        //Get Carriers
        $carriers = $this->getCarriers($cart);

        return [
            'carriers' => $carriers,
            'form' => $this->createForm($carriers)->createView(),
        ];
    }

    /**
     * @param CartInterface $cart
     *
     * @return array
     */
    private function getCarriers(CartInterface $cart)
    {
        $carriers = $this->cartCarrierProcessor->getCarriersForCart($cart, $cart->getShippingAddress());
        $availableCarriers = [];

        foreach ($carriers as $carrier) {
            $carrierPrice = $this->carrierPriceCalculator->getPrice($carrier, $cart, $cart->getShippingAddress());

            $availableCarriers[$carrier->getId()] = new \stdClass();
            $availableCarriers[$carrier->getId()]->carrier = $carrier;
            $availableCarriers[$carrier->getId()]->price = $carrierPrice;
        }

        return $availableCarriers;
    }

    /**
     * @param $carriers
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    private function createForm($carriers)
    {
        $form = $this->formFactory->createNamed('', FormType::class);

        $form->add('carrier', ChoiceType::class, [
            'constraints' => [new Valid()],
            'choices' => $carriers,
            'expanded' => true,
            'choice_label' => function ($carrier) {
                return $carrier->carrier->getLabel().' '.$this->moneyFormatter->format($carrier->price, $this->currencyContext->getCurrency()->getIsoCode());
            },
        ]);

        return $form;
    }
}
