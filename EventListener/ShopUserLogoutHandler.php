<?php
/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) CoreShop GmbH (https://www.coreshop.org)
 * @license    https://www.coreshop.org/license     GPLv3 and CCL
 */

declare(strict_types=1);

namespace CoreShop\Bundle\CoreBundle\EventListener;

use CoreShop\Component\Core\Model\StoreInterface;
use CoreShop\Component\Store\Context\StoreContextInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

/** @psalm-suppress DeprecatedInterface */
final class ShopUserLogoutHandler implements LogoutSuccessHandlerInterface
{
    public function __construct(
        private RouterInterface $router,
        private string $routeName,
        private StoreContextInterface $storeContext
    )
    {
    }

    public function onLogoutSuccess(Request $request): Response
    {
        $store = $this->storeContext->getStore();

        if ($store instanceof StoreInterface) {

            $request->getSession()->remove('coreshop.cart.' . $store->getId());
        }

        return new RedirectResponse($this->router->generate($this->routeName, ['_locale' => $request->getLocale()]));
    }
}
