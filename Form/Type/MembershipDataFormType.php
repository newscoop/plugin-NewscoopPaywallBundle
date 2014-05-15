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

class MembershipDataFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array(
                'error_bubbling' => true,
                'required' => true,
                'constraints' => new NotBlank(),
        ))
        ->add('surname', null, array(
            'error_bubbling' => true,
            'required' => true,
            'constraints' => new NotBlank(),
        ))
        ->add('selected', 'hidden', array(
            'error_bubbling' => true,
            'required' => false,
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
    }

    public function getName()
    {
        return 'membershipdataForm';
    }
}
