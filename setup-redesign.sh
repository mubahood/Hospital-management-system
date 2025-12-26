#!/bin/bash

###############################################################################
# Laravel Admin Redesign - Quick Setup Script
###############################################################################
#
# This script automates the setup of the redesigned Laravel Admin backend
# to match your React frontend design system.
#
# Usage: bash setup-redesign.sh
#
###############################################################################

echo "================================================"
echo "Laravel Admin Redesign - Setup Script"
echo "================================================"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored messages
print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

# Check if we're in the correct directory
if [ ! -f "artisan" ]; then
    print_error "Error: artisan file not found. Please run this script from your Laravel root directory."
    exit 1
fi

print_success "Found Laravel installation"

# Step 1: Check if CSS file exists
echo ""
echo "Step 1: Checking CSS files..."
if [ -f "public/css/admin-redesign.css" ]; then
    print_success "admin-redesign.css found"
else
    print_error "admin-redesign.css not found in public/css/"
    echo "Please ensure the CSS file is in the correct location."
    exit 1
fi

# Step 2: Check if login view exists
echo ""
echo "Step 2: Checking login view..."
if [ -f "resources/views/auth/login-redesigned.blade.php" ]; then
    print_success "login-redesigned.blade.php found"
else
    print_error "login-redesigned.blade.php not found"
    echo "Please ensure the login view is in the correct location."
    exit 1
fi

# Step 3: Backup original login view
echo ""
echo "Step 3: Backing up original files..."
if [ -f "resources/views/auth/login.blade.php" ]; then
    if [ ! -f "resources/views/auth/login-backup.blade.php" ]; then
        cp resources/views/auth/login.blade.php resources/views/auth/login-backup.blade.php
        print_success "Backed up original login.blade.php to login-backup.blade.php"
    else
        print_info "Backup already exists, skipping..."
    fi
fi

# Step 4: Replace login view
echo ""
echo "Step 4: Installing redesigned login view..."
read -p "Do you want to replace the current login view? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    cp resources/views/auth/login-redesigned.blade.php resources/views/auth/login.blade.php
    print_success "Login view replaced successfully"
else
    print_warning "Skipped login view replacement"
fi

# Step 5: Clear Laravel caches
echo ""
echo "Step 5: Clearing Laravel caches..."
php artisan cache:clear > /dev/null 2>&1
print_success "Cache cleared"

php artisan view:clear > /dev/null 2>&1
print_success "View cache cleared"

php artisan config:clear > /dev/null 2>&1
print_success "Config cache cleared"

# Step 6: Check for Laravel Admin
echo ""
echo "Step 6: Checking Laravel Admin installation..."
if [ -f "config/admin.php" ]; then
    print_success "Laravel Admin config found"
else
    print_warning "Laravel Admin config not found. Make sure Laravel Admin is installed."
fi

# Step 7: Verify assets are accessible
echo ""
echo "Step 7: Verifying assets..."
if [ -d "public/css" ]; then
    print_success "CSS directory accessible"
else
    print_error "CSS directory not found"
fi

if [ -d "public/assets/img" ]; then
    print_success "Images directory accessible"
else
    print_warning "Images directory not found. Logo may not display."
fi

# Step 8: Check for Font Awesome (optional)
echo ""
echo "Step 8: Checking Font Awesome..."
print_info "Font Awesome is loaded via CDN in the login page"
print_info "If you want to host it locally, download and add to public/css/"

# Step 9: Create symbolic link for storage (if needed)
echo ""
echo "Step 9: Checking storage link..."
if [ -L "public/storage" ]; then
    print_success "Storage link already exists"
else
    read -p "Do you want to create storage link? (y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        php artisan storage:link
        print_success "Storage link created"
    else
        print_warning "Skipped storage link creation"
    fi
fi

# Step 10: Final instructions
echo ""
echo "================================================"
echo "Setup Complete!"
echo "================================================"
echo ""
print_success "The Laravel Admin redesign has been set up successfully!"
echo ""
echo "Next Steps:"
echo "1. Visit your admin login page to see the new design"
echo "2. Review LARAVEL_ADMIN_REDESIGN_DOCUMENTATION.md for detailed usage"
echo "3. Customize colors in public/css/admin-redesign.css if needed"
echo "4. Update your blade templates to use the new design classes"
echo ""
echo "Quick Test:"
echo "  ${BLUE}php artisan serve${NC}"
echo "  Then visit: http://localhost:8000/admin"
echo ""
echo "Troubleshooting:"
echo "  • If CSS doesn't load: Clear browser cache (Cmd+Shift+R / Ctrl+Shift+R)"
echo "  • If changes don't appear: Run 'php artisan view:clear' again"
echo "  • For issues: Check LARAVEL_ADMIN_REDESIGN_DOCUMENTATION.md"
echo ""
print_info "Original files backed up with '-backup' suffix"
echo ""
echo "================================================"
echo "Happy coding! 🚀"
echo "================================================"
