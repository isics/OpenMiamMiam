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

use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Manager\PaymentManager;
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
        return [
            new \Twig_SimpleFunction('has_missing_allocations', [$this, 'hasMissingAllocations']),
        ];
    }

    /**
     * Return true if user (or anonymous) has missing allocations
     *
     * @param Association $association
     * @param User        $user
     *
     * @return bool
     */
    public function hasMissingAllocations(Association $association, User $user = null)
    {
        return $this->paymentManager->hasMissingAllocations($association, $user);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'open_miam_miam_payment_extension';
    }
}
