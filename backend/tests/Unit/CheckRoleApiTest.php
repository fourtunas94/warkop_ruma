<?php

namespace Tests\Unit;

use App\Http\Middleware\CheckRoleApi;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class CheckRoleApiTest extends TestCase
{
    public function test_kasir_can_access_admin_management_routes(): void
    {
        $request = new Request();
        $request->setUserResolver(static fn () => (object) ['role' => 'kasir']);

        $result = (new CheckRoleApi())->handle(
            $request,
            static fn () => 'allowed',
            'admin',
            'kasir',
        );

        $this->assertSame('allowed', $result);
    }

    public function test_admin_can_still_access_admin_management_routes(): void
    {
        $request = new Request();
        $request->setUserResolver(static fn () => (object) ['role' => 'admin']);

        $result = (new CheckRoleApi())->handle(
            $request,
            static fn () => 'allowed',
            'admin',
            'kasir',
        );

        $this->assertSame('allowed', $result);
    }
}
