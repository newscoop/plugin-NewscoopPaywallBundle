<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class SettingsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('notificationEmail', 'email', array(
            'required' => true,
            'error_bubbling' => true,
        ))
        ->add('adapter', 'hidden', array(
            'required' => true,
            'error_bubbling' => true,
        ))
        ->add('enableNotify', 'checkbox', array(
            'error_bubbling' => true,
            'required' => false,
        ))
        ->add('notificationFromEmail', 'email', array(
            'required' => true,
            'error_bubbling' => true,
        ));
        /*->add('currency', 'paywall_currency_choice', array(
            'label' => 'paywall.label.currency',
            'constraints' => array(
                new Assert\NotBlank(),
                new Assert\Currency(),
            ),
        ));*/
    }

    public function getName()
    {
        return 'settingsForm';
    }
}
