<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Model\SalesOrder;

class SalesOrderConfirmation
{
    /**
     * @var bool
     */
    protected $termsOfSaleChecked = true;

    /**
     * @var string
     */
    protected $consumerComment;

    /**
     * @param string $consumerComment
     */
    public function setConsumerComment($consumerComment)
    {
        $this->consumerComment = $consumerComment;
    }

    /**
     * @return string
     */
    public function getConsumerComment()
    {
        return $this->consumerComment;
    }

    /**
     * @param boolean $termsOfSaleChecked
     */
    public function setTermsOfSaleChecked($termsOfSaleChecked)
    {
        $this->termsOfSaleChecked = $termsOfSaleChecked;
    }

    /**
     * @return boolean
     */
    public function getTermsOfSaleChecked()
    {
        return $this->termsOfSaleChecked;
    }
}
