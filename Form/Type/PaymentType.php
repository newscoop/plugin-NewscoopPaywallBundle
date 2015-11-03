<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Newscoop\PaywallBundle\Entity\PaymentInterface;

/**
 * Payment form type.
 */
class PaymentType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', 'money', array(
                'label' => 'paywall.label.total',
                'precision' => 2,
                'currency' => false,
            ))
            ->add('state', 'choice', array(
                'label' => 'paywall.step2.label.active',
                'choices' => array(
                    PaymentInterface::STATE_FAILED => 'paywall.form.payment.state.failed',
                    PaymentInterface::STATE_COMPLETED => 'paywall.form.payment.state.completed',
                    PaymentInterface::STATE_NEW => 'paywall.form.payment.state.new',
                    PaymentInterface::STATE_CANCELLED => 'paywall.form.payment.state.cancelled',
                    PaymentInterface::STATE_UNKNOWN => 'paywall.form.payment.state.unknown',
                ),
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'paywall_payment';
    }
}
