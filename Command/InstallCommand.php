<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Core\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Dotfiles\Core\Command\CommandInterface;
use Dotfiles\Core\Event\InstallEvent;

class InstallCommand extends Command implements CommandInterface
{
    public function configure()
    {
        $this->setName('install');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(getenv('HOME'));

        $output->writeln('Begin installing <comment>dotfiles</comment>');
        $emitter = $this->getApplication()->getEmitter();
        $event = new InstallEvent();
        $emitter->emit($event);
    }
}
