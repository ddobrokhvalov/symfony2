<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Security\Handler;

use Sonata\AdminBundle\Admin\AdminInterface;

class NoopSecurityHandler implements SecurityHandlerInterface
{
    /**
     * {@inheritDoc}
     */
    public function isGranted($attributes, $object = null)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function buildSecurityInformation(AdminInterface $admin)
    {
        return array();
    }
}