<?php
/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2015-2020 Dominik Pfaffenbauer (https://www.pfaffenbauer.at)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 */

declare(strict_types=1);

namespace CoreShop\Bundle\CoreBundle\Security;

use CoreShop\Component\Core\Model\CustomerInterface;
use CoreShop\Component\Customer\Repository\CustomerRepositoryInterface;
use CoreShop\Component\User\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ObjectUserProvider implements UserProviderInterface
{
    protected UserRepositoryInterface $userRepository;
    protected string $className;
    protected string $loginIdentifier;

    public function __construct(
        UserRepositoryInterface $userRepository,
        string $className,
        string $loginIdentifier
    )
    {
        $this->userRepository = $userRepository;
        $this->className = $className;
        $this->loginIdentifier = $loginIdentifier;
    }

    public function loadUserByUsername(string $userNameOrEmailAddress)
    {
        $customer = $this->userRepository->findUniqueByLoginIdentifier($this->loginIdentifier, $userNameOrEmailAddress, false);

        if ($customer instanceof CustomerInterface) {
            return $customer;
        }

        throw new UsernameNotFoundException(sprintf('User with email address or username "%s" was not found', $userNameOrEmailAddress));
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof \CoreShop\Component\Core\Model\UserInterface) {
            throw new UnsupportedUserException();
        }

        return $this->userRepository->find($user->getId());
    }

    public function supportsClass($class)
    {
        return $class === $this->className;
    }
}
