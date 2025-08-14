@php
    $menuItems = [
        [
            'title' => 'Dashboard',
            'icon' => 'fas fa-tachometer-alt',
            'route' => 'admin.dashboard',
            'permission' => 'dashboard.view'
        ],
        [
            'title' => 'Patients',
            'icon' => 'fas fa-users',
            'route' => 'admin.patients.index',
            'permission' => 'patients.view',
            'submenu' => [
                ['title' => 'All Patients', 'route' => 'admin.patients.index', 'permission' => 'patients.view'],
                ['title' => 'Add Patient', 'route' => 'admin.patients.create', 'permission' => 'patients.create'],
                ['title' => 'Patient Reports', 'route' => 'admin.patients.reports', 'permission' => 'patients.view']
            ]
        ],
        [
            'title' => 'Doctors',
            'icon' => 'fas fa-user-md',
            'route' => 'admin.doctors.index',
            'permission' => 'doctors.view',
            'submenu' => [
                ['title' => 'All Doctors', 'route' => 'admin.doctors.index', 'permission' => 'doctors.view'],
                ['title' => 'Add Doctor', 'route' => 'admin.doctors.create', 'permission' => 'doctors.create'],
                ['title' => 'Doctor Schedules', 'route' => 'admin.doctors.schedules', 'permission' => 'doctors.view']
            ]
        ],
        [
            'title' => 'Appointments',
            'icon' => 'fas fa-calendar-check',
            'route' => 'admin.appointments.index',
            'permission' => 'appointments.view',
            'submenu' => [
                ['title' => 'All Appointments', 'route' => 'admin.appointments.index', 'permission' => 'appointments.view'],
                ['title' => 'Book Appointment', 'route' => 'admin.appointments.create', 'permission' => 'appointments.create'],
                ['title' => 'Appointment Calendar', 'route' => 'admin.appointments.calendar', 'permission' => 'appointments.view']
            ]
        ],
        [
            'title' => 'Medical Records',
            'icon' => 'fas fa-file-medical',
            'route' => 'admin.medical-records.index',
            'permission' => 'medical_records.view',
            'submenu' => [
                ['title' => 'All Records', 'route' => 'admin.medical-records.index', 'permission' => 'medical_records.view'],
                ['title' => 'Add Record', 'route' => 'admin.medical-records.create', 'permission' => 'medical_records.create']
            ]
        ],
        [
            'title' => 'Departments',
            'icon' => 'fas fa-building',
            'route' => 'admin.departments.index',
            'permission' => 'departments.view',
            'submenu' => [
                ['title' => 'All Departments', 'route' => 'admin.departments.index', 'permission' => 'departments.view'],
                ['title' => 'Add Department', 'route' => 'admin.departments.create', 'permission' => 'departments.create']
            ]
        ],
        [
            'title' => 'Billing',
            'icon' => 'fas fa-file-invoice-dollar',
            'route' => 'admin.billing.index',
            'permission' => 'billing.view',
            'submenu' => [
                ['title' => 'All Bills', 'route' => 'admin.billing.index', 'permission' => 'billing.view'],
                ['title' => 'Create Bill', 'route' => 'admin.billing.create', 'permission' => 'billing.create'],
                ['title' => 'Payment History', 'route' => 'admin.billing.payments', 'permission' => 'billing.view']
            ]
        ],
        [
            'title' => 'Inventory',
            'icon' => 'fas fa-boxes',
            'route' => 'admin.inventory.index',
            'permission' => 'inventory.view',
            'submenu' => [
                ['title' => 'All Items', 'route' => 'admin.inventory.index', 'permission' => 'inventory.view'],
                ['title' => 'Add Item', 'route' => 'admin.inventory.create', 'permission' => 'inventory.create'],
                ['title' => 'Stock Reports', 'route' => 'admin.inventory.reports', 'permission' => 'inventory.view']
            ]
        ],
        [
            'title' => 'Reports',
            'icon' => 'fas fa-chart-bar',
            'route' => 'admin.reports.index',
            'permission' => 'reports.view',
            'submenu' => [
                ['title' => 'Patient Reports', 'route' => 'admin.reports.patients', 'permission' => 'reports.view'],
                ['title' => 'Financial Reports', 'route' => 'admin.reports.financial', 'permission' => 'reports.view'],
                ['title' => 'Inventory Reports', 'route' => 'admin.reports.inventory', 'permission' => 'reports.view'],
                ['title' => 'Appointment Reports', 'route' => 'admin.reports.appointments', 'permission' => 'reports.view']
            ]
        ],
        [
            'title' => 'Administration',
            'icon' => 'fas fa-cogs',
            'route' => 'admin.settings.index',
            'permission' => 'admin.manage',
            'submenu' => [
                ['title' => 'Users', 'route' => 'admin.users.index', 'permission' => 'users.view'],
                ['title' => 'Roles & Permissions', 'route' => 'admin.roles.index', 'permission' => 'roles.view'],
                ['title' => 'System Settings', 'route' => 'admin.settings.index', 'permission' => 'admin.manage'],
                ['title' => 'Backup Management', 'route' => 'admin.backups.index', 'permission' => 'admin.manage']
            ]
        ]
    ];
