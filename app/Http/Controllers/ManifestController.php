<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponser;
use Encore\Admin\Auth\Database\Menu;
use Encore\Admin\Auth\Database\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ManifestController extends Controller
{
    use ApiResponser;

    /**
     * Generate complete application manifest for frontend
     * This endpoint provides all necessary data for the React frontend
     * including navigation, permissions, dropdown options, and configurations
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Cache the manifest for 5 minutes to improve performance
            $cacheKey = 'app_manifest_' . (auth()->id() ?? 'guest');
            
            $manifest = Cache::remember($cacheKey, 300, function () {
                return $this->generateManifest();
            });

            return $this->success($manifest, 'Application manifest retrieved successfully');
            
        } catch (\Exception $e) {
            return $this->error('Failed to generate application manifest: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Generate the complete application manifest
     *
     * @return array
     */
    private function generateManifest()
    {
        $user = auth()->user();
        
        return [
            'app' => $this->getAppConfiguration(),
            'navigation' => $this->getNavigationStructure($user),
            'permissions' => $this->getUserPermissions($user),
            'options' => $this->getDropdownOptions(),
            'ui' => $this->getUIConstants(),
            'meta' => $this->getMetaInformation(),
            'version' => $this->getVersionInformation(),
            'generated_at' => now()->toISOString(),
            'user_context' => $user ? [
                'id' => $user->id,
                'role' => $user->role ?? 'user',
                'permissions' => $this->extractUserPermissions($user)
            ] : null
        ];
    }

    /**
     * Get application configuration
     *
     * @return array
     */
    private function getAppConfiguration()
    {
        return [
            'name' => config('app.name', 'MediCare Hospital Management'),
            'url' => config('app.url'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'currency' => 'UGX',
            'company' => [
                'name' => 'MediCare Hospital',
                'tagline' => 'Professional Healthcare Management',
                'address' => 'Kampala, Uganda',
                'phone' => '+256 700 000 000',
                'email' => 'info@medicare.com',
                'website' => 'https://medicare.com'
            ],
            'features' => [
                'appointments' => true,
                'billing' => true,
                'inventory' => true,
                'reports' => true,
                'notifications' => true,
                'mobile_app' => false
            ]
        ];
    }

    /**
     * Get complete navigation structure for frontend
     *
     * @param $user
     * @return array
     */
    private function getNavigationStructure($user)
    {
        return [
            'main_menu' => $this->getMainMenuItems($user),
            'admin_menu' => $this->getAdminMenuItems($user),
            'public_menu' => $this->getPublicMenuItems(),
            'user_menu' => $this->getUserMenuItems($user),
            'footer_menu' => $this->getFooterMenuItems()
        ];
    }

    /**
     * Get main admin menu items from Laravel Admin
     *
     * @param $user
     * @return array
     */
    private function getMainMenuItems($user)
    {
        try {
            // Get menu items from Laravel Admin menu table
            $menuItems = Menu::query()
                ->orderBy('order')
                ->get()
                ->toArray();

            // Build hierarchical menu structure
            return $this->buildMenuTree($menuItems, 0, $user);
            
        } catch (\Exception $e) {
            // Return fallback menu if database query fails
            return $this->getFallbackAdminMenu();
        }
    }

    /**
     * Build hierarchical menu tree from flat menu items
     *
     * @param array $items
     * @param int $parentId
     * @param $user
     * @return array
     */
    private function buildMenuTree($items, $parentId = 0, $user = null)
    {
        $branch = [];

        foreach ($items as $item) {
            if ($item['parent_id'] == $parentId) {
                // Check if user has permission for this menu item
                if (!$this->userHasMenuPermission($user, $item)) {
                    continue;
                }

                $menuItem = [
                    'id' => $item['id'],
                    'parent_id' => $item['parent_id'],
                    'title' => $item['title'],
                    'icon' => $this->normalizeIcon($item['icon']),
                    'uri' => $item['uri'],
                    'order' => $item['order'],
                    'permission' => $item['permission'],
                    'children' => $this->buildMenuTree($items, $item['id'], $user),
                    'has_children' => false,
                    'is_active' => false,
                    'react_component' => $this->getReactComponent($item['uri']),
                    'react_path' => $this->convertUriToReactPath($item['uri'])
                ];

                // Mark if item has children
                $menuItem['has_children'] = count($menuItem['children']) > 0;

                $branch[] = $menuItem;
            }
        }

        return $branch;
    }

    /**
     * Get admin-specific menu items
     *
     * @param $user
     * @return array
     */
    private function getAdminMenuItems($user)
    {
        return [
            [
                'id' => 'dashboard',
                'title' => 'Dashboard',
                'icon' => 'BarChart3',
                'uri' => 'dashboard',
                'react_path' => '/admin/dashboard',
                'react_component' => 'Dashboard',
                'order' => 1,
                'permission' => null
            ],
            [
                'id' => 'patients',
                'title' => 'Patients',
                'icon' => 'Users',
                'uri' => 'patients',
                'react_path' => '/admin/patients',
                'react_component' => 'Patients',
                'order' => 2,
                'permission' => 'patients.view'
            ],
            [
                'id' => 'consultations',
                'title' => 'Consultations',
                'icon' => 'Calendar',
                'uri' => 'consultations',
                'react_path' => '/admin/consultations',
                'react_component' => 'Consultations',
                'order' => 3,
                'permission' => 'consultations.view'
            ],
            [
                'id' => 'medical-services',
                'title' => 'Medical Services',
                'icon' => 'Activity',
                'uri' => 'medical-services',
                'react_path' => '/admin/medical-services',
                'react_component' => 'MedicalServices',
                'order' => 4,
                'permission' => 'services.view'
            ],
            [
                'id' => 'departments',
                'title' => 'Departments',
                'icon' => 'Building2',
                'uri' => 'departments',
                'react_path' => '/admin/departments',
                'react_component' => 'Departments',
                'order' => 5,
                'permission' => 'departments.view'
            ],
            [
                'id' => 'staff',
                'title' => 'Staff',
                'icon' => 'UserCheck',
                'uri' => 'staff',
                'react_path' => '/admin/staff',
                'react_component' => 'Staff',
                'order' => 6,
                'permission' => 'staff.view'
            ],
            [
                'id' => 'billing',
                'title' => 'Billing',
                'icon' => 'CreditCard',
                'uri' => 'billing',
                'react_path' => '/admin/billing',
                'react_component' => 'Billing',
                'order' => 7,
                'permission' => 'billing.view'
            ],
            [
                'id' => 'reports',
                'title' => 'Reports',
                'icon' => 'FileText',
                'uri' => 'reports',
                'react_path' => '/admin/reports',
                'react_component' => 'Reports',
                'order' => 8,
                'permission' => 'reports.view'
            ],
            [
                'id' => 'settings',
                'title' => 'Settings',
                'icon' => 'Settings',
                'uri' => 'settings',
                'react_path' => '/admin/settings',
                'react_component' => 'Settings',
                'order' => 9,
                'permission' => 'settings.view'
            ]
        ];
    }

    /**
     * Get public menu items
     *
     * @return array
     */
    private function getPublicMenuItems()
    {
        return [
            [
                'id' => 'home',
                'title' => 'Home',
                'path' => '/',
                'order' => 1
            ],
            [
                'id' => 'about',
                'title' => 'About',
                'path' => '/about',
                'order' => 2
            ],
            [
                'id' => 'services',
                'title' => 'Services',
                'path' => '/services',
                'order' => 3
            ],
            [
                'id' => 'doctors',
                'title' => 'Doctors',
                'path' => '/doctors',
                'order' => 4
            ],
            [
                'id' => 'contact',
                'title' => 'Contact',
                'path' => '/contact',
                'order' => 5
            ]
        ];
    }

    /**
     * Get user-specific menu items
     *
     * @param $user
     * @return array
     */
    private function getUserMenuItems($user)
    {
        if (!$user) {
            return [
                [
                    'id' => 'login',
                    'title' => 'Login',
                    'path' => '/auth/login',
                    'icon' => 'LogIn'
                ],
                [
                    'id' => 'register',
                    'title' => 'Register',
                    'path' => '/auth/register',
                    'icon' => 'UserPlus'
                ]
            ];
        }

        return [
            [
                'id' => 'profile',
                'title' => 'Profile',
                'path' => '/admin/profile',
                'icon' => 'User'
            ],
            [
                'id' => 'logout',
                'title' => 'Logout',
                'action' => 'logout',
                'icon' => 'LogOut'
            ]
        ];
    }

    /**
     * Get footer menu items
     *
     * @return array
     */
    private function getFooterMenuItems()
    {
        return [
            'company' => [
                ['title' => 'About Us', 'path' => '/about'],
                ['title' => 'Services', 'path' => '/services'],
                ['title' => 'Contact', 'path' => '/contact']
            ],
            'legal' => [
                ['title' => 'Privacy Policy', 'path' => '/privacy'],
                ['title' => 'Terms of Service', 'path' => '/terms'],
                ['title' => 'Disclaimer', 'path' => '/disclaimer']
            ],
            'support' => [
                ['title' => 'Help Center', 'path' => '/help'],
                ['title' => 'FAQ', 'path' => '/faq'],
                ['title' => 'Contact Support', 'path' => '/support']
            ]
        ];
    }

    /**
     * Get user permissions
     *
     * @param $user
     * @return array
     */
    private function getUserPermissions($user)
    {
        if (!$user) {
            return [];
        }

        return [
            'role' => $user->role ?? 'user',
            'permissions' => $this->extractUserPermissions($user),
            'is_admin' => in_array($user->role, ['admin', 'super_admin']),
            'is_doctor' => $user->role === 'doctor',
            'is_nurse' => $user->role === 'nurse',
            'is_staff' => in_array($user->role, ['staff', 'receptionist'])
        ];
    }

    /**
     * Extract user permissions from role relationships
     *
     * @param $user
     * @return array
     */
    private function extractUserPermissions($user)
    {
        // Default permissions based on role
        $defaultPermissions = [
            'super_admin' => ['*'],
            'admin' => [
                'dashboard.view', 'patients.view', 'patients.create', 'patients.edit',
                'consultations.view', 'consultations.create', 'consultations.edit',
                'staff.view', 'staff.create', 'staff.edit',
                'billing.view', 'billing.create', 'reports.view', 'settings.view'
            ],
            'doctor' => [
                'dashboard.view', 'patients.view', 'patients.create', 'patients.edit',
                'consultations.view', 'consultations.create', 'consultations.edit'
            ],
            'nurse' => [
                'dashboard.view', 'patients.view', 'consultations.view'
            ],
            'staff' => [
                'dashboard.view', 'patients.view'
            ]
        ];

        $role = $user->role ?? 'staff';
        return $defaultPermissions[$role] ?? $defaultPermissions['staff'];
    }

    /**
     * Get dropdown options for forms
     *
     * @return array
     */
    private function getDropdownOptions()
    {
        return [
            'genders' => [
                ['value' => 'male', 'label' => 'Male'],
                ['value' => 'female', 'label' => 'Female'],
                ['value' => 'other', 'label' => 'Other']
            ],
            'blood_types' => [
                ['value' => 'A+', 'label' => 'A+'],
                ['value' => 'A-', 'label' => 'A-'],
                ['value' => 'B+', 'label' => 'B+'],
                ['value' => 'B-', 'label' => 'B-'],
                ['value' => 'AB+', 'label' => 'AB+'],
                ['value' => 'AB-', 'label' => 'AB-'],
                ['value' => 'O+', 'label' => 'O+'],
                ['value' => 'O-', 'label' => 'O-']
            ],
            'marital_status' => [
                ['value' => 'single', 'label' => 'Single'],
                ['value' => 'married', 'label' => 'Married'],
                ['value' => 'divorced', 'label' => 'Divorced'],
                ['value' => 'widowed', 'label' => 'Widowed']
            ],
            'departments' => [
                ['value' => 'cardiology', 'label' => 'Cardiology'],
                ['value' => 'neurology', 'label' => 'Neurology'],
                ['value' => 'orthopedics', 'label' => 'Orthopedics'],
                ['value' => 'pediatrics', 'label' => 'Pediatrics'],
                ['value' => 'general', 'label' => 'General Medicine'],
                ['value' => 'emergency', 'label' => 'Emergency'],
                ['value' => 'surgery', 'label' => 'Surgery']
            ],
            'specializations' => [
                ['value' => 'general_practitioner', 'label' => 'General Practitioner'],
                ['value' => 'cardiologist', 'label' => 'Cardiologist'],
                ['value' => 'neurologist', 'label' => 'Neurologist'],
                ['value' => 'orthopedic_surgeon', 'label' => 'Orthopedic Surgeon'],
                ['value' => 'pediatrician', 'label' => 'Pediatrician'],
                ['value' => 'emergency_physician', 'label' => 'Emergency Physician']
            ],
            'countries' => [
                ['value' => 'UG', 'label' => 'Uganda'],
                ['value' => 'KE', 'label' => 'Kenya'],
                ['value' => 'TZ', 'label' => 'Tanzania'],
                ['value' => 'RW', 'label' => 'Rwanda'],
                ['value' => 'BI', 'label' => 'Burundi'],
                ['value' => 'SS', 'label' => 'South Sudan'],
                ['value' => 'CD', 'label' => 'Democratic Republic of Congo']
            ],
            'user_roles' => [
                ['value' => 'admin', 'label' => 'Administrator'],
                ['value' => 'doctor', 'label' => 'Doctor'],
                ['value' => 'nurse', 'label' => 'Nurse'],
                ['value' => 'staff', 'label' => 'Staff'],
                ['value' => 'receptionist', 'label' => 'Receptionist']
            ],
            'appointment_status' => [
                ['value' => 'scheduled', 'label' => 'Scheduled'],
                ['value' => 'confirmed', 'label' => 'Confirmed'],
                ['value' => 'in_progress', 'label' => 'In Progress'],
                ['value' => 'completed', 'label' => 'Completed'],
                ['value' => 'cancelled', 'label' => 'Cancelled'],
                ['value' => 'no_show', 'label' => 'No Show']
            ],
            'payment_methods' => [
                ['value' => 'cash', 'label' => 'Cash'],
                ['value' => 'card', 'label' => 'Credit/Debit Card'],
                ['value' => 'mobile_money', 'label' => 'Mobile Money'],
                ['value' => 'bank_transfer', 'label' => 'Bank Transfer'],
                ['value' => 'insurance', 'label' => 'Insurance']
            ]
        ];
    }

    /**
     * Get UI constants and configurations
     *
     * @return array
     */
    private function getUIConstants()
    {
        return [
            'pagination' => [
                'default_per_page' => 20,
                'options' => [10, 20, 50, 100]
            ],
            'date_formats' => [
                'display' => 'Y-m-d',
                'input' => 'Y-m-d',
                'datetime' => 'Y-m-d H:i:s'
            ],
            'validation' => [
                'phone_pattern' => '^[+]?[0-9]{10,15}$',
                'email_pattern' => '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$',
                'password_min_length' => 8
            ],
            'messages' => [
                'loading' => 'Loading...',
                'no_data' => 'No data available',
                'error' => 'An error occurred. Please try again.',
                'success' => 'Operation completed successfully',
                'confirmation' => 'Are you sure you want to continue?'
            ],
            'theme' => [
                'primary_color' => '#0a1e34',
                'secondary_color' => '#f59e0b',
                'success_color' => '#10b981',
                'error_color' => '#ef4444',
                'warning_color' => '#f59e0b',
                'info_color' => '#3b82f6'
            ]
        ];
    }

    /**
     * Get meta information about the application
     *
     * @return array
     */
    private function getMetaInformation()
    {
        return [
            'description' => 'Professional Hospital Management System',
            'keywords' => 'hospital, healthcare, management, medical, patient',
            'author' => 'MediCare Development Team',
            'copyright' => '© 2025 MediCare Hospital. All rights reserved.',
            'contact' => [
                'support_email' => 'support@medicare.com',
                'phone' => '+256 700 000 000'
            ]
        ];
    }

    /**
     * Get version information
     *
     * @return array
     */
    private function getVersionInformation()
    {
        return [
            'api_version' => '1.0.0',
            'frontend_version' => '1.0.0',
            'last_updated' => now()->toDateString(),
            'build' => 'stable'
        ];
    }

    /**
     * Check if user has permission for menu item
     *
     * @param $user
     * @param array $menuItem
     * @return bool
     */
    private function userHasMenuPermission($user, $menuItem)
    {
        if (!$user || !isset($menuItem['permission']) || empty($menuItem['permission'])) {
            return true;
        }

        $userPermissions = $this->extractUserPermissions($user);
        
        // Super admin has all permissions
        if (in_array('*', $userPermissions)) {
            return true;
        }

        return in_array($menuItem['permission'], $userPermissions);
    }

    /**
     * Normalize icon names for frontend consistency
     *
     * @param string $icon
     * @return string
     */
    private function normalizeIcon($icon)
    {
        $iconMap = [
            'fa-dashboard' => 'BarChart3',
            'fa-users' => 'Users',
            'fa-calendar' => 'Calendar',
            'fa-activity' => 'Activity',
            'fa-building' => 'Building2',
            'fa-user-md' => 'UserCheck',
            'fa-credit-card' => 'CreditCard',
            'fa-file-text' => 'FileText',
            'fa-cog' => 'Settings',
            'fa-list' => 'List'
        ];

        return $iconMap[$icon] ?? $icon;
    }

    /**
     * Get React component name for URI
     *
     * @param string $uri
     * @return string
     */
    private function getReactComponent($uri)
    {
        $componentMap = [
            'dashboard' => 'Dashboard',
            'patients' => 'Patients',
            'consultations' => 'Consultations',
            'medical-services' => 'MedicalServices',
            'departments' => 'Departments',
            'staff' => 'Staff',
            'billing' => 'Billing',
            'reports' => 'Reports',
            'settings' => 'Settings'
        ];

        return $componentMap[$uri] ?? ucfirst(str_replace('-', '', $uri));
    }

    /**
     * Convert backend URI to React Router path
     *
     * @param string $uri
     * @return string
     */
    private function convertUriToReactPath($uri)
    {
        return '/admin/' . $uri;
    }

    /**
     * Get fallback admin menu if database is unavailable
     *
     * @return array
     */
    private function getFallbackAdminMenu()
    {
        return $this->getAdminMenuItems(null);
    }

    /**
     * Clear manifest cache
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearCache(Request $request)
    {
        try {
            $user = auth()->user();
            $cacheKey = 'app_manifest_' . ($user->id ?? 'guest');
            
            Cache::forget($cacheKey);
            
            return $this->success(null, 'Manifest cache cleared successfully');
            
        } catch (\Exception $e) {
            return $this->error('Failed to clear manifest cache: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Generate public manifest for unauthenticated users
     * This provides basic app configuration and public navigation
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function publicManifest(Request $request)
    {
        try {
            $manifest = Cache::remember('public_manifest', 600, function () {
                return [
                    'app' => $this->getAppConfiguration(),
                    'navigation' => [
                        'public_menu' => $this->getPublicMenuItems(),
                        'user_menu' => $this->getUserMenuItems(null),
                        'footer_menu' => $this->getFooterMenuItems()
                    ],
                    'options' => $this->getDropdownOptions(),
                    'ui' => $this->getUIConstants(),
                    'meta' => $this->getMetaInformation(),
                    'version' => $this->getVersionInformation(),
                    'generated_at' => now()->toISOString(),
                    'user_context' => null
                ];
            });

            return $this->success($manifest, 'Public manifest retrieved successfully');
            
        } catch (\Exception $e) {
            return $this->error('Failed to generate public manifest: ' . $e->getMessage(), 500);
        }
    }
}
