<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view content',
            'create content',
            'edit content',
            'delete content',
            'publish content',
            'view translations',
            'create translations',
            'edit translations',
            'delete translations',
            'view media',
            'upload media',
            'delete media',
            'view users',
            'create users',
            'edit users',
            'delete users',
            'view settings',
            'edit settings',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Super Admin - has all permissions
        $superAdmin = Role::create(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin - content, translations, and media
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'view content', 'create content', 'edit content', 'delete content', 'publish content',
            'view translations', 'create translations', 'edit translations', 'delete translations',
            'view media', 'upload media', 'delete media',
        ]);

        // Editor - content and translations (no delete)
        $editor = Role::create(['name' => 'editor']);
        $editor->givePermissionTo([
            'view content', 'create content', 'edit content', 'publish content',
            'view translations', 'create translations', 'edit translations',
            'view media', 'upload media',
        ]);

        // Author - own content only (will need additional logic)
        $author = Role::create(['name' => 'author']);
        $author->givePermissionTo([
            'view content', 'create content', 'edit content',
            'view media', 'upload media',
        ]);

        // Translator - translations only
        $translator = Role::create(['name' => 'translator']);
        $translator->givePermissionTo([
            'view content',
            'view translations', 'create translations', 'edit translations',
        ]);
    }
}
