<?php

/*
 * This script is part of baldeweg/incwadi-core
 *
 * Copyright 2019 André Baldeweg <kontakt@andrebaldeweg.de>
 * MIT-licensed
 */

namespace Incwadi\Core\Tests\Command;

use Incwadi\Core\Command\ListUserCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;

class ListUserCommandTest extends TestCase
{
    public function testExecute()
    {
        $em = $this->getMockBuilder('\\Doctrine\\ORM\\EntityManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $application = new Application();
        $application->add(new ListUserCommand($em));
        $command = $application->find('user:list');

        $this->assertEquals(
            'user:list',
            $command->getName(),
            'ListUserCommandTest user:list'
        );
    }
}
