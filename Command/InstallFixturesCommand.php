<?php
/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) CoreShop GmbH (https://www.coreshop.org)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 */

declare(strict_types=1);

namespace CoreShop\Bundle\CoreBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class InstallFixturesCommand extends AbstractInstallCommand
{
    protected function configure(): void
    {
        $this
            ->setName('coreshop:install:fixtures')
            ->setDescription('Install CoreShop Main Fixtures.')
            ->setHelp(
                <<<EOT
The <info>%command.name%</info> command install CoreShop Main Fixtures.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->runCommands(['coreshop:fixture:data:load'], $output);

        return 0;
    }
}
