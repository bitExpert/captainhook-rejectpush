<?php

/*
 * This file is part of the Captain Hook Reject Push plugin package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bitExpert\CaptainHook\RejectPush;

use CaptainHook\App\Config;
use CaptainHook\App\Config\Action;
use CaptainHook\App\Config\Options;
use CaptainHook\App\Console\IO;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use RuntimeException;
use SebastianFeldmann\Git\Log\Commit;
use SebastianFeldmann\Git\Repository;

class RejectPushActionUnitTest extends TestCase
{
    /**
     * @var MockObject&Config
     */
    private $config;
    /**
     * @var MockObject&IO
     */
    private $io;
    /**
     * @var MockObject&Repository
     */
    private $repository;
    /**
     * @var MockObject&Action
     */
    private $action;
    /**
     * @var MockObject&RejectPushAction
     */
    private $hook;

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->config = $this->createMock(Config::class);
        $this->io = $this->createMock(IO::class);
        $this->repository = $this->createMock(Repository::class);
        $this->action = $this->createMock(Action::class);
        $this->hook = $this->createPartialMock(RejectPushAction::class, ['getAllCommits']);
    }

    /**
     * @test
     */
    public function missingTargetIOParameterStopsExecution(): void
    {
        $this->io->expects(self::once())
            ->method('getArguments')
            ->willReturn([]);

        $this->action->expects(self::never())
            ->method('getOptions');

        $this->hook->execute($this->config, $this->io, $this->repository, $this->action);
    }

    /**
     * @test
     */
    public function missingConfigurationForTargetStopsExecution(): void
    {
        $this->io->expects(self::once())
            ->method('getArguments')
            ->willReturn(['target' => 'my-origin']);

        $this->action->expects(self::once())
            ->method('getOptions')
            ->willReturn(new Options([]));

        $this->hook->expects(self::never())
            ->method('getAllCommits');

        $this->hook->execute($this->config, $this->io, $this->repository, $this->action);
    }

    /**
     * @test
     */
    public function emptyConfigurationArrayForTargetStopsExecution(): void
    {
        $this->io->expects(self::once())
            ->method('getArguments')
            ->willReturn(['target' => 'my-origin']);

        $this->action->expects(self::once())
            ->method('getOptions')
            ->willReturn(new Options(['my-origin' => []]));

        $this->hook->expects(self::never())
            ->method('getAllCommits');

        $this->hook->execute($this->config, $this->io, $this->repository, $this->action);
    }

    /**
     * @test
     */
    public function allowPushWhenConfiguredCommitIdsCannotBeFoundInCommitHistory(): void
    {
        $this->io->expects(self::once())
            ->method('getArguments')
            ->willReturn(['target' => 'my-origin']);

        $this->action->expects(self::once())
            ->method('getOptions')
            ->willReturn(new Options(['my-origin' => ['abcdefg', 'bcdefgh']]));

        $this->hook->expects(self::once())
            ->method('getAllCommits')
            ->willReturn($this->createCommitHistory());

        $this->hook->execute($this->config, $this->io, $this->repository, $this->action);
    }

    /**
     * @test
     */
    public function allowPushWhenConfiguredCommitIdCannotBeFoundInCommitHistory(): void
    {
        $this->io->expects(self::once())
            ->method('getArguments')
            ->willReturn(['target' => 'my-origin']);

        $this->action->expects(self::once())
            ->method('getOptions')
            ->willReturn(new Options(['my-origin' => 'abcdefg']));

        $this->hook->expects(self::once())
            ->method('getAllCommits')
            ->willReturn($this->createCommitHistory());

        $this->hook->execute($this->config, $this->io, $this->repository, $this->action);
    }

    /**
     * @test
     */
    public function denyPushWhenConfiguredCommitIdsAreFoundInCommitHistory(): void
    {
        $this->expectException(RuntimeException::class);

        $this->io->expects(self::once())
            ->method('getArguments')
            ->willReturn(['target' => 'my-origin']);

        $this->action->expects(self::once())
            ->method('getOptions')
            ->willReturn(new Options(['my-origin' => ['2345678', '3456789']]));

        $this->hook->expects(self::once())
            ->method('getAllCommits')
            ->willReturn($this->createCommitHistory());

        $this->hook->execute($this->config, $this->io, $this->repository, $this->action);
    }

    /**
     * @test
     */
    public function denyPushWhenConfiguredCommitIdIsFoundInCommitHistory(): void
    {
        $this->expectException(RuntimeException::class);

        $this->io->expects(self::once())
            ->method('getArguments')
            ->willReturn(['target' => 'my-origin']);

        $this->action->expects(self::once())
            ->method('getOptions')
            ->willReturn(new Options(['my-origin' => '1234567']));

        $this->hook->expects(self::once())
            ->method('getAllCommits')
            ->willReturn($this->createCommitHistory());

        $this->hook->execute($this->config, $this->io, $this->repository, $this->action);
    }

    /**
     * Helper method to return a default Git Commit History collection
     *
     * @throws \Exception
     * @return iterable<Commit>
     */
    private function createCommitHistory(): iterable
    {
        return [
            new Commit('1234567', [], '', '', new DateTimeImmutable(), 'Author'),
            new Commit('2345678', [], '', '', new DateTimeImmutable(), 'Author'),
            new Commit('3456789', [], '', '', new DateTimeImmutable(), 'Author'),
            new Commit('4567890', [], '', '', new DateTimeImmutable(), 'Author'),

        ];
    }
}
