<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was disstributed with this source code.
 */

namespace Dotfiles\Core\Tests\Command;

use Dotfiles\Core\Application;
use Dotfiles\Core\Command\SelfUpdateCommand;
use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Exceptions\InstallFailedException;
use Dotfiles\Core\Tests\BaseTestCase;
use Dotfiles\Core\Util\Downloader;
use Dotfiles\Core\Util\Filesystem;
use Dotfiles\Core\Util\Toolkit;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SelfUpdateCommandTest extends BaseTestCase
{
    /**
     * @var MockObject
     */
    private $config;

    /**
     * @var MockObject
     */
    private $downloader;

    /**
     * @var string
     */
    private $tempDir;

    /**
     * @var ProgressBar
     */
    private $progressBar;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    public function setUp(): void/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->config       = $this->createMock(Config::class);
        $this->downloader   = $this->createMock(Downloader::class);
        $this->tempDir      = sys_get_temp_dir().'/dotfiles';
        $this->output       = $this->createMock(OutputInterface::class);
        $this->input        = $this->createMock(InputInterface::class);
        $this->progressBar  =  new ProgressBar($this->output);
        static::cleanupTempDir();
    }

    public function testExecute()
    {
        $tempDir    = $this->tempDir;
        $versionFile = $tempDir.'/temp/update/dotfiles.phar.json';
        $pharFile = $tempDir.'/temp/update/test/dotfiles.phar';

        Toolkit::ensureFileDir($versionFile);

        $this->downloader->expects($this->exactly(2))
            ->method('run')
            ->withConsecutive(
                [SelfUpdateCommand::BASE_URL.'/dotfiles.phar.json',$versionFile],
                [SelfUpdateCommand::BASE_URL.'/dotfiles.phar',$pharFile]
            )
            ->will(
                $this->returnCallback([$this,'createFakeVersionFile'])
            )
        ;
        $command    = $this->getSUT();
        $command->execute($this->input,$this->output);
    }

    public function testExecuteOnLatestVersionPhar()
    {
        $version = Application::VERSION;
        $versionFile = <<<EOF
{
    "version": "{$version}",
    "branch": "1.0-dev",
    "date": "2018-04-08 06:50:24",
    "sha256": "51ccbace494495e667c9b77bb628bc0ddae590f268524bf644419745e86a07aa  dotfiles.pha"
}
EOF;

        $this->downloader->expects($this->once())
            ->method('run')
            ->will(
                $this->returnCallback(
                    function($url,$target) use ($versionFile){
                        file_put_contents($target,$versionFile,LOCK_EX);
                    }
                )
            )
        ;
        $this->output->expects($this->exactly(2))
            ->method('writeln')
            ->withConsecutive(
                [$this->stringContains('Start checking new version')],
                [$this->stringContains('You already have latest')]
            )
        ;
        $command = $this->getSUT();
        $command->execute($this->input,$this->output);
    }

    public function testExecuteThrowsOnEmptyVersionFile()
    {
        $this->downloader->expects($this->once())
            ->method('run')
            ->will(
                $this->returnCallback(
                    function($url,$target){
                        touch($target);
                    }
                )
            )
        ;

        $this->expectException(InstallFailedException::class);
        $this->expectExceptionMessage('Can not parse dotfiles.phar.json file');
        $command = $this->getSUT();
        $command->execute($this->input,$this->output);
    }

    public function createFakeVersionFile($url,$target)
    {
        if(false !== strpos($url,'dotfiles.phar.json')){
            $origin = __DIR__.'/fixtures/dotfiles.phar.json';
        }else{
            $origin =__DIR__.'/fixtures/dotfiles.phar';
        }
        $fs = new Filesystem();
        $fs->copy($origin,$target);
        return;
    }

    private function getSUT()
    {
        $tempDir = $this->tempDir;

        $this->config->expects($this->any())
            ->method('get')
            ->willReturnMap([
                ['dotfiles.temp_dir',$tempDir.'/temp'],
                ['dotfiles.cache_dir', $tempDir.'/cache'],
                ['dotfiles.dry_run',false]
            ])
        ;
        $this->downloader->expects($this->any())
            ->method('getProgressBar')
            ->willReturn($this->progressBar)
        ;
        return new SelfUpdateCommand(
            null,
            $this->downloader,
            $this->config
        );
    }
}
