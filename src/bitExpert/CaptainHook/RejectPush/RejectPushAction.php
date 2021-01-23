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
use CaptainHook\App\Console\IO;
use CaptainHook\App\Hook\Action;
use SebastianFeldmann\Cli\Command\Runner\Simple;
use SebastianFeldmann\Cli\Processor\ProcOpen as Processor;
use SebastianFeldmann\Git\Command\Log\Commits;
use SebastianFeldmann\Git\Command\Log\Commits\Jsonized;
use SebastianFeldmann\Git\Repository;

class RejectPushAction implements Action
{
    /**
     * @var Simple
     */
    private $runner;

    /**
     * Creates new {@link \bitExpert\CaptainHook\RejectPush\RejectPushAction}.
     */
    public function __construct()
    {
        $this->runner = new Simple(new Processor());
    }

    /**
     * Executes the action.
     *
     * @param  \CaptainHook\App\Config $config
     * @param  \CaptainHook\App\Console\IO $io
     * @param  \SebastianFeldmann\Git\Repository $repository
     * @param  \CaptainHook\App\Config\Action $action
     * @return void
     * @throws \Exception
     */
    public function execute(Config $config, IO $io, Repository $repository, Config\Action $action): void
    {
        $arguments = $io->getArguments();
        $target = $arguments['target'] ?? '';
        if (empty($target)) {
            return;
        }

        $options = $action->getOptions()->getAll();
        $notAllowedCommits = $options[$target] ?? [];
        if (!is_array($notAllowedCommits)) {
            $notAllowedCommits = [$notAllowedCommits];
        }

        if (count($notAllowedCommits) === 0) {
            // no need to iterate over the whole Git history when no configuration for the $target
            // was found
            return;
        }

        foreach ($this->getAllCommits($repository) as $commit) {
            if (in_array($commit->getHash(), $notAllowedCommits, true)) {
                throw new \RuntimeException(
                    sprintf(
                        'Commit "%s" found! Not able to push to "%s"!',
                        $commit->getHash(),
                        $target
                    )
                );
            }
        }
    }

    /**
     * Returns a list of all commits of the current branch.
     *
     * @param Repository $repository
     * @return iterable<\SebastianFeldmann\Git\Log\Commit>
     */
    protected function getAllCommits(Repository $repository): iterable
    {
        $cmd = (new Commits($repository->getRoot()))
            ->prettyFormat(Jsonized::FORMAT);

        $result = $this->runner->run($cmd, new Jsonized());
        return $result->getFormattedOutput();
    }
}
