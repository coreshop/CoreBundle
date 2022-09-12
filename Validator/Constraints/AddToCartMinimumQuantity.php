<?php

declare(strict_types=1);

/*
 * CoreShop
 *
 * This source file is available under two different licenses:
 *  - GNU General Public License version 3 (GPLv3)
 *  - CoreShop Commercial License (CCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) CoreShop GmbH (https://www.coreshop.org)
 * @license    https://www.coreshop.org/license     GPLv3 and CCL
 *
 */

namespace CoreShop\Bundle\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

final class AddToCartMinimumQuantity extends Constraint
{
    public string $messageBelowMinimum;

    public function validatedBy(): string
    {
        return 'coreshop_add_to_cart_minimum_quantity';
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
