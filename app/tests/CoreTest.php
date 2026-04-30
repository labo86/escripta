<?php
declare(strict_types=1);

namespace labo86\escripta\tests;

use labo86\escripta\Core;
use PHPUnit\Framework\TestCase;

class CoreTest extends TestCase
{
    public function testIsHelpRequestedWithLongOption(): void
    {
        $this->assertTrue(Core::isHelpRequested(['escripta.phar', '--help']));
    }

    public function testIsHelpRequestedWithShortOption(): void
    {
        $this->assertTrue(Core::isHelpRequested(['escripta.phar', '-h']));
    }

    public function testGetHelpTextListsMainOptions(): void
    {
        $help = Core::getHelpText();

        $this->assertStringContainsString('--version', $help);
        $this->assertStringContainsString('--self-update', $help);
        $this->assertStringContainsString('--install-agent-guide', $help);
        $this->assertStringContainsString('--help', $help);
    }

    public function testProcessFolderByCommandLineShowsHelpByDefault(): void
    {
        global $argv;
        $argv = ['escripta.phar'];

        ob_start();
        Core::processFolderByCommandLine();
        $output = ob_get_clean();

        $this->assertStringContainsString('Escripta CLI', (string) $output);
        $this->assertStringContainsString('Uso:', (string) $output);
    }
}
