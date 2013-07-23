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
            'required' => true
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

    public function getName()
    {
        return 'specificationForm';
    }
}