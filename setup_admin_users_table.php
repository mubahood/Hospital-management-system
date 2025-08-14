<?php
// Create admin_users table with ALL required columns
echo "🏗️ Creating admin_users table with all required columns...\n";

try {
    $pdo = new PDO('mysql:unix_} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>et=/Applications/MAMP/tmp/mysql/mysql.sock;dbname=hospital', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create admin_users table with ALL the columns you need
    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS `admin_users` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `enterprise_id` bigint(20) unsigned NULL,
        `name` varchar(255) NOT NULL,
        `email` varchar(255) NULL UNIQUE,
        `email_verified_at` timestamp NULL,
        `password` varchar(255) NOT NULL,
        `first_name` varchar(255) NULL,
        `last_name` varchar(255) NULL,
        `username` varchar(255) NULL UNIQUE,
        `phone_number_1` varchar(255) NULL,
        `phone_number_2` varchar(255) NULL,
        `date_of_birth` date NULL,
        `place_of_birth` varchar(255) NULL,
        `sex` enum('male','female','other') NULL,
        `home_address` text NULL,
        `current_address` text NULL,
        `nationality` varchar(255) NULL,
        `religion` varchar(255) NULL,
        `spouse_name` varchar(255) NULL,
        `spouse_phone` varchar(255) NULL,
        `father_name` varchar(255) NULL,
        `father_phone` varchar(255) NULL,
        `mother_name` varchar(255) NULL,
        `mother_phone` varchar(255) NULL,
        `languages` varchar(255) NULL,
        `emergency_person_name` varchar(255) NULL,
        `emergency_person_phone` varchar(255) NULL,
        `emergency_contact_relationship` varchar(255) NULL,
        `national_id_number` varchar(255) NULL,
        `passport_number` varchar(255) NULL,
        `tin` varchar(255) NULL,
        `nssf_number` varchar(255) NULL,
        `bank_name` varchar(255) NULL,
        `bank_account_number` varchar(255) NULL,
        `marital_status` varchar(255) NULL,
        `title` varchar(255) NULL,
        `company_id` bigint(20) unsigned NULL,
        `user_type` varchar(255) DEFAULT 'patient',
        `patient_status` varchar(255) NULL,
        `avatar` varchar(255) NULL,
        `intro` text NULL,
        `rate` decimal(8,2) DEFAULT 0,
        `belongs_to_company` varchar(255) NULL,
        `card_status` varchar(255) DEFAULT 'inactive',
        `card_number` varchar(255) NULL,
        `card_balance` decimal(15,2) DEFAULT 0,
        `card_accepts_credit` tinyint(1) DEFAULT 0,
        `card_max_credit` decimal(15,2) DEFAULT 0,
        `card_accepts_cash` tinyint(1) DEFAULT 1,
        `is_dependent` tinyint(1) DEFAULT 0,
        `dependent_status` varchar(255) NULL,
        `dependent_id` bigint(20) unsigned NULL,
        `card_expiry` date NULL,
        `belongs_to_company_status` varchar(255) NULL,
        `medical_history` text NULL,
        `allergies` text NULL,
        `current_medications` text NULL,
        `blood_type` varchar(255) NULL,
        `height` decimal(5,2) NULL,
        `weight` decimal(5,2) NULL,
        `insurance_provider` varchar(255) NULL,
        `insurance_policy_number` varchar(255) NULL,
        `insurance_expiry_date` date NULL,
        `family_doctor_name` varchar(255) NULL,
        `family_doctor_phone` varchar(255) NULL,
        `employment_status` varchar(255) NULL,
        `employer_name` varchar(255) NULL,
        `annual_income` decimal(15,2) NULL,
        `education_level` varchar(255) NULL,
        `preferred_language` varchar(255) NULL,
        `remember_token` varchar(100) NULL,
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_enterprise_id` (`enterprise_id`),
        KEY `idx_user_type` (`user_type`),
        KEY `idx_card_number` (`card_number`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($createTableSQL);
    echo "✅ admin_users table created successfully!\n\n";
    
    // Now insert the 50 users
    echo "👥 Creating 50 comprehensive users...\n";
    
    $userTypes = ['patient', 'doctor', 'nurse', 'administrator', 'pharmacist', 'technician'];
    $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
    $maritalStatuses = ['single', 'married', 'divorced', 'widowed'];
    
    for ($i = 1; $i <= 50; $i++) {
        $firstName = ['John', 'Mary', 'James', 'Sarah', 'David', 'Jennifer', 'Michael', 'Lisa', 'Robert', 'Karen'][rand(0, 9)];
        $lastName = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez'][rand(0, 9)];
        $email = strtolower($firstName . '.' . $lastName . $i . '@hospital.com');
        $userType = $userTypes[rand(0, count($userTypes) - 1)];
        $sex = rand(0, 1) ? 'male' : 'female';
        
        $insertSQL = "INSERT INTO admin_users (
            enterprise_id, name, email, password, first_name, last_name, username,
            phone_number_1, date_of_birth, sex, home_address, nationality, religion,
            emergency_person_name, emergency_person_phone, national_id_number,
            marital_status, title, user_type, card_number, card_balance,
            blood_type, height, weight, employment_status, education_level,
            created_at, updated_at
        ) VALUES (
            1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
        )";
        
        $stmt = $pdo->prepare($insertSQL);
        $stmt->execute([
            $firstName . ' ' . $lastName, // name
            $email, // email
            password_hash('password123', PASSWORD_DEFAULT), // password
            $firstName, // first_name
            $lastName, // last_name
            $email, // username
            '+256' . rand(700000000, 799999999), // phone_number_1
            date('Y-m-d', strtotime('-' . rand(18, 80) . ' years')), // date_of_birth
            $sex, // sex
            rand(1, 999) . ' Kampala Road, Kampala', // home_address
            'Ugandan', // nationality
            'Christian', // religion
            $firstName . ' Emergency Contact', // emergency_person_name
            '+256' . rand(700000000, 799999999), // emergency_person_phone
            'NIN' . rand(1000000000, 9999999999), // national_id_number
            $maritalStatuses[rand(0, count($maritalStatuses) - 1)], // marital_status
            ($userType === 'doctor' ? 'Dr.' : ($sex === 'male' ? 'Mr.' : 'Ms.')), // title
            $userType, // user_type
            'HOS-' . rand(1000, 9999) . '-' . rand(1000, 9999), // card_number
            rand(0, 10000), // card_balance
            $bloodTypes[rand(0, count($bloodTypes) - 1)], // blood_type
            rand(150, 200), // height
            rand(45, 120), // weight
            'employed', // employment_status
            'Bachelor', // education_level
        ]);
        
        if ($i % 10 == 0) {
            echo "✓ Created $i users...\n";
        }
    }
    
    echo "\n🎉 Successfully created 50 users in admin_users table!\n";
    
    // Verify the data
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM admin_users');
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "📊 Total users in admin_users table: {$total['total']}\n";
    
    // Show user types
    $stmt = $pdo->query('SELECT user_type, COUNT(*) as count FROM admin_users GROUP BY user_type');
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\n📈 User type distribution:\n";
    foreach ($results as $result) {
        echo "  - {$result['user_type']}: {$result['count']} users\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
