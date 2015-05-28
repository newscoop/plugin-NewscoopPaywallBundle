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

class DurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('value', 'integer', array(
            'constraints' => array(
                new Assert\NotBlank(),
                new Assert\Range(array(
                    'min' => 1,
                    'max' => 9999,
                )),
            ),
            'required' => true,
        ))
        ->add('attribute', 'choice', array(
            'label' => 'paywall.step1.form.label.duration',
            'error_bubbling' => true,
            'invalid_message' => 'paywall.step1.form.error.duration',
            'choices' => array(
                'month' => 'paywall.label.months',
            ),
            'required' => true,
        ));
    }

    public function getName()
    {
        return 'durationForm';
    }
}