@endphp

<nav class="mobile-responsive-nav">
    <!-- Mobile Header -->
    <div class="mobile-header d-lg-none">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-6">
                    <div class="mobile-logo">
                        <i class="fas fa-hospital-alt text-primary me-2"></i>
                        <span class="logo-text">{{ env('APP_NAME', 'Hospital') }}</span>
                    </div>
                </div>
                <div class="col-6 text-end">
                    <button class="mobile-menu-toggle btn btn-outline-primary" type="button">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div class="mobile-sidebar-overlay d-lg-none"></div>

    <!-- Navigation Sidebar -->
    <div class="nav-sidebar">
        <!-- Desktop Logo -->
        <div class="sidebar-logo d-none d-lg-block">
            <i class="fas fa-hospital-alt text-primary me-2"></i>
            <span class="logo-text">{{ env('APP_NAME', 'Hospital Management') }}</span>
        </div>

        <!-- User Profile Section -->
        <div class="user-profile-section">
            <div class="user-avatar">
                <img src="{{ Auth::user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=0066cc&color=fff' }}" 
                     alt="User Avatar" class="avatar-img">
            </div>
            <div class="user-info">
                <div class="user-name">{{ Auth::user()->name }}</div>
                <div class="user-role">{{ Auth::user()->roles->first()->name ?? 'User' }}</div>
            </div>
            <div class="user-actions">
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cog"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('admin.profile') }}"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.settings') }}"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('admin.logout') }}"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <div class="nav-menu">
            <ul class="nav-list">
                @foreach($menuItems as $item)
                    @can($item['permission'])
                        <li class="nav-item {{ isset($item['submenu']) ? 'has-submenu' : '' }} {{ request()->routeIs($item['route'] . '*') ? 'active' : '' }}">
                            <a href="{{ isset($item['submenu']) ? '#' : route($item['route']) }}" 
                               class="nav-link {{ isset($item['submenu']) ? 'submenu-toggle' : '' }}"
                               @if(isset($item['submenu'])) data-bs-toggle="collapse" data-bs-target="#submenu-{{ $loop->index }}" @endif>
                                <span class="nav-icon">
                                    <i class="{{ $item['icon'] }}"></i>
                                </span>
                                <span class="nav-text">{{ $item['title'] }}</span>
                                @if(isset($item['submenu']))
                                    <span class="submenu-arrow">
                                        <i class="fas fa-chevron-down"></i>
                                    </span>
                                @endif
                            </a>
                            
                            @if(isset($item['submenu']))
                                <div class="collapse submenu {{ request()->routeIs($item['route'] . '*') ? 'show' : '' }}" id="submenu-{{ $loop->index }}">
                                    <ul class="submenu-list">
                                        @foreach($item['submenu'] as $subitem)
                                            @can($subitem['permission'])
                                                <li class="submenu-item {{ request()->routeIs($subitem['route']) ? 'active' : '' }}">
                                                    <a href="{{ route($subitem['route']) }}" class="submenu-link">
                                                        <span class="submenu-text">{{ $subitem['title'] }}</span>
                                                    </a>
                                                </li>
                                            @endcan
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </li>
                    @endcan
                @endforeach
            </ul>
        </div>

        <!-- Navigation Footer -->
        <div class="nav-footer">
            <div class="system-info">
                <div class="system-status">
                    <span class="status-indicator online"></span>
                    <span class="status-text">System Online</span>
                </div>
                <div class="version-info">
                    <small class="text-muted">Version 1.0.0</small>
                </div>
            </div>
        </div>
    </div>
</nav>

<style>
/* Mobile Responsive Navigation Styles */
.mobile-responsive-nav {
    position: relative;
}

/* Mobile Header */
.mobile-header {
    background: white;
    border-bottom: 1px solid #e9ecef;
    padding: 15px 0;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1040;
}

.mobile-logo {
    display: flex;
    align-items: center;
    font-size: 1.2rem;
    font-weight: 600;
    color: #2c3e50;
}

.mobile-menu-toggle {
    border: none;
    padding: 8px 12px;
    border-radius: 6px;
}

/* Mobile Sidebar Overlay */
.mobile-sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1041;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.mobile-sidebar-overlay.active {
    opacity: 1;
    visibility: visible;
}

