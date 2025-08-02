<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the security configuration options for the hospital
    | management system. These settings control various security features
    | including authentication, authorization, audit logging, and data protection.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Authentication Security
    |--------------------------------------------------------------------------
    */
    'authentication' => [
        'max_login_attempts' => env('SECURITY_MAX_LOGIN_ATTEMPTS', 5),
        'lockout_duration' => env('SECURITY_LOCKOUT_DURATION', 15), // minutes
        'session_timeout' => env('SECURITY_SESSION_TIMEOUT', 120), // minutes
        'max_concurrent_sessions' => env('SECURITY_MAX_CONCURRENT_SESSIONS', 3),
        'check_ip_consistency' => env('SECURITY_CHECK_IP_CONSISTENCY', false),
        'check_user_agent_consistency' => env('SECURITY_CHECK_USER_AGENT_CONSISTENCY', false),
        'require_2fa_for_admins' => env('SECURITY_REQUIRE_2FA_ADMINS', true),
        'password_reset_timeout' => env('SECURITY_PASSWORD_RESET_TIMEOUT', 60), // minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limiting' => [
        'max_requests_per_minute' => env('SECURITY_MAX_REQUESTS_PER_MINUTE', 60),
        'max_login_attempts_per_minute' => env('SECURITY_MAX_LOGIN_ATTEMPTS_PER_MINUTE', 5),
        'max_api_requests_per_minute' => env('SECURITY_MAX_API_REQUESTS_PER_MINUTE', 100),
        'max_password_reset_attempts' => env('SECURITY_MAX_PASSWORD_RESET_ATTEMPTS', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Logging
    |--------------------------------------------------------------------------
    */
    'audit_logging' => [
        'enabled' => env('SECURITY_AUDIT_LOGGING_ENABLED', true),
        'log_all_requests' => env('SECURITY_LOG_ALL_REQUESTS', false),
        'log_sensitive_operations' => env('SECURITY_LOG_SENSITIVE_OPERATIONS', true),
        'log_authentication_events' => env('SECURITY_LOG_AUTH_EVENTS', true),
        'log_data_changes' => env('SECURITY_LOG_DATA_CHANGES', true),
        'log_file_uploads' => env('SECURITY_LOG_FILE_UPLOADS', true),
        'retention_days' => env('SECURITY_AUDIT_RETENTION_DAYS', 365),
        'compress_old_logs' => env('SECURITY_COMPRESS_OLD_LOGS', true),
        'log_channels' => [
            'default' => 'audit',
            'security' => 'security',
            'performance' => 'performance',
            'error' => 'error',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Encryption
    |--------------------------------------------------------------------------
    */
    'encryption' => [
        'enabled' => env('SECURITY_ENCRYPTION_ENABLED', true),
        'algorithm' => env('SECURITY_ENCRYPTION_ALGORITHM', 'AES-256-CBC'),
        'key_rotation_days' => env('SECURITY_KEY_ROTATION_DAYS', 90),
        'backup_encrypted_data' => env('SECURITY_BACKUP_ENCRYPTED_DATA', true),
        'validate_encryption_integrity' => env('SECURITY_VALIDATE_ENCRYPTION_INTEGRITY', true),
        'sensitive_fields' => [
            'medical_history',
            'allergies',
            'current_medications',
            'insurance_policy_number',
            'social_security_number',
            'bank_account_number',
            'credit_card_number',
            'home_address',
            'phone_numbers',
            'emergency_contacts',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Access Control
    |--------------------------------------------------------------------------
    */
    'access_control' => [
        'enterprise_isolation' => env('SECURITY_ENTERPRISE_ISOLATION', true),
        'role_based_access' => env('SECURITY_ROLE_BASED_ACCESS', true),
        'permission_inheritance' => env('SECURITY_PERMISSION_INHERITANCE', true),
        'admin_whitelist_ips' => env('SECURITY_ADMIN_WHITELIST_IPS', ''),
        'api_whitelist_ips' => env('SECURITY_API_WHITELIST_IPS', ''),
        'block_tor_access' => env('SECURITY_BLOCK_TOR_ACCESS', true),
        'block_vpn_access' => env('SECURITY_BLOCK_VPN_ACCESS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Validation
    |--------------------------------------------------------------------------
    */
    'validation' => [
        'strict_input_validation' => env('SECURITY_STRICT_INPUT_VALIDATION', true),
        'sanitize_inputs' => env('SECURITY_SANITIZE_INPUTS', true),
        'validate_file_uploads' => env('SECURITY_VALIDATE_FILE_UPLOADS', true),
        'max_file_size' => env('SECURITY_MAX_FILE_SIZE', 10240), // KB
        'allowed_file_types' => [
            'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'documents' => ['pdf', 'doc', 'docx', 'txt'],
            'medical' => ['dcm', 'dicom'],
        ],
        'scan_uploaded_files' => env('SECURITY_SCAN_UPLOADED_FILES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    */
    'headers' => [
        'content_security_policy' => env('SECURITY_CSP_ENABLED', true),
        'hsts_enabled' => env('SECURITY_HSTS_ENABLED', true),
        'hsts_max_age' => env('SECURITY_HSTS_MAX_AGE', 31536000), // 1 year
        'frame_options' => env('SECURITY_FRAME_OPTIONS', 'DENY'),
        'content_type_options' => env('SECURITY_CONTENT_TYPE_OPTIONS', 'nosniff'),
        'xss_protection' => env('SECURITY_XSS_PROTECTION', '1; mode=block'),
        'referrer_policy' => env('SECURITY_REFERRER_POLICY', 'strict-origin-when-cross-origin'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring and Alerting
    |--------------------------------------------------------------------------
    */
    'monitoring' => [
        'suspicious_activity_detection' => env('SECURITY_SUSPICIOUS_ACTIVITY_DETECTION', true),
        'failed_login_threshold' => env('SECURITY_FAILED_LOGIN_THRESHOLD', 3),
        'rapid_request_threshold' => env('SECURITY_RAPID_REQUEST_THRESHOLD', 30),
        'unusual_access_time_detection' => env('SECURITY_UNUSUAL_ACCESS_TIME_DETECTION', true),
        'geographic_anomaly_detection' => env('SECURITY_GEOGRAPHIC_ANOMALY_DETECTION', false),
        'alert_security_team' => env('SECURITY_ALERT_SECURITY_TEAM', true),
        'alert_channels' => [
            'email' => env('SECURITY_ALERT_EMAIL', ''),
            'slack' => env('SECURITY_ALERT_SLACK_WEBHOOK', ''),
            'sms' => env('SECURITY_ALERT_SMS_ENABLED', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Compliance Settings
    |--------------------------------------------------------------------------
    */
    'compliance' => [
        'hipaa_compliance' => env('SECURITY_HIPAA_COMPLIANCE', true),
        'gdpr_compliance' => env('SECURITY_GDPR_COMPLIANCE', true),
        'sox_compliance' => env('SECURITY_SOX_COMPLIANCE', false),
        'data_retention_policy' => env('SECURITY_DATA_RETENTION_POLICY', true),
        'right_to_erasure' => env('SECURITY_RIGHT_TO_ERASURE', true),
        'data_portability' => env('SECURITY_DATA_PORTABILITY', true),
        'consent_management' => env('SECURITY_CONSENT_MANAGEMENT', true),
        'breach_notification' => env('SECURITY_BREACH_NOTIFICATION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup and Recovery
    |--------------------------------------------------------------------------
    */
    'backup' => [
        'encrypt_backups' => env('SECURITY_ENCRYPT_BACKUPS', true),
        'backup_verification' => env('SECURITY_BACKUP_VERIFICATION', true),
        'offsite_backup' => env('SECURITY_OFFSITE_BACKUP', true),
        'backup_retention_days' => env('SECURITY_BACKUP_RETENTION_DAYS', 90),
        'disaster_recovery_plan' => env('SECURITY_DISASTER_RECOVERY_PLAN', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Development and Testing
    |--------------------------------------------------------------------------
    */
    'development' => [
        'disable_security_in_testing' => env('SECURITY_DISABLE_IN_TESTING', false),
        'log_debug_info' => env('SECURITY_LOG_DEBUG_INFO', false),
        'allow_test_users' => env('SECURITY_ALLOW_TEST_USERS', false),
        'mock_external_services' => env('SECURITY_MOCK_EXTERNAL_SERVICES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | External Integrations
    |--------------------------------------------------------------------------
    */
    'integrations' => [
        'virus_scanning' => [
            'enabled' => env('SECURITY_VIRUS_SCANNING_ENABLED', false),
            'service' => env('SECURITY_VIRUS_SCANNING_SERVICE', 'clamav'),
            'api_key' => env('SECURITY_VIRUS_SCANNING_API_KEY', ''),
        ],
        'ip_geolocation' => [
            'enabled' => env('SECURITY_IP_GEOLOCATION_ENABLED', false),
            'service' => env('SECURITY_IP_GEOLOCATION_SERVICE', 'maxmind'),
            'api_key' => env('SECURITY_IP_GEOLOCATION_API_KEY', ''),
        ],
        'threat_intelligence' => [
            'enabled' => env('SECURITY_THREAT_INTELLIGENCE_ENABLED', false),
            'service' => env('SECURITY_THREAT_INTELLIGENCE_SERVICE', ''),
            'api_key' => env('SECURITY_THREAT_INTELLIGENCE_API_KEY', ''),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Policy
    |--------------------------------------------------------------------------
    */
    'password_policy' => [
        'min_length' => env('SECURITY_PASSWORD_MIN_LENGTH', 8),
        'require_uppercase' => env('SECURITY_PASSWORD_REQUIRE_UPPERCASE', true),
        'require_lowercase' => env('SECURITY_PASSWORD_REQUIRE_LOWERCASE', true),
        'require_numbers' => env('SECURITY_PASSWORD_REQUIRE_NUMBERS', true),
        'require_symbols' => env('SECURITY_PASSWORD_REQUIRE_SYMBOLS', true),
        'prevent_common_passwords' => env('SECURITY_PASSWORD_PREVENT_COMMON', true),
        'prevent_personal_info' => env('SECURITY_PASSWORD_PREVENT_PERSONAL_INFO', true),
        'password_history' => env('SECURITY_PASSWORD_HISTORY', 5),
        'expiry_days' => env('SECURITY_PASSWORD_EXPIRY_DAYS', 90),
        'warning_days' => env('SECURITY_PASSWORD_WARNING_DAYS', 7),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Security
    |--------------------------------------------------------------------------
    */
    'api' => [
        'rate_limiting' => env('SECURITY_API_RATE_LIMITING', true),
        'require_authentication' => env('SECURITY_API_REQUIRE_AUTH', true),
        'token_expiry_hours' => env('SECURITY_API_TOKEN_EXPIRY_HOURS', 24),
        'cors_enabled' => env('SECURITY_API_CORS_ENABLED', true),
        'allowed_origins' => env('SECURITY_API_ALLOWED_ORIGINS', ''),
        'api_versioning' => env('SECURITY_API_VERSIONING', true),
        'deprecation_warnings' => env('SECURITY_API_DEPRECATION_WARNINGS', true),
    ],

];
