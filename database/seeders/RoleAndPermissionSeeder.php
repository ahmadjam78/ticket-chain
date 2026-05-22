<?php

namespace Database\Seeders;

use App\Shared\Enums\Permission;
use App\Shared\Enums\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (Permission::values() as $permissionName) {
            SpatiePermission::firstOrCreate(['name' => $permissionName]);
        }

        foreach (Role::values() as $roleName) {
            SpatieRole::firstOrCreate(['name' => $roleName]);
        }

        $this->assignPermissionsToRoles();
    }

    private function assignPermissionsToRoles(): void
    {
        $customer = SpatieRole::where('name', Role::CUSTOMER->value)->first();
        $customer->syncPermissions([
            Permission::CREATE_TICKETS->value,
            Permission::VIEW_TICKETS->value,
        ]);

        $admin1 = SpatieRole::where('name', Role::ADMIN_LEVEL_1->value)->first();
        $admin1->syncPermissions([
            Permission::VIEW_TICKETS->value,
            Permission::APPROVE_LEVEL_1->value,
            Permission::REJECT_TICKET->value,
        ]);

        $admin2 = SpatieRole::where('name', Role::ADMIN_LEVEL_2->value)->first();
        $admin2->syncPermissions([
            Permission::VIEW_TICKETS->value,
            Permission::APPROVE_LEVEL_2->value,
            Permission::REJECT_TICKET->value,
        ]);
    }
}
