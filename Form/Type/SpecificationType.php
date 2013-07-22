<?php

namespace Newscoop\PaywallBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SpecificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('publicationId', 'integer', array(
            'required' => false
        ))
        ->add('issueId', 'integer', array(
            'required' => false
        ))
        ->add('sectionId', 'integer', array(
            'required' => false
        ))
        ->add('articleNumber', 'integer', array(
            'required' => false
        ));
    }

    /*public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Newscoop\PaywallBundle\Entity\Subscription_specification'
        ));
    }*/

    public function getName()
    {
        return 'specificationForm';
    }
}