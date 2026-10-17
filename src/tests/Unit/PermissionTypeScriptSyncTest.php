<?php

namespace Tests\Unit;

use App\Enums\Permission;
use PHPUnit\Framework\TestCase;

class PermissionTypeScriptSyncTest extends TestCase
{
    public function test_the_frontend_permission_list_matches_the_backend_enum(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2).'/resources/js/types/permissions.ts');

        $this->assertIsString($source);
        $this->assertSame(1, preg_match('/PERMISSIONS\s*=\s*\[(.*?)\]\s*as const/s', $source, $block));

        preg_match_all("/'([^']+)'/", $block[1], $matches);

        $this->assertEqualsCanonicalizing(
            array_map(fn (Permission $permission): string => $permission->value, Permission::cases()),
            $matches[1],
            'resources/js/types/permissions.ts is out of sync with app/Enums/Permission.php.',
        );
    }
}
