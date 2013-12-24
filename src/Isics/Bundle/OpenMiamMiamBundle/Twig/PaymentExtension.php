<?php
/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Twig;

use Isics\Bundle\OpenMiamMiamBundle\Manager\PaymentManager;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;

class PaymentExtension extends \Twig_Extension
{
    /**
     * @var PaymentManager $paymentManager
     */
    private $paymentManager;



    /**
     * Constructor
     *
     * @param PaymentManager $paymentManager
     */
    public function __construct(PaymentManager $paymentManager)
    {
        $this->paymentManager = $paymentManager;
    }

    /**
     * Returns available functions
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'has_payment_with_rest_for_association' => new \Twig_Function_Method($this, 'hasPaymentWithRestForAssociation'),
        );
    }

    /**
     * Returns true if user has at least one payment with rest
     *
     * @param Association $association
     * @param User        $user
     *
     * @return bool
     */
    public function hasPaymentWithRestForAssociation(Association $association, User $user = null)
    {
        return $this->paymentManager->hasPaymentWithRestForAssociation($association, $user);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'open_miam_miam_payment_extension';
    }
}
