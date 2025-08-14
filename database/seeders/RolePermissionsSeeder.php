<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionsSeeder extends Seeder
{
    public function run()
    {
        // Clear existing role-menu relationships
        DB::table('admin_role_menu')->truncate();
        
        // Create comprehensive permissions for hospital operations
        $permissions = [
            // Patient Management
            ['name' => 'Patient Management', 'slug' => 'patient.management', 'http_method' => '', 'http_path' => '/auth/users*'],
            ['name' => 'View Patients', 'slug' => 'patient.view', 'http_method' => 'GET', 'http_path' => '/auth/users'],
            ['name' => 'Create Patients', 'slug' => 'patient.create', 'http_method' => 'GET,POST', 'http_path' => '/auth/users/create'],
            ['name' => 'Edit Patients', 'slug' => 'patient.edit', 'http_method' => 'GET,PUT', 'http_path' => '/auth/users/*/edit'],
            ['name' => 'Delete Patients', 'slug' => 'patient.delete', 'http_method' => 'DELETE', 'http_path' => '/auth/users/*'],
            
            // Medical Records
            ['name' => 'Medical Records Management', 'slug' => 'medical.records', 'http_method' => '', 'http_path' => '/medical-records*'],
            ['name' => 'View Medical Records', 'slug' => 'medical.view', 'http_method' => 'GET', 'http_path' => '/medical-records'],
            ['name' => 'Create Medical Records', 'slug' => 'medical.create', 'http_method' => 'GET,POST', 'http_path' => '/medical-records/create'],
            ['name' => 'Edit Medical Records', 'slug' => 'medical.edit', 'http_method' => 'GET,PUT', 'http_path' => '/medical-records/*/edit'],
            
            // Appointments
            ['name' => 'Appointment Management', 'slug' => 'appointment.management', 'http_method' => '', 'http_path' => '/appointments*'],
            ['name' => 'View Appointments', 'slug' => 'appointment.view', 'http_method' => 'GET', 'http_path' => '/appointments'],
            ['name' => 'Schedule Appointments', 'slug' => 'appointment.create', 'http_method' => 'GET,POST', 'http_path' => '/appointments/create'],
            ['name' => 'Modify Appointments', 'slug' => 'appointment.edit', 'http_method' => 'GET,PUT', 'http_path' => '/appointments/*/edit'],
            ['name' => 'Cancel Appointments', 'slug' => 'appointment.delete', 'http_method' => 'DELETE', 'http_path' => '/appointments/*'],
            
            // Billing & Financial
            ['name' => 'Billing Management', 'slug' => 'billing.management', 'http_method' => '', 'http_path' => '/billing*'],
            ['name' => 'View Billing', 'slug' => 'billing.view', 'http_method' => 'GET', 'http_path' => '/billing'],
            ['name' => 'Create Bills', 'slug' => 'billing.create', 'http_method' => 'GET,POST', 'http_path' => '/billing/create'],
            ['name' => 'Edit Bills', 'slug' => 'billing.edit', 'http_method' => 'GET,PUT', 'http_path' => '/billing/*/edit'],
            ['name' => 'Financial Reports Access', 'slug' => 'financial.reports', 'http_method' => 'GET', 'http_path' => '/financial-reports*'],
            
            // Pharmacy
            ['name' => 'Pharmacy Management', 'slug' => 'pharmacy.management', 'http_method' => '', 'http_path' => '/pharmacy*'],
            ['name' => 'View Medications', 'slug' => 'pharmacy.view', 'http_method' => 'GET', 'http_path' => '/pharmacy'],
            ['name' => 'Manage Inventory', 'slug' => 'pharmacy.inventory', 'http_method' => 'GET,POST,PUT', 'http_path' => '/pharmacy/inventory*'],
            ['name' => 'Dispense Medications', 'slug' => 'pharmacy.dispense', 'http_method' => 'POST,PUT', 'http_path' => '/pharmacy/dispense*'],
            
            // Laboratory
            ['name' => 'Laboratory Management', 'slug' => 'lab.management', 'http_method' => '', 'http_path' => '/laboratory*'],
            ['name' => 'View Lab Tests', 'slug' => 'lab.view', 'http_method' => 'GET', 'http_path' => '/laboratory'],
            ['name' => 'Create Lab Tests', 'slug' => 'lab.create', 'http_method' => 'GET,POST', 'http_path' => '/laboratory/create'],
            ['name' => 'Lab Results', 'slug' => 'lab.results', 'http_method' => 'GET,POST,PUT', 'http_path' => '/laboratory/results*'],
            
            // System Administration
            ['name' => 'System Administration', 'slug' => 'system.admin', 'http_method' => '', 'http_path' => '/auth/roles*,/auth/permissions*,/auth/menu*'],
            ['name' => 'User Management', 'slug' => 'user.management', 'http_method' => '', 'http_path' => '/auth/users*'],
            ['name' => 'Role Management', 'slug' => 'role.management', 'http_method' => '', 'http_path' => '/auth/roles*'],
            ['name' => 'Permission Management', 'slug' => 'permission.management', 'http_method' => '', 'http_path' => '/auth/permissions*'],
            
            // Reports & Analytics
            ['name' => 'Reports Management', 'slug' => 'reports.management', 'http_method' => '', 'http_path' => '/reports*'],
            ['name' => 'Patient Reports', 'slug' => 'reports.patients', 'http_method' => 'GET', 'http_path' => '/reports/patients*'],
            ['name' => 'Medical Reports', 'slug' => 'reports.medical', 'http_method' => 'GET', 'http_path' => '/reports/medical*'],
            ['name' => 'Hospital Financial Reports', 'slug' => 'reports.financial', 'http_method' => 'GET', 'http_path' => '/reports/financial*'],
            
            // Dashboard Access
            ['name' => 'Dashboard Access', 'slug' => 'dashboard.access', 'http_method' => 'GET', 'http_path' => '/'],
        ];

        // Insert permissions (using updateOrInsert to handle duplicates)
        foreach ($permissions as $permission) {
            DB::table('admin_permissions')->updateOrInsert(
                ['slug' => $permission['slug']], // Match on slug only
                array_merge($permission, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }

        // Get all roles and permissions
        $roles = DB::table('admin_roles')->get();
        $allPermissions = DB::table('admin_permissions')->get();
        $allMenus = DB::table('admin_menu')->get();

        // Define role-based permission mappings
        $rolePermissions = [
            'super-administrator' => $allPermissions->pluck('id')->toArray(), // All permissions
            
            'hospital-administrator' => [
                'patient.management', 'patient.view', 'patient.create', 'patient.edit',
                'appointment.management', 'appointment.view', 'appointment.create', 'appointment.edit', 'appointment.delete',
                'billing.management', 'billing.view', 'billing.create', 'billing.edit', 'financial.reports',
                'reports.management', 'reports.patients', 'reports.medical', 'reports.financial',
                'user.management', 'dashboard.access'
            ],
            
            'doctor' => [
                'patient.management', 'patient.view', 'patient.create', 'patient.edit',
                'medical.records', 'medical.view', 'medical.create', 'medical.edit',
                'appointment.management', 'appointment.view', 'appointment.edit',
                'lab.management', 'lab.view', 'lab.create', 'lab.results',
                'reports.patients', 'reports.medical', 'dashboard.access'
            ],
            
            'nurse' => [
                'patient.view', 'patient.edit',
                'medical.view', 'medical.edit',
                'appointment.view', 'appointment.edit',
                'lab.view', 'lab.results',
                'dashboard.access'
            ],
            
            'pharmacist' => [
                'patient.view',
                'pharmacy.management', 'pharmacy.view', 'pharmacy.inventory', 'pharmacy.dispense',
                'medical.view',
                'dashboard.access'
            ],
            
            'receptionist' => [
                'patient.management', 'patient.view', 'patient.create', 'patient.edit',
                'appointment.management', 'appointment.view', 'appointment.create', 'appointment.edit', 'appointment.delete',
                'billing.view', 'billing.create',
                'dashboard.access'
            ],
            
            'lab-technician' => [
                'patient.view',
                'lab.management', 'lab.view', 'lab.create', 'lab.results',
                'medical.view',
                'dashboard.access'
            ],
            
            'accountant' => [
                'patient.view',
                'billing.management', 'billing.view', 'billing.create', 'billing.edit',
                'financial.reports', 'reports.financial',
                'dashboard.access'
            ]
        ];

        // Define role-based menu access
        $roleMenus = [
            'super-administrator' => $allMenus->pluck('id')->toArray(), // All menus
            
            'hospital-administrator' => [
                'auth.users', 'auth.roles', 'auth.permissions', 'appointments', 'billing', 
                'reports', 'medical-records', 'pharmacy', 'laboratory'
            ],
            
            'doctor' => [
                'auth.users', 'appointments', 'medical-records', 'laboratory', 'reports'
            ],
            
            'nurse' => [
                'auth.users', 'appointments', 'medical-records', 'laboratory'
            ],
            
            'pharmacist' => [
                'auth.users', 'pharmacy', 'medical-records'
            ],
            
            'receptionist' => [
                'auth.users', 'appointments', 'billing'
            ],
            
            'lab-technician' => [
                'auth.users', 'laboratory', 'medical-records'
            ],
            
            'accountant' => [
                'auth.users', 'billing', 'reports'
            ]
        ];

        // Clear existing role permissions
        DB::table('admin_role_permissions')->truncate();

        // Assign permissions to roles
        foreach ($roles as $role) {
            if (isset($rolePermissions[$role->slug])) {
                $permissionSlugs = $rolePermissions[$role->slug];
                
                foreach ($permissionSlugs as $permissionSlug) {
                    if (is_string($permissionSlug)) {
                        $permission = $allPermissions->where('slug', $permissionSlug)->first();
                        if ($permission) {
                            DB::table('admin_role_permissions')->updateOrInsert([
                                'role_id' => $role->id,
                                'permission_id' => $permission->id
                            ]);
                        }
                    } else {
                        // For super-administrator (direct permission IDs)
                        DB::table('admin_role_permissions')->updateOrInsert([
                            'role_id' => $role->id,
                            'permission_id' => $permissionSlug
                        ]);
                    }
                }
            }
        }

        // Assign menus to roles
        foreach ($roles as $role) {
            if (isset($roleMenus[$role->slug])) {
                $menuSlugs = $roleMenus[$role->slug];
                
                foreach ($menuSlugs as $menuSlug) {
                    if (is_string($menuSlug)) {
                        $menu = $allMenus->where('uri', $menuSlug)->first();
                        if ($menu) {
                            DB::table('admin_role_menu')->updateOrInsert([
                                'role_id' => $role->id,
                                'menu_id' => $menu->id
                            ]);
                        }
                    } else {
                        // For super-administrator (direct menu IDs)
                        DB::table('admin_role_menu')->updateOrInsert([
                            'role_id' => $role->id,
                            'menu_id' => $menuSlug
                        ]);
                    }
                }
            }
        }

        $this->command->info('✅ Role-based permissions and menu access configured successfully!');
        $this->command->info('📊 Configured ' . count($permissions) . ' permissions across 8 medical roles');
        $this->command->info('🏥 Hospital role hierarchy established with appropriate access levels');
    }
}
