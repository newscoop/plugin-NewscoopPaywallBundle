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

class SubscriptionEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('topay', 'money', array(
            'label'  => 'paywall.form.label.topay',
            'error_bubbling' => true,
            'invalid_message' => 'To pay field value is in wrong format.',
            'required' => true
        ))
        ->add('currency', null, array(
            'label'  => 'paywall.form.label.currency',
            'error_bubbling' => true,
            'invalid_message' => 'Currency is invalid format.',
            'required' => true
        ))
        ->add('type', 'choice', array(
            'label'  => 'paywall.form.label.type',
            'choices'   => array(
                'P' => 'Paid', 
                'PN' => 'Paid Now', 
                'T' => 'Trial'
            ),
            'required' => true
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false
        ));
    }

    public function getName()
    {
        return 'subscriptioneditForm';
    }
}