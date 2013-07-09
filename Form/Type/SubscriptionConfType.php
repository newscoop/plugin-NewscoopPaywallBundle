<?php

namespace Newscoop\PaywallBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SubscriptionConfType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array(
                    'label' => 'Name of subscription: ',
                    'error_bubbling' => true,
                    'invalid_message' => 'Subscription name can not be empty.'
            ))
            ->add('type', 'entity', array(
                'class' => 'Newscoop\Entity\Section',
                'label'  => 'Type of subscription: ',
                'property' => 'name',
            ))
            ->add('range', null, array(
                    'label' => 'Duration of subscription in days: ',
                    'error_bubbling' => true,
                    'invalid_message' => 'Type duration of subscription in days.'
            ))
            ->add('price', null, array(
                    'label' => 'Value of the subscription: ',
                    'error_bubbling' => true,
                    'required' => true,
                    'invalid_message' => 'Type the value of the subscription.'
            ))
            ->add('currency', null, array(
                    'label' => 'Currency: ',
                    'error_bubbling' => true,
                    'required' => true,
                    'invalid_message' => 'Type currency.'
            )) 
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Newscoop\PaywallBundle\Entity\Subscriptions'
        ));
    }

    public function getName()
    {
        return 'subscriptionconf';
    }
}