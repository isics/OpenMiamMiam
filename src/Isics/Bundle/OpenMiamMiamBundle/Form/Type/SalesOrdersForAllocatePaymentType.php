<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;

class SalesOrdersForAllocatePaymentType extends AbstractType
{
    public function getParent()
    {
        return EntityType::class;
    }
}
