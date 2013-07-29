<?php
/**
 * @package Newscoop\PaywallBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

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
            'label' => 'step1.form.label.name',
            'error_bubbling' => true,
            'invalid_message' => 'step1.form.error.name'
        ))
        ->add('type', 'choice', array(
            'label'  => 'step1.form.label.type',
            'choices'   => array(
                'publication'   => 'step1.form.select.type.publication',
                'issue'   => 'step1.form.select.type.issue',
                'section' => 'step1.form.select.type.section',
                'article'   => 'step1.form.select.type.article',
            )
        ))
        ->add('range', null, array(
            'label' => 'step1.form.label.duration',
            'attr' => array('min'=>'1'),
            'error_bubbling' => true,
            'invalid_message' => 'step1.form.error.duration'
        ))
        ->add('price', null, array(
            'label' => 'step1.form.label.price',
            'error_bubbling' => true,
            'required' => true,
            'invalid_message' => 'step1.form.error.price'
        ))
        ->add('currency', null, array(
            'label' => 'step1.form.label.currency',
            'error_bubbling' => true,
            'required' => true,
            'invalid_message' => 'step1.form.error.price'
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Newscoop\PaywallBundle\Entity\Subscriptions',
            'csrf_protection' => false
        ));
    }

    public function getName()
    {
        return 'subscriptionconf';
    }
}