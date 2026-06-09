<?php

namespace Tests\Feature;

use Tests\TestCase;

class DeployScriptTest extends TestCase
{
    public function test_deploy_script_exists_and_passes_syntax_check(): void
    {
        $script = base_path('scripts/deploy.sh');

        $this->assertFileExists($script);
        $this->assertTrue(is_executable($script));

        exec('bash -n '.escapeshellarg($script).' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode("\n", $output));
    }

    public function test_deploy_script_help_exits_zero(): void
    {
        $script = base_path('scripts/deploy.sh');

        exec('bash '.escapeshellarg($script).' --help 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('--sync', implode("\n", $output));
    }
}
