<?php
/**
 * @package Newscoop\PaywallBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class MembershipDataFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];

        $builder
            ->add('name', null, array(
                'error_bubbling' => true,
                'required' => true,
                'constraints' => array(new NotBlank(array('message' => $translator->trans('paywall.membership.error.firstname'))),
                new Length(array(
                'max'        => 100,
                'maxMessage' => $translator->trans('paywall.membership.error.firstnamelength', array('{{ limit }}')),
            )))
        ))
        ->add('surname', null, array(
            'error_bubbling' => true,
            'required' => true,
            'constraints' => array(new NotBlank(array('message' => $translator->trans('paywall.membership.error.lastname'))),
                new Length(array(
                'max'        => 100,
                'maxMessage' => $translator->trans('paywall.membership.error.lastnamelength', array('{{ limit }}')),
            )))
        ))
        ->add('street', 'text', array(
            'error_bubbling' => true,
            'required' => true,
            'constraints' => array(new NotBlank(array('message' => $translator->trans('paywall.membership.error.street'))),
                new Length(array(
                'max'        => 255,
                'maxMessage' => $translator->trans('paywall.membership.error.streetlength', array('{{ limit }}')),
            )))
        ))
        ->add('city', 'text', array(
            'error_bubbling' => true,
            'required' => true,
            'constraints' => array(new NotBlank(array('message' => $translator->trans('paywall.membership.error.city'))),
                new Length(array(
                'max'        => 100,
                'maxMessage' => $translator->trans('paywall.membership.error.citylength', array('{{ limit }}')),
            )))
        ))
        ->add('postal', 'text', array(
            'error_bubbling' => true,
            'required' => true,
            'constraints' => array(new NotBlank(array('message' => $translator->trans('paywall.membership.error.postal'))),
                new Length(array(
                'max'        => 10,
                'maxMessage' => $translator->trans('paywall.membership.error.postallength', array('{{ limit }}')),
            )))
        ))
        ->add('state', null, array(
            'error_bubbling' => true,
            'required' => true,
            'constraints' => array(new NotBlank(array('message' => $translator->trans('paywall.membership.error.state'))),
                new Length(array(
                'max'        => 32,
                'maxMessage' => $translator->trans('paywall.membership.error.statelength', array('{{ limit }}')),
            ))),
        ))
        ->add('fancybox', 'hidden', array(
            'required' => false,
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false
        ));

        $resolver->setRequired(array(
            'translator',
        ));
    }

    public function getName()
    {
        return 'membershipdataForm';
    }
}
