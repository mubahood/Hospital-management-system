# Clinic Dynamics - Hospital Management System

A comprehensive Laravel-based hospital management system designed to streamline healthcare operations, patient management, and administrative tasks.

## 🏥 Overview

Clinic Dynamics is a feature-rich hospital management system built with Laravel 8 and Laravel Admin. The system provides end-to-end healthcare management capabilities including patient registration, consultation management, medical services, billing, inventory management, and reporting.

## ✨ Key Features

### 👥 Patient Management
- **Patient Registration**: Complete patient profiling with personal, medical, and contact information
- **Patient Records**: Comprehensive medical history tracking including:
  - Medical conditions and allergies
  - Surgery and hospitalization history
  - Smoking and alcohol history
  - Dental and medical assessments
  - Chief complaints and treatment history

### 🩺 Medical Services
- **Consultation Management**: End-to-end consultation workflow
- **Medical Services Assignment**: Assign services to specialists and doctors
- **Treatment Records**: Detailed treatment documentation with photo support
- **Dose Management**: Medication dosage tracking and scheduling
- **Progress Monitoring**: Track patient progress throughout treatment

### 💰 Financial Management
- **Billing System**: Automated billing with itemized charges
- **Payment Processing**: Multi-payment method support including FlutterWave integration
- **Invoice Generation**: Automated PDF invoice and report generation
- **Financial Tracking**: Complete payment history and due amount tracking

### 📦 Inventory Management
- **Stock Management**: Track medical supplies and equipment
- **Stock Categories**: Organized inventory categorization
- **Stock-out Records**: Automatic inventory deduction during services
- **Quantity Tracking**: Real-time stock quantity monitoring

### 🏢 Administrative Features
- **Multi-Company Support**: Support for multiple healthcare facilities
- **Role-Based Access**: Comprehensive user role management
- **Department Management**: Organize staff by departments
- **Employee Management**: Staff registration and management
- **Meeting Scheduling**: Internal meeting coordination

### 📊 Reporting & Analytics
- **Medical Reports**: Generate comprehensive medical reports
- **Financial Reports**: Billing and payment analytics
- **Dashboard Analytics**: Real-time system overview
- **Export Capabilities**: CSV and PDF export functionality

## 🛠 Technology Stack

- **Framework**: Laravel 8.65+
- **Admin Panel**: Laravel Admin (Encore Admin)
- **Database**: MySQL
- **PDF Generation**: DomPDF
- **Authentication**: JWT Auth
- **Frontend**: AdminLTE theme with Bootstrap
- **Rich Text Editor**: Quill.js, Summernote
- **Charts**: Chart.js
- **File Processing**: Zebra Image

## 📋 System Requirements

- PHP 7.3+ or 8.0+
- MySQL 5.7+ or 8.0+
- Composer
- Node.js & NPM
- Web server (Apache/Nginx)

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd hospital
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret
```

### 4. Database Setup
Update your `.env` file with database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_USERNAME=your_username
DB_DATABASE=hospital
DB_PASSWORD=your_password
```

### 5. Run Migrations
```bash
php artisan migrate
```

### 6. Seed Database (Optional)
```bash
php artisan db:seed
```

### 7. Build Frontend Assets
```bash
npm run dev
# or for production
npm run production
```

### 8. Storage Setup
```bash
php artisan storage:link
```

## ⚙️ Configuration

### Admin Panel Setup
The system uses Laravel Admin for the administrative interface. Default configuration can be found in `config/admin.php`.

### Mail Configuration
Update mail settings in `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
```

### Payment Gateway
Configure FlutterWave for payment processing:
```env
FLUTTERWAVE_PUBLIC_KEY=your_public_key
FLUTTERWAVE_SECRET_KEY=your_secret_key
```

## 🔧 Usage

### Accessing the System
- **Admin Panel**: `http://your-domain/admin`
- **Main Application**: `http://your-domain`

### User Roles
- **System Administrator**: Full system access
- **Receptionist**: Patient registration and consultation management
- **Doctor/Specialist**: Medical services and treatment management
- **Billing Staff**: Financial management and invoice generation

### Core Workflows

#### 1. Patient Registration
1. Navigate to Patients section
2. Add new patient with complete information
3. Create patient medical record

#### 2. Consultation Process
1. Create new consultation for patient
2. Assign medical services to specialists
3. Complete medical services
4. Generate billing and process payments

#### 3. Inventory Management
1. Set up stock item categories
2. Add stock items with quantities
3. System automatically tracks usage during consultations

## 🏗 System Architecture

### Models Structure
- **User**: Patient and staff management
- **Consultation**: Core consultation workflow
- **MedicalService**: Individual medical services
- **BillingItem**: Billing components
- **PaymentRecord**: Payment tracking
- **StockItem**: Inventory management
- **Company**: Multi-tenant support

### Database Schema
The system uses a comprehensive database schema with over 40 tables covering:
- Patient management
- Consultation workflow
- Medical services
- Billing and payments
- Inventory management
- Administrative functions

## 📱 API Integration

The system includes API endpoints for:
- Patient data management
- Consultation status updates
- Payment processing
- Mobile app integration

## 🔒 Security Features

- JWT-based authentication
- Role-based access control
- CSRF protection
- SQL injection prevention
- XSS protection
- Secure file upload handling

## 📈 Reporting Features

### Available Reports
- **Medical Reports**: Comprehensive patient treatment reports
- **Financial Reports**: Billing and payment summaries
- **Inventory Reports**: Stock usage and availability
- **Administrative Reports**: System usage analytics

### Export Options
- PDF generation for invoices and reports
- CSV export for data analysis
- Print-friendly report formats

## 🛡 Backup & Maintenance

### Regular Maintenance
```bash
# Clear application cache
php artisan cache:clear

# Clear configuration cache
php artisan config:clear

# Optimize application
php artisan optimize
```

### Database Backup
Regular database backups are recommended using:
```bash
mysqldump -u username -p hospital > backup_$(date +%Y%m%d).sql
```

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 Support

For support and questions:
- Email: support@clinicdynamics.com
- Documentation: [System Documentation](docs/)
- Issues: [GitHub Issues](../../issues)

## 🎯 Future Enhancements

- Mobile application for patients
- Telemedicine integration
- Laboratory management module
- Pharmacy management system
- Advanced analytics and reporting
- Integration with external medical devices
- Multi-language support

## 📊 System Statistics

- **Models**: 25+ core models
- **Controllers**: 35+ admin controllers
- **Database Tables**: 40+ tables
- **Features**: 100+ distinct features
- **User Roles**: 4 primary roles
- **Supported Languages**: Extensible

---

**Clinic Dynamics** - Transforming Healthcare Management Through Technology

*Built with ❤️ using Laravel & Laravel Admin*

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
