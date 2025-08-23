<?php

/**
 * This file is part of the Brille24 customer options plugin.
 *
 * (c) Brille24 GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Brille24\SyliusCustomerOptionsPlugin\Form;

use Brille24\SyliusCustomerOptionsPlugin\Enumerations\CustomerOptionTypeEnum;
use Brille24\SyliusCustomerOptionsPlugin\Form\EventSubscriber\GenerateCustomerOptionCodeSubscriber;
use Doctrine\Common\Collections\Collection;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Sylius\Bundle\ResourceBundle\Form\EventSubscriber\AddCodeFormSubscriber;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

final class CustomerOptionType extends AbstractResourceType {

    public function __construct(
        string $dataClass,
        array $validationGroups = [],
        private readonly GenerateCustomerOptionCodeSubscriber $codeSubscriber,
    ) {
        parent::__construct($dataClass, $validationGroups);
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Array keys are the constants and the values are the translations
        $possibleTypes = CustomerOptionTypeEnum::getTranslateArray();
        $typeValue = $options['data']?->getType();

        $builder
            ->add('code', TextType::class, [
                'label' => 'sylius.ui.code',
                'empty_data' => '',
                'required' => false,
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'sylius.ui.type',
                'choices' => array_flip($possibleTypes),
                'data' => $typeValue,
                'required' => true,
            ])
            ->add('required', CheckboxType::class, [
                'label' => 'brille24.ui.required',
            ])
            ->add('translations', ResourceTranslationsType::class, [
                'entry_type' => CustomerOptionTranslationType::class,
                'label' => 'sylius.form.option.name',
            ])
            ->add('values', LiveCollectionType::class, [
                'entry_type' => CustomerOptionValueType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
                'button_add_options' => [
                    'label' => 'sylius.form.option_value.add_value',
                ]
            ])
            ->add('configuration', CustomerOptionConfigurationType::class, [
                'label' => false,
            ])
            ->addEventSubscriber($this->codeSubscriber)
        ;

    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix(): string
    {
        return 'brille24_customer_option';
    }
}
