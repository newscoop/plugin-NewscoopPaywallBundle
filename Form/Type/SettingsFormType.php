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
        ));
    }

    public function getName()
    {
        return 'settingsForm';
    }
}
