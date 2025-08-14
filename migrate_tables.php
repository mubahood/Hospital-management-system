<?php
try {
    $pdo = new PDO('mysql:unix_socket=/Applications/MAMP/tmp/mysql/mysql.sock;dbname=hospital', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Adding all columns from users table to admin_users...\n";
    
    // Add all columns from users to admin_users
    $alterQueries = [
        "ALTER TABLE admin_users ADD COLUMN enterprise_id BIGINT UNSIGNED NULL AFTER id",
        "ALTER TABLE admin_users ADD COLUMN email VARCHAR(255) NULL AFTER name",
        "ALTER TABLE admin_users ADD COLUMN email_verified_at TIMESTAMP NULL AFTER email",
        "ALTER TABLE admin_users ADD COLUMN first_name VARCHAR(255) NULL AFTER password",
        "ALTER TABLE admin_users ADD COLUMN last_name VARCHAR(255) NULL AFTER first_name",
        "ALTER TABLE admin_users ADD COLUMN phone_number_1 VARCHAR(255) NULL AFTER last_name",
        "ALTER TABLE admin_users ADD COLUMN phone_number_2 VARCHAR(255) NULL AFTER phone_number_1",
        "ALTER TABLE admin_users ADD COLUMN date_of_birth DATE NULL AFTER phone_number_2",
        "ALTER TABLE admin_users ADD COLUMN place_of_birth VARCHAR(255) NULL AFTER date_of_birth",
        "ALTER TABLE admin_users ADD COLUMN sex ENUM('male', 'female', 'other') NULL AFTER place_of_birth",
        "ALTER TABLE admin_users ADD COLUMN home_address TEXT NULL AFTER sex",
        "ALTER TABLE admin_users ADD COLUMN current_address TEXT NULL AFTER home_address",
        "ALTER TABLE admin_users ADD COLUMN nationality VARCHAR(255) NULL AFTER current_address",
        "ALTER TABLE admin_users ADD COLUMN religion VARCHAR(255) NULL AFTER nationality",
        "ALTER TABLE admin_users ADD COLUMN spouse_name VARCHAR(255) NULL AFTER religion",
        "ALTER TABLE admin_users ADD COLUMN spouse_phone VARCHAR(255) NULL AFTER spouse_name",
        "ALTER TABLE admin_users ADD COLUMN father_name VARCHAR(255) NULL AFTER spouse_phone",
        "ALTER TABLE admin_users ADD COLUMN father_phone VARCHAR(255) NULL AFTER father_name",
        "ALTER TABLE admin_users ADD COLUMN mother_name VARCHAR(255) NULL AFTER father_phone",
        "ALTER TABLE admin_users ADD COLUMN mother_phone VARCHAR(255) NULL AFTER mother_name",
        "ALTER TABLE admin_users ADD COLUMN languages VARCHAR(255) NULL AFTER mother_phone",
        "ALTER TABLE admin_users ADD COLUMN emergency_person_name VARCHAR(255) NULL AFTER languages",
        "ALTER TABLE admin_users ADD COLUMN emergency_person_phone VARCHAR(255) NULL AFTER emergency_person_name",
        "ALTER TABLE admin_users ADD COLUMN emergency_contact_relationship VARCHAR(255) NULL AFTER emergency_person_phone",
        "ALTER TABLE admin_users ADD COLUMN national_id_number VARCHAR(255) NULL AFTER emergency_contact_relationship",
        "ALTER TABLE admin_users ADD COLUMN passport_number VARCHAR(255) NULL AFTER national_id_number",
        "ALTER TABLE admin_users ADD COLUMN tin VARCHAR(255) NULL AFTER passport_number",
        "ALTER TABLE admin_users ADD COLUMN nssf_number VARCHAR(255) NULL AFTER tin",
        "ALTER TABLE admin_users ADD COLUMN bank_name VARCHAR(255) NULL AFTER nssf_number",
        "ALTER TABLE admin_users ADD COLUMN bank_account_number VARCHAR(255) NULL AFTER bank_name",
        "ALTER TABLE admin_users ADD COLUMN marital_status VARCHAR(255) NULL AFTER bank_account_number",
        "ALTER TABLE admin_users ADD COLUMN title VARCHAR(255) NULL AFTER marital_status",
        "ALTER TABLE admin_users ADD COLUMN company_id BIGINT UNSIGNED NULL AFTER title",
        "ALTER TABLE admin_users ADD COLUMN user_type VARCHAR(255) DEFAULT 'patient' AFTER company_id",
        "ALTER TABLE admin_users ADD COLUMN patient_status VARCHAR(255) NULL AFTER user_type",
        "ALTER TABLE admin_users ADD COLUMN intro TEXT NULL AFTER avatar",
        "ALTER TABLE admin_users ADD COLUMN rate DECIMAL(8,2) DEFAULT 0 AFTER intro",
        "ALTER TABLE admin_users ADD COLUMN belongs_to_company VARCHAR(255) NULL AFTER rate",
        "ALTER TABLE admin_users ADD COLUMN card_status VARCHAR(255) DEFAULT 'inactive' AFTER belongs_to_company",
        "ALTER TABLE admin_users ADD COLUMN card_number VARCHAR(255) NULL AFTER card_status",
        "ALTER TABLE admin_users ADD COLUMN card_balance DECIMAL(15,2) DEFAULT 0 AFTER card_number",
        "ALTER TABLE admin_users ADD COLUMN card_accepts_credit BOOLEAN DEFAULT 0 AFTER card_balance",
        "ALTER TABLE admin_users ADD COLUMN card_max_credit DECIMAL(15,2) DEFAULT 0 AFTER card_accepts_credit",
        "ALTER TABLE admin_users ADD COLUMN card_accepts_cash BOOLEAN DEFAULT 1 AFTER card_max_credit",
        "ALTER TABLE admin_users ADD COLUMN is_dependent BOOLEAN DEFAULT 0 AFTER card_accepts_cash",
        "ALTER TABLE admin_users ADD COLUMN dependent_status VARCHAR(255) NULL AFTER is_dependent",
        "ALTER TABLE admin_users ADD COLUMN dependent_id BIGINT UNSIGNED NULL AFTER dependent_status",
        "ALTER TABLE admin_users ADD COLUMN card_expiry DATE NULL AFTER dependent_id",
        "ALTER TABLE admin_users ADD COLUMN belongs_to_company_status VARCHAR(255) NULL AFTER card_expiry",
        "ALTER TABLE admin_users ADD COLUMN medical_history TEXT NULL AFTER belongs_to_company_status",
        "ALTER TABLE admin_users ADD COLUMN allergies TEXT NULL AFTER medical_history",
        "ALTER TABLE admin_users ADD COLUMN current_medications TEXT NULL AFTER allergies",
        "ALTER TABLE admin_users ADD COLUMN blood_type VARCHAR(255) NULL AFTER current_medications",
        "ALTER TABLE admin_users ADD COLUMN height DECIMAL(5,2) NULL AFTER blood_type",
        "ALTER TABLE admin_users ADD COLUMN weight DECIMAL(5,2) NULL AFTER height",
        "ALTER TABLE admin_users ADD COLUMN insurance_provider VARCHAR(255) NULL AFTER weight",
        "ALTER TABLE admin_users ADD COLUMN insurance_policy_number VARCHAR(255) NULL AFTER insurance_provider",
        "ALTER TABLE admin_users ADD COLUMN insurance_expiry_date DATE NULL AFTER insurance_policy_number",
        "ALTER TABLE admin_users ADD COLUMN family_doctor_name VARCHAR(255) NULL AFTER insurance_expiry_date",
        "ALTER TABLE admin_users ADD COLUMN family_doctor_phone VARCHAR(255) NULL AFTER family_doctor_name",
        "ALTER TABLE admin_users ADD COLUMN employment_status VARCHAR(255) NULL AFTER family_doctor_phone",
        "ALTER TABLE admin_users ADD COLUMN employer_name VARCHAR(255) NULL AFTER employment_status",
        "ALTER TABLE admin_users ADD COLUMN annual_income DECIMAL(15,2) NULL AFTER employer_name",
        "ALTER TABLE admin_users ADD COLUMN education_level VARCHAR(255) NULL AFTER annual_income",
        "ALTER TABLE admin_users ADD COLUMN preferred_language VARCHAR(255) NULL AFTER education_level"
    ];
    
    foreach ($alterQueries as $query) {
        try {
            $pdo->exec($query);
            echo "✓ " . substr($query, 0, 80) . "...\n";
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate column name')) {
                echo "⚠ Column already exists: " . substr($query, 0, 50) . "...\n";
            } else {
                echo "✗ Error: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\nDropping users table...\n";
    try {
        $pdo->exec("DROP TABLE IF EXISTS users");
        echo "✓ Users table dropped successfully\n";
    } catch (Exception $e) {
        echo "✗ Error dropping users table: " . $e->getMessage() . "\n";
    }
    
    echo "\nDone! admin_users now has all the columns we need.\n";
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>
