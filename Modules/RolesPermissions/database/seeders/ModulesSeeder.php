<?php

namespace Modules\RolesPermissions\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        $modules = [
            'General Settings',
            'Communication',
            'Categories',
            'Testimonials',
            'FAQ',
            'Newsletter',
            'Blogs',
            'Roles & Permissions',
            'Product',
            'Service'
        ];

        foreach ($modules as $module) {
            DB::table('modules')->insert([
                'name' => $module,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