/* Navigation Sidebar */
.nav-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 280px;
    height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    z-index: 1042;
    overflow-y: auto;
    transition: transform 0.3s ease;
    display: flex;
    flex-direction: column;
}

/* Mobile: Hide sidebar by default */
@media (max-width: 991.98px) {
    .nav-sidebar {
        transform: translateX(-100%);
    }
    
    .nav-sidebar.active {
        transform: translateX(0);
    }
    
    /* Adjust body padding for mobile header */
    body {
        padding-top: 70px;
    }
}

/* Desktop: Show sidebar */
@media (min-width: 992px) {
    .nav-sidebar {
        position: relative;
        transform: none;
    }
    
    body {
        padding-top: 0;
    }
}

/* Sidebar Logo */
.sidebar-logo {
    padding: 25px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    font-size: 1.3rem;
    font-weight: 600;
    color: white;
}

/* User Profile Section */
.user-profile-section {
    padding: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    gap: 15px;
}

.user-avatar {
    flex-shrink: 0;
}

.avatar-img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    border: 2px solid rgba(255, 255, 255, 0.2);
}

.user-info {
    flex-grow: 1;
    min-width: 0;
}

.user-name {
    font-weight: 600;
    color: white;
    font-size: 1rem;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-role {
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.8);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-actions {
    flex-shrink: 0;
}

/* Navigation Menu */
.nav-menu {
    flex-grow: 1;
    padding: 20px 0;
}

.nav-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.nav-item {
    margin-bottom: 5px;
}

.nav-link {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
}

.nav-link:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

.nav-item.active > .nav-link {
    background: rgba(255, 255, 255, 0.15);
    color: white;
    border-right: 3px solid #fff;
}

.nav-icon {
    width: 20px;
    margin-right: 15px;
    text-align: center;
    flex-shrink: 0;
}

.nav-text {
    flex-grow: 1;
    font-weight: 500;
}

.submenu-arrow {
    flex-shrink: 0;
    transition: transform 0.3s ease;
}

.nav-item.has-submenu.active .submenu-arrow {
    transform: rotate(180deg);
}

/* Submenu */
.submenu {
    background: rgba(0, 0, 0, 0.1);
}

.submenu-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.submenu-item {
    margin-bottom: 2px;
}

.submenu-link {
    display: block;
    padding: 10px 20px 10px 55px;
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: all 0.3s ease;
}

.submenu-link:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

.submenu-item.active .submenu-link {
    background: rgba(255, 255, 255, 0.15);
    color: white;
    border-right: 2px solid #fff;
}

/* Navigation Footer */
.nav-footer {
    padding: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    margin-top: auto;
}

.system-info {
    text-align: center;
}

.system-status {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.9rem;
}

.status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: 8px;
}

.status-indicator.online {
    background: #28a745;
    box-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
}

.status-indicator.offline {
    background: #dc3545;
}

.version-info {
    color: rgba(255, 255, 255, 0.6);
}

/* Responsive Design */
@media (max-width: 768px) {
    .nav-sidebar {
        width: 250px;
    }
    
    .user-profile-section {
        padding: 15px;
    }
    
    .avatar-img {
        width: 40px;
        height: 40px;
    }
    
    .user-name {
        font-size: 0.9rem;
    }
    
    .user-role {
        font-size: 0.8rem;
    }
}

@media (max-width: 480px) {
    .nav-sidebar {
        width: 220px;
    }
    
    .nav-link {
        padding: 10px 15px;
    }
    
    .nav-icon {
        margin-right: 12px;
    }
    
    .submenu-link {
        padding: 8px 15px 8px 45px;
    }
}

/* Scrollbar Styling */
.nav-sidebar::-webkit-scrollbar {
    width: 6px;
}

.nav-sidebar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
}

.nav-sidebar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 3px;
}

.nav-sidebar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const sidebar = document.querySelector('.nav-sidebar');
    const overlay = document.querySelector('.mobile-sidebar-overlay');
    
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.classList.toggle('sidebar-open');
        });
    }
    
    if (overlay) {
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.classList.remove('sidebar-open');
        });
    }
    
    // Submenu toggle
    const submenuToggles = document.querySelectorAll('.submenu-toggle');
    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            if (this.getAttribute('href') === '#') {
                e.preventDefault();
            }
            
            const parentItem = this.closest('.nav-item');
            parentItem.classList.toggle('active');
        });
    });
    
    // Close mobile menu when clicking on a regular nav link
    const navLinks = document.querySelectorAll('.nav-link:not(.submenu-toggle)');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 992) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                document.body.classList.remove('sidebar-open');
            }
        });
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.classList.remove('sidebar-open');
        }
    });
});
</script>
