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

class SubscriptionAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('users', 'hidden', array(
            'label' => 'paywall.manage.label.users',
            'required' => true
        ))
        ->add('subscriptions', 'entity', array(
            'label' => 'paywall.manage.label.subscriptions',
            'class' => 'Newscoop\PaywallBundle\Entity\Subscriptions',
            'property' => 'name',
        ))
        ->add('type', 'choice', array(
            'label' => 'paywall.manage.label.paymenttype',
            'choices'   => array(
                'P'   => 'paywall.manage.label.paid',
                'PN'   => 'paywall.manage.label.paidnow',
                'T' => 'paywall.manage.label.trial',
            )
        ))
        ->add('status', 'choice', array(
            'label' => 'paywall.manage.label.active',
            'choices'   => array(
                'Y'   => 'paywall.manage.label.yes',
                'N'   => 'paywall.manage.label.no',
            )
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
        return 'subscriptionaddForm';
    }
}
