<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Discount form type.
 */
class DiscountType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'label' => 'paywall.manage.label.name',
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array(
                        'max' => 255,
                        'min' => 1,
                    )),
                ),
            ))
            ->add('description', 'text', array(
                'label' => 'paywall.manage.label.description',
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array(
                        'max' => 255,
                        'min' => 1,
                    )),
                ),
            ))
            ->add('type', 'choice', array(
                'label' => 'paywall.label.discounttype',
                'choices' => array(
                    'percentage_discount' => 'Percentage discount',
                ),
            ))
            ->add('countBased', 'checkbox', array(
                'label' => 'paywall.label.countbased',
                'required' => false,
            ))
            ->add('value', 'percent', array(
                'label' => 'Value',
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Type(array('type' => 'numeric')),
                ),
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'paywall_discount';
    }
}
