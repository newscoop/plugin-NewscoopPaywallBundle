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
            'label'  => 'paywall.manage.label.topay',
            'error_bubbling' => true,
            'invalid_message' => 'paywall.manage.error.topay',
            'required' => true,
            'currency' => false,
        ))
        ->add('currency', null, array(
            'label'  => 'paywall.step2.label.currency',
            'error_bubbling' => true,
            'invalid_message' => 'paywall.manage.error.currency',
            'required' => true,
        ))
         ->add('type', 'choice', array(
            'label' => 'paywall.manage.label.paymenttype',
            'choices'   => array(
                'P'   => 'paywall.manage.label.paid',
                'PN'   => 'paywall.manage.label.paidnow',
                'T' => 'paywall.manage.label.trial',
            ),
            'required' => true,
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'subscriptioneditForm';
    }
}
