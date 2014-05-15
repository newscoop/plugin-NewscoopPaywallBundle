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

class MembershipFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $subs = $options['subs'];

        $builder
            ->add('membershipType', 'choice', array(
                'choices'   => $subs,
                'error_bubbling' => true,
                'multiple' => false,
                'expanded' => true,
                'required' => true,
                'constraints' => new NotBlank(),
        ))
        ->add('customer_id', 'text', array(
            'required'  => false,
            'error_bubbling' => true,
            //'constraints' => new NotBlank(),
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false
        ));

        $resolver->setRequired(array(
            'subs',
        ));
    }

    public function getName()
    {
        return 'membershipForm';
    }
}
