<?php

namespace Newscoop\PaywallBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SubscriptionEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('subscriptionName', 'text', array(
            'required' => true
        ))
        ->add('subscriptionDuration', 'integer', array(
            'required' => false
        ))
        ->add('subscriptionPrice', 'number', array(
            'required' => false
        ))
        ->add('subscriptionCurrency', 'money', array(
            'required' => false
        ));
    }

    public function getName()
    {
        return 'subscriptionEdit';
    }
}