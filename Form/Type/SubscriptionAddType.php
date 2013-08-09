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
        ->add('users', 'entity', array(
            'class' => 'Newscoop\Entity\User',
            'property' => 'username',
        ))
        ->add('subscriptions', 'entity', array(
            'class' => 'Newscoop\PaywallBundle\Entity\Subscriptions',
            'property' => 'name',
        ))
        ->add('type', 'choice', array(
            'label'  => 'Type',
            'choices'   => array(
                'P'   => 'Paid',
                'PN'   => 'Paid now',
                'T' => 'Trial',
            )
        ))
        ->add('status', 'choice', array(
            'label'  => 'Active',
            'choices'   => array(
                'Y'   => 'Yes',
                'N'   => 'No',
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