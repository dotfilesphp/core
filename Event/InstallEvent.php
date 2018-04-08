<?php

declare(strict_types=1);

/*
 * This file is part of the dotfiles project.
 *
 *     (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Core\Event;

class InstallEvent extends AbstractEvent
{
    public const NAME = 'dotfiles.install';

    /**
     * @var bool
     */
    private $dryRun;

    /**
     * @var bool
     */
    private $overwriteNewFiles;

    /**
     * @var array
     */
    private $patches = array();

    public function addPatch($target, $patch): void
    {
        if (!isset($this->patches[$target])) {
            $this->patches[$target] = array();
        }
        $this->patches[$target][] = $patch;
    }

    public function getName()
    {
        return static::NAME;
    }

    /**
     * @return array
     */
    public function getPatches(): array
    {
        return $this->patches;
    }

    /**
     * @return bool
     */
    public function isDryRun(): bool
    {
        return $this->dryRun;
    }

    /**
     * @return bool
     */
    public function isOverwriteNewFiles(): bool
    {
        return $this->overwriteNewFiles;
    }

    /**
     * @param bool $dryRun
     *
     * @return InstallEvent
     */
    public function setDryRun(bool $dryRun): self
    {
        $this->dryRun = $dryRun;

        return $this;
    }

    /**
     * @param bool $overwriteNewFiles
     *
     * @return InstallEvent
     */
    public function setOverwriteNewFiles(bool $overwriteNewFiles): self
    {
        $this->overwriteNewFiles = $overwriteNewFiles;

        return $this;
    }
}
