<?php
// Direct database insertion for 50 comprehensive users
$host = 'localhost';
$dbname = 'hospital';
$username = 'root';
$password = 'root';
$socket = '/Applications/MAMP/tmp/mysql/mysql.sock';

try {
    $pdo = new PDO("mysql:unix_socket=$socket;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🚀 Creating 50 comprehensive users with all fields...\n\n";
    
    // User types for variety
    $userTypes = ['patient', 'doctor', 'nurse', 'administrator', 'pharmacist', 'technician'];
    $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
    $maritalStatuses = ['single', 'married', 'divorced', 'widowed', 'separated'];
    $cardStatuses = ['active', 'inactive', 'suspended', 'expired'];
    $employmentStatuses = ['employed', 'unemployed', 'retired', 'student', 'self-employed'];
    $educationLevels = ['Primary', 'Secondary', 'Certificate', 'Diploma', 'Bachelor', 'Master', 'PhD'];
    $religions = ['Christian', 'Muslim', 'Hindu', 'Buddhist', 'Jewish', 'Other'];
    $languages = ['English', 'Swahili', 'Luganda', 'French', 'Arabic'];
    $nationalities = ['Ugandan', 'Kenyan', 'Tanzanian', 'Rwandan', 'American', 'British', 'Canadian'];
    
    $firstNames = ['John', 'Mary', 'James', 'Sarah', 'David', 'Jennifer', 'Michael', 'Lisa', 'Robert', 'Karen', 'William', 'Nancy', 'Richard', 'Betty', 'Joseph', 'Helen', 'Thomas', 'Sandra', 'Charles', 'Donna', 'Daniel', 'Carol', 'Matthew', 'Ruth', 'Anthony', 'Maria', 'Mark', 'Laura', 'Donald', 'Julie', 'Steven', 'Christine', 'Paul', 'Samantha', 'Andrew', 'Deborah', 'Joshua', 'Rachel', 'Kenneth', 'Carolyn', 'Kevin', 'Janet', 'Brian', 'Catherine', 'George', 'Frances', 'Timothy', 'Martha', 'Ronald', 'Rebecca'];
    
    $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Perez', 'Thompson', 'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson', 'Walker', 'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen', 'Hill', 'Flores', 'Green', 'Adams', 'Nelson', 'Baker', 'Hall', 'Rivera', 'Campbell', 'Mitchell', 'Carter', 'Roberts'];
    
    // Insert statement
    $sql = "INSERT INTO admin_users (
        enterprise_id, name, email, email_verified_at, password, first_name, last_name, username,
        phone_number_1, phone_number_2, date_of_birth, place_of_birth, sex, home_address,
        current_address, nationality, religion, spouse_name, spouse_phone, father_name,
        father_phone, mother_name, mother_phone, languages, emergency_person_name,
        emergency_person_phone, emergency_contact_relationship, national_id_number, passport_number, tin, nssf_number,
        bank_name, bank_account_number, marital_status, title, company_id, user_type,
        patient_status, avatar, intro, rate, belongs_to_company, card_status, card_number,
        card_balance, card_accepts_credit, card_max_credit, card_accepts_cash, is_dependent,
        dependent_status, dependent_id, card_expiry, belongs_to_company_status, medical_history,
        allergies, current_medications, blood_type, height, weight, insurance_provider,
        insurance_policy_number, insurance_expiry_date, family_doctor_name, family_doctor_phone,
        employment_status, employer_name, annual_income, education_level, preferred_language,
        remember_token, created_at, updated_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    
    for ($i = 1; $i <= 50; $i++) {
        $firstName = $firstNames[array_rand($firstNames)];
        $lastName = $lastNames[array_rand($lastNames)];
        $email = strtolower($firstName . '.' . $lastName . $i . '@hospital.com');
        $phoneNumber = '+256' . rand(700000000, 799999999);
        $userType = $userTypes[array_rand($userTypes)];
        $sex = rand(0, 1) ? 'male' : 'female';
        $birthDate = date('Y-m-d', strtotime('-' . rand(18, 80) . ' years'));
        $cardNumber = 'HOS-' . rand(1000, 9999) . '-' . rand(1000, 9999);
        
        $userData = [
            1, // enterprise_id
            $firstName . ' ' . $lastName, // name
            $email, // email
            date('Y-m-d H:i:s'), // email_verified_at
            password_hash('password123', PASSWORD_DEFAULT), // password
            $firstName, // first_name
            $lastName, // last_name
            $email, // username
            $phoneNumber, // phone_number_1
            rand(0, 1) ? '+256' . rand(700000000, 799999999) : null, // phone_number_2
            $birthDate, // date_of_birth
            'Kampala, Uganda', // place_of_birth
            $sex, // sex
            rand(1, 999) . ' ' . ['Kampala Road', 'Bombo Road', 'Entebbe Road', 'Jinja Road'][array_rand(['Kampala Road', 'Bombo Road', 'Entebbe Road', 'Jinja Road'])], // home_address
            rand(1, 999) . ' ' . ['Plot ', 'House '][array_rand(['Plot ', 'House '])] . rand(1, 999), // current_address
            $nationalities[array_rand($nationalities)], // nationality
            $religions[array_rand($religions)], // religion
            rand(0, 1) ? $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)] : null, // spouse_name
            rand(0, 1) ? '+256' . rand(700000000, 799999999) : null, // spouse_phone
            $firstNames[array_rand($firstNames)] . ' ' . $lastName, // father_name
            '+256' . rand(700000000, 799999999), // father_phone
            $firstNames[array_rand($firstNames)] . ' ' . $lastName, // mother_name
            '+256' . rand(700000000, 799999999), // mother_phone
            $languages[array_rand($languages)] . ', ' . $languages[array_rand($languages)], // languages
            $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)], // emergency_person_name
            '+256' . rand(700000000, 799999999), // emergency_person_phone
            ['spouse', 'parent', 'sibling', 'friend'][array_rand(['spouse', 'parent', 'sibling', 'friend'])], // emergency_contact_relationship
            'NIN' . rand(1000000000, 9999999999), // national_id_number
            rand(0, 1) ? 'PP' . rand(100000, 999999) : null, // passport_number
            rand(0, 1) ? rand(1000000000, 9999999999) : null, // tin
            rand(0, 1) ? 'NSSF' . rand(1000000, 9999999) : null, // nssf_number
            rand(0, 1) ? ['Stanbic Bank', 'Centenary Bank', 'DFCU Bank'][array_rand(['Stanbic Bank', 'Centenary Bank', 'DFCU Bank'])] : null, // bank_name
            rand(0, 1) ? rand(1000000000, 9999999999) : null, // bank_account_number
            $maritalStatuses[array_rand($maritalStatuses)], // marital_status
            ($userType === 'doctor' ? 'Dr.' : ($sex === 'male' ? 'Mr.' : 'Ms.')), // title
            rand(0, 1) ? rand(1, 5) : null, // company_id
            $userType, // user_type
            $userType === 'patient' ? ['active', 'inactive'][array_rand(['active', 'inactive'])] : null, // patient_status
            null, // avatar
            'Professional ' . $userType . ' with extensive experience.', // intro
            $userType === 'doctor' ? rand(50, 500) : 0, // rate
            rand(0, 1) ? 'Company ' . rand(1, 10) : null, // belongs_to_company
            $cardStatuses[array_rand($cardStatuses)], // card_status
            $cardNumber, // card_number
            rand(0, 10000), // card_balance
            rand(0, 1), // card_accepts_credit
            rand(500, 5000), // card_max_credit
            1, // card_accepts_cash
            rand(0, 1) ? 1 : 0, // is_dependent
            rand(0, 1) ? ['child', 'spouse'][array_rand(['child', 'spouse'])] : null, // dependent_status
            rand(0, 1) ? rand(1, 10) : null, // dependent_id
            date('Y-m-d', strtotime('+' . rand(1, 5) . ' years')), // card_expiry
            rand(0, 1) ? 'active' : null, // belongs_to_company_status
            'No significant medical history reported.', // medical_history
            rand(0, 1) ? ['Penicillin', 'Peanuts'][array_rand(['Penicillin', 'Peanuts'])] : null, // allergies
            rand(0, 1) ? 'Daily vitamins and supplements' : null, // current_medications
            $bloodTypes[array_rand($bloodTypes)], // blood_type
            rand(150, 200), // height
            rand(45, 120), // weight
            rand(0, 1) ? ['AAR Healthcare', 'NHIF'][array_rand(['AAR Healthcare', 'NHIF'])] : null, // insurance_provider
            rand(0, 1) ? 'INS' . rand(1000000, 9999999) : null, // insurance_policy_number
            rand(0, 1) ? date('Y-m-d', strtotime('+2 years')) : null, // insurance_expiry_date
            rand(0, 1) ? 'Dr. ' . $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)] : null, // family_doctor_name
            rand(0, 1) ? '+256' . rand(700000000, 799999999) : null, // family_doctor_phone
            $employmentStatuses[array_rand($employmentStatuses)], // employment_status
            rand(0, 1) ? 'Company ' . rand(1, 100) : null, // employer_name
            rand(1200000, 50000000), // annual_income
            $educationLevels[array_rand($educationLevels)], // education_level
            $languages[array_rand($languages)], // preferred_language
            null, // remember_token
            date('Y-m-d H:i:s', strtotime('-' . rand(1, 365) . ' days')), // created_at
            date('Y-m-d H:i:s'), // updated_at
        ];
        
        $stmt->execute($userData);
        
        if ($i % 10 == 0) {
            echo "✓ Created $i users...\n";
        }
    }
    
    echo "\n🎉 Successfully created 50 comprehensive users!\n";
    
    // Show user type distribution
    $stmt = $pdo->query("SELECT user_type, COUNT(*) as count FROM admin_users GROUP BY user_type");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n📊 User type distribution:\n";
    foreach ($results as $result) {
        echo "  - {$result['user_type']}: {$result['count']} users\n";
    }
    
    // Show total count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM admin_users");
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "\n📈 Total users in database: {$total['total']}\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
