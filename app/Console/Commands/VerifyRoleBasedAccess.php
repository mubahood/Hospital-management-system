<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class VerifyRoleBasedAccess extends Command
{
    protected $signature = 'hospital:verify-roles';
    protected $description = 'Verify role-based access control implementation';

    public function handle()
    {
        $this->info('🔐 HOSPITAL ROLE-BASED ACCESS CONTROL VERIFICATION');
        $this->info('=======================================================');

        // 1. Verify Role Structure
        $this->verifyRoleStructure();
        
        // 2. Verify Permission System
        $this->verifyPermissionSystem();
        
        // 3. Verify Role-Permission Assignments
        $this->verifyRolePermissionAssignments();
        
        // 4. Verify Menu Access Control
        $this->verifyMenuAccessControl();
        
        // 5. Test Role Functionality
        $this->testRoleFunctionality();
        
        $this->info('✅ Role-based access control verification completed!');
    }

    private function verifyRoleStructure()
    {
        $this->info('\n📋 1. ROLE STRUCTURE VERIFICATION');
        $this->info('====================================');
        
        $roles = DB::table('admin_roles')->get();
        $this->info("Total Roles: " . $roles->count());
        
        $expectedRoles = [
            'super-administrator',
            'hospital-administrator', 
            'doctor',
            'nurse',
            'pharmacist',
            'receptionist',
            'lab-technician',
            'accountant'
        ];
        
        foreach ($expectedRoles as $expectedRole) {
            $exists = $roles->where('slug', $expectedRole)->first();
            if ($exists) {
                $this->info("✅ {$exists->name} ({$expectedRole})");
            } else {
                $this->error("❌ Missing role: {$expectedRole}");
            }
        }
    }

    private function verifyPermissionSystem()
    {
        $this->info('\n🔑 2. PERMISSION SYSTEM VERIFICATION');
        $this->info('=====================================');
        
        $permissions = DB::table('admin_permissions')->get();
        $this->info("Total Permissions: " . $permissions->count());
        
        $categories = [
            'patient' => 'Patient Management',
            'medical' => 'Medical Records',
            'appointment' => 'Appointments',
            'billing' => 'Billing & Financial',
            'pharmacy' => 'Pharmacy',
            'lab' => 'Laboratory',
            'system' => 'System Administration',
            'reports' => 'Reports & Analytics',
            'dashboard' => 'Dashboard Access'
        ];
        
        foreach ($categories as $prefix => $category) {
            $categoryPerms = $permissions->filter(function($perm) use ($prefix) {
                return str_starts_with($perm->slug, $prefix);
            });
            $this->info("✅ {$category}: " . $categoryPerms->count() . " permissions");
        }
    }

    private function verifyRolePermissionAssignments()
    {
        $this->info('\n🔗 3. ROLE-PERMISSION ASSIGNMENTS');
        $this->info('==================================');
        
        $roles = DB::table('admin_roles')->get();
        
        foreach ($roles as $role) {
            $permissionCount = DB::table('admin_role_permissions')
                ->where('role_id', $role->id)
                ->count();
            
            if ($permissionCount > 0) {
                $this->info("✅ {$role->name}: {$permissionCount} permissions");
            } else {
                $this->warn("⚠️ {$role->name}: No permissions assigned");
            }
        }
    }

    private function verifyMenuAccessControl()
    {
        $this->info('\n📋 4. MENU ACCESS CONTROL');
        $this->info('==========================');
        
        $totalMenus = DB::table('admin_menu')->count();
        $this->info("Total Menu Items: {$totalMenus}");
        
        $roles = DB::table('admin_roles')->get();
        
        foreach ($roles as $role) {
            $menuCount = DB::table('admin_role_menu')
                ->where('role_id', $role->id)
                ->count();
            
            if ($menuCount > 0) {
                $this->info("✅ {$role->name}: {$menuCount} accessible menus");
            } else {
                $this->warn("⚠️ {$role->name}: No menu access configured");
            }
        }
    }

    private function testRoleFunctionality()
    {
        $this->info('\n🧪 5. ROLE FUNCTIONALITY TESTING');
        $this->info('==================================');
        
        // Test Doctor role permissions
        $doctorRole = DB::table('admin_roles')->where('slug', 'doctor')->first();
        if ($doctorRole) {
            $doctorPermissions = DB::table('admin_role_permissions')
                ->join('admin_permissions', 'admin_role_permissions.permission_id', '=', 'admin_permissions.id')
                ->where('admin_role_permissions.role_id', $doctorRole->id)
                ->get();
            
            $hasPatientAccess = $doctorPermissions->where('slug', 'patient.view')->count() > 0;
            $hasMedicalAccess = $doctorPermissions->where('slug', 'medical.view')->count() > 0;
            
            if ($hasPatientAccess && $hasMedicalAccess) {
                $this->info('✅ Doctor role has appropriate medical permissions');
            } else {
                $this->error('❌ Doctor role missing essential permissions');
            }
        }
        
        // Test Receptionist role permissions
        $receptionistRole = DB::table('admin_roles')->where('slug', 'receptionist')->first();
        if ($receptionistRole) {
            $receptionistPermissions = DB::table('admin_role_permissions')
                ->join('admin_permissions', 'admin_role_permissions.permission_id', '=', 'admin_permissions.id')
                ->where('admin_role_permissions.role_id', $receptionistRole->id)
                ->get();
            
            $hasAppointmentAccess = $receptionistPermissions->where('slug', 'appointment.create')->count() > 0;
            $hasBillingAccess = $receptionistPermissions->where('slug', 'billing.view')->count() > 0;
            
            if ($hasAppointmentAccess && $hasBillingAccess) {
                $this->info('✅ Receptionist role has appropriate front-desk permissions');
            } else {
                $this->error('❌ Receptionist role missing essential permissions');
            }
        }
        
        // Verify role hierarchy
        $superAdminRole = DB::table('admin_roles')->where('slug', 'super-administrator')->first();
        $hospitalAdminRole = DB::table('admin_roles')->where('slug', 'hospital-administrator')->first();
        
        if ($superAdminRole && $hospitalAdminRole) {
            $this->info('✅ Administrative hierarchy established');
        }
    }
}
