<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Menu;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;
use Symfony\Component\HttpFoundation\Request;

class Voter implements VoterInterface
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * Sets request
     *
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @see VoterInterface
     */
    public function matchItem(ItemInterface $item)
    {
        if ($item->getUri() === $this->request->getRequestUri()) {
            // URL's completely match
            return true;
        } else if ($item->getUri() !== $this->request->getBaseUrl().'/' && $item->getUri() === substr($this->request->getRequestUri(), 0, strlen($item->getUri()))) {
            // URL isn't just "/" and the first part of the URL match
            return true;
        }

        return null;
    }
}