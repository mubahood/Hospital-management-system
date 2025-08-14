<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class RoleManagementController extends Controller
{
    /**
     * Display role assignment interface
     */
    public function index()
    {
        $users = User::with('roles')->paginate(20);
        $roles = DB::table('admin_roles')->get();
        
        return view('admin.role-management.index', compact('users', 'roles'));
    }

    /**
     * Assign role to user
     */
    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:admin_users,id',
            'role_id' => 'required|exists:admin_roles,id'
        ]);

        // Check if assignment already exists
        $exists = DB::table('admin_role_users')
            ->where('user_id', $request->user_id)
            ->where('role_id', $request->role_id)
            ->exists();

        if (!$exists) {
            DB::table('admin_role_users')->insert([
                'user_id' => $request->user_id,
                'role_id' => $request->role_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Role assigned successfully!'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Role already assigned to this user.'
        ]);
    }

    /**
     * Remove role from user
     */
    public function removeRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:admin_users,id',
            'role_id' => 'required|exists:admin_roles,id'
        ]);

        $deleted = DB::table('admin_role_users')
            ->where('user_id', $request->user_id)
            ->where('role_id', $request->role_id)
            ->delete();

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Role removed successfully!'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Role assignment not found.'
        ]);
    }

    /**
     * Get user permissions based on roles
     */
    public function getUserPermissions($userId)
    {
        $permissions = DB::table('admin_role_users')
            ->join('admin_role_permissions', 'admin_role_users.role_id', '=', 'admin_role_permissions.role_id')
            ->join('admin_permissions', 'admin_role_permissions.permission_id', '=', 'admin_permissions.id')
            ->where('admin_role_users.user_id', $userId)
            ->select('admin_permissions.*')
            ->distinct()
            ->get();

        return response()->json([
            'success' => true,
            'permissions' => $permissions
        ]);
    }

    /**
     * Test role switching functionality
     */
    public function testRoleAccess(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:admin_users,id',
            'role_slug' => 'required|exists:admin_roles,slug'
        ]);

        $user = User::find($request->user_id);
        $role = DB::table('admin_roles')->where('slug', $request->role_slug)->first();

        if (!$user || !$role) {
            return response()->json([
                'success' => false,
                'message' => 'User or role not found.'
            ]);
        }

        // Get user's permissions for this role
        $permissions = DB::table('admin_role_permissions')
            ->join('admin_permissions', 'admin_role_permissions.permission_id', '=', 'admin_permissions.id')
            ->where('admin_role_permissions.role_id', $role->id)
            ->select('admin_permissions.name', 'admin_permissions.slug', 'admin_permissions.http_path')
            ->get();

        // Get accessible menus for this role
        $menus = DB::table('admin_role_menu')
            ->join('admin_menu', 'admin_role_menu.menu_id', '=', 'admin_menu.id')
            ->where('admin_role_menu.role_id', $role->id)
            ->select('admin_menu.title', 'admin_menu.uri', 'admin_menu.icon')
            ->get();

        return response()->json([
            'success' => true,
            'role' => $role->name,
            'permissions_count' => $permissions->count(),
            'permissions' => $permissions,
            'accessible_menus' => $menus,
            'menu_count' => $menus->count()
        ]);
    }

    /**
     * Generate role hierarchy documentation
     */
    public function generateRoleDocumentation()
    {
        $roles = DB::table('admin_roles')->get();
        $documentation = [];

        foreach ($roles as $role) {
            $permissions = DB::table('admin_role_permissions')
                ->join('admin_permissions', 'admin_role_permissions.permission_id', '=', 'admin_permissions.id')
                ->where('admin_role_permissions.role_id', $role->id)
                ->select('admin_permissions.name', 'admin_permissions.slug')
                ->get();

            $menus = DB::table('admin_role_menu')
                ->join('admin_menu', 'admin_role_menu.menu_id', '=', 'admin_menu.id')
                ->where('admin_role_menu.role_id', $role->id)
                ->select('admin_menu.title', 'admin_menu.uri')
                ->get();

            $documentation[] = [
                'role' => $role,
                'permissions' => $permissions,
                'menus' => $menus,
                'permission_count' => $permissions->count(),
                'menu_count' => $menus->count()
            ];
        }

        return view('admin.role-management.documentation', compact('documentation'));
    }
}
