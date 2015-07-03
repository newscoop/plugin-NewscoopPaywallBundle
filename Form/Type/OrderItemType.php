<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

class OrderItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('subscription', 'entity', array(
            'label' => 'paywall.manage.label.subscriptions',
            'class' => 'Newscoop\PaywallBundle\Entity\Subscriptions',
            'property' => 'name',
            'empty_value' => 'paywall.manage.label.choose',
            'required' => true,
            'constraints' => array(
                new Assert\NotBlank(),
            ),
        ))
        ->add('type', 'choice', array(
            'label' => 'paywall.manage.label.paymenttype',
            'choices' => array(
                'P' => 'paywall.manage.label.paid',
                'PN' => 'paywall.manage.label.paidnow',
                'T' => 'paywall.manage.label.trial',
            ),
            'required' => true,
            'constraints' => array(
                new Assert\NotBlank(),
            ),
        ))
        ->add('active', 'choice', array(
            'label' => 'paywall.manage.label.active',
            'choices' => array(
                'Y' => 'paywall.manage.label.yes',
                'N' => 'paywall.manage.label.no',
            ),
            'required' => true,
            'constraints' => array(
                new Assert\NotBlank(),
            ),
        ));

        $formModifier = function ($form, $subscription = null) {
            $periods = null === $subscription ? array() : $subscription->getRanges();
            $form->add('duration', 'entity', array(
                'label' => 'paywall.label.period',
                'class' => 'Newscoop\PaywallBundle\Entity\Duration',
                'choices' => $periods,
                'empty_value' => 'paywall.manage.label.choose',
                'required' => true,
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ));
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $item = $event->getData();
                $subscription = null;
                if ($item) {
                    $subscription = $item->getSubscription();
                }
                $formModifier($event->getForm(), $subscription);
            }
        );

        $builder->get('subscription')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $subscription = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $subscription);
            }
        );
    }

    public function getName()
    {
        return 'orderItemForm';
    }
}
