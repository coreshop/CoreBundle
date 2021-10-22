<?php
/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) CoreShop GmbH (https://www.coreshop.org)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 */

declare(strict_types=1);

namespace CoreShop\Bundle\CoreBundle\Form\Type;

use CoreShop\Bundle\AddressBundle\Form\Type\AddressType;
use CoreShop\Bundle\AddressBundle\Form\Type\SalutationChoiceType;
use CoreShop\Bundle\CoreBundle\Form\EventSubscriber\CustomerRegistrationFormSubscriber;
use CoreShop\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use CoreShop\Component\Core\Model\CustomerInterface;
use CoreShop\Component\Core\Model\UserInterface;
use CoreShop\Component\Customer\Repository\CustomerRepositoryInterface;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Valid;

class CustomerRegistrationType extends AbstractResourceType
{
    public function __construct(
        string $dataClass,
        array $validationGroups,
        private string $loginIdentifier,
        private DataMapperInterface $dataMapper,
        private CustomerRepositoryInterface $customerRepository
    ) {
        parent::__construct($dataClass, $validationGroups);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new CustomerRegistrationFormSubscriber($this->customerRepository));

        $builder->setDataMapper($this->dataMapper);
        $builder
            ->add('user', UserRegistrationType::class, [
                'label' => false,
                'constraints' => [new Valid(['groups' => $this->validationGroups])],
                'allow_username' => 'username' === $this->loginIdentifier,
            ])
            ->add('salutation', SalutationChoiceType::class, [
                'label' => 'coreshop.form.customer.salutation',
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'coreshop.form.customer.gender',
                'choices' => [
                    'coreshop.form.customer.gender.male' => 'male',
                    'coreshop.form.customer.gender.female' => 'female',
                ],
            ])
            ->add('firstname', TextType::class, [
                'label' => 'coreshop.form.customer.firstname',
            ])
            ->add('lastname', TextType::class, [
                'label' => 'coreshop.form.customer.lastname',
            ])
            ->add('email', RepeatedType::class, [
                'type' => EmailType::class,
                'invalid_message' => 'coreshop.form.customer.email.must_match',
                'first_options' => ['label' => 'coreshop.form.customer.email'],
                'second_options' => ['label' => 'coreshop.form.customer.email_repeat'],
            ])
            ->add('newsletterActive', CheckboxType::class, [
                'label' => 'coreshop.form.customer.newsletter.subscribe',
                'required' => false,
            ])
            ->add('address', AddressType::class, [
                'label' => 'coreshop.form.customer_registration.address',
                'label_attr' => [
                    'class' => 'cs-address',
                ],
                'constraints' => [
                    new Valid(['groups' => $this->validationGroups]),
                ],
                'mapped' => false,
            ])
            ->add('termsAccepted', CheckboxType::class, [
                'label' => 'coreshop.form.customer.terms',
                'mapped' => false,
                'validation_groups' => $this->validationGroups,
                'constraints' => new IsTrue(['groups' => $this->validationGroups]),
            ])
            ->add('submit', SubmitType::class);

        if ('username' !== $this->loginIdentifier) {
            $builder->addEventListener(FormEvents::SUBMIT, static function (FormEvent $event) {
                $data = $event->getData();

                if (!$data instanceof CustomerInterface) {
                    return;
                }

                $user = $data->getObjectVar('user');

                if (!$user instanceof UserInterface) {
                    return;
                }

                $user->setLoginIdentifier($data->getEmail());
            });
        }
    }

    public function getBlockPrefix(): string
    {
        return 'coreshop_customer_registration';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'csrf_protection' => true,
            'allow_extra_fields' => false,
        ]);
    }
}
