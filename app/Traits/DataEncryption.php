<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Encryption\DecryptException;

/**
 * DataEncryption Trait
 * 
 * Provides automatic encryption/decryption for sensitive model attributes
 * Supports field-level encryption for HIPAA compliance and data protection
 * 
 * Models using this trait should define:
 * - protected $encryptedFields = []; // Array of fields to encrypt
 * - protected $hashedFields = [];    // Array of fields to hash (optional)
 */
trait DataEncryption
{
    /**
     * Boot the trait
     */
    public static function bootDataEncryption()
    {
        // Encrypt data before saving
        static::saving(function ($model) {
            $model->encryptAttributes();
        });

        // Decrypt data after retrieving from database
        static::retrieved(function ($model) {
            $model->decryptAttributes();
        });
    }

    /**
     * Encrypt specified attributes before saving
     */
    protected function encryptAttributes(): void
    {
        foreach ($this->getEncryptedFields() as $field) {
            if (isset($this->attributes[$field]) && !$this->isFieldEncrypted($field)) {
                $this->attributes[$field] = $this->encryptField($this->attributes[$field]);
            }
        }

        foreach ($this->getHashedFields() as $field) {
            if (isset($this->attributes[$field]) && !$this->isFieldHashed($field)) {
                $this->attributes[$field] = $this->hashField($this->attributes[$field]);
            }
        }
    }

    /**
     * Decrypt specified attributes after retrieval
     */
    protected function decryptAttributes(): void
    {
        foreach ($this->getEncryptedFields() as $field) {
            if (isset($this->attributes[$field]) && $this->isFieldEncrypted($field)) {
                $this->attributes[$field] = $this->decryptField($this->attributes[$field]);
            }
        }
    }

    /**
     * Get list of encrypted fields for this model
     */
    protected function getEncryptedFields(): array
    {
        return property_exists($this, 'encryptedFields') ? $this->encryptedFields : [];
    }

    /**
     * Get list of hashed fields for this model
     */
    protected function getHashedFields(): array
    {
        return property_exists($this, 'hashedFields') ? $this->hashedFields : [];
    }

    /**
     * Encrypt a field value
     */
    protected function encryptField($value): string
    {
        if (empty($value)) {
            return $value;
        }

        try {
            return 'encrypted:' . Crypt::encrypt($value);
        } catch (\Exception $e) {
            Log::error('Encryption failed for field', [
                'model' => get_class($this),
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Failed to encrypt sensitive data');
        }
    }

    /**
     * Decrypt a field value
     */
    protected function decryptField($value): string
    {
        if (empty($value) || !$this->isFieldEncrypted('', $value)) {
            return $value;
        }

        try {
            // Remove the 'encrypted:' prefix and decrypt
            $encryptedValue = substr($value, 10);
            return Crypt::decrypt($encryptedValue);
        } catch (DecryptException $e) {
            Log::error('Decryption failed for field', [
                'model' => get_class($this),
                'error' => $e->getMessage()
            ]);
            return '[DECRYPTION_FAILED]';
        }
    }

    /**
     * Hash a field value (one-way)
     */
    protected function hashField($value): string
    {
        if (empty($value)) {
            return $value;
        }

        return 'hashed:' . Hash::make($value);
    }

    /**
     * Check if a field value is encrypted
     */
    protected function isFieldEncrypted(string $field, ?string $value = null): bool
    {
        $checkValue = $value ?? ($this->attributes[$field] ?? '');
        return strpos($checkValue, 'encrypted:') === 0;
    }

    /**
     * Check if a field value is hashed
     */
    protected function isFieldHashed(string $field, ?string $value = null): bool
    {
        $checkValue = $value ?? ($this->attributes[$field] ?? '');
        return strpos($checkValue, 'hashed:') === 0;
    }

    /**
     * Verify a hashed field value
     */
    public function verifyHashedField(string $field, string $value): bool
    {
        if (!in_array($field, $this->getHashedFields())) {
            return false;
        }

        $hashedValue = $this->attributes[$field] ?? '';
        
        if (!$this->isFieldHashed($field, $hashedValue)) {
            return false;
        }

        // Remove the 'hashed:' prefix and verify
        $hashToVerify = substr($hashedValue, 7);
        return Hash::check($value, $hashToVerify);
    }

    /**
     * Set an encrypted attribute value
     */
    public function setEncryptedAttribute(string $field, $value): void
    {
        if (in_array($field, $this->getEncryptedFields())) {
            $this->attributes[$field] = $this->encryptField($value);
        } else {
            $this->attributes[$field] = $value;
        }
    }

    /**
     * Get a decrypted attribute value
     */
    public function getEncryptedAttribute(string $field)
    {
        if (!in_array($field, $this->getEncryptedFields())) {
            return $this->attributes[$field] ?? null;
        }

        $value = $this->attributes[$field] ?? null;
        
        if ($this->isFieldEncrypted($field, $value)) {
            return $this->decryptField($value);
        }

        return $value;
    }

    /**
     * Search encrypted fields (limited functionality)
     * Note: Encrypted fields cannot be searched directly in database
     */
    public function searchEncryptedField(string $field, string $searchValue): bool
    {
        if (!in_array($field, $this->getEncryptedFields())) {
            return false;
        }

        $decryptedValue = $this->getEncryptedAttribute($field);
        return stripos($decryptedValue, $searchValue) !== false;
    }

    /**
     * Get all encrypted field values in decrypted form
     */
    public function getDecryptedAttributes(): array
    {
        $decrypted = [];
        
        foreach ($this->getEncryptedFields() as $field) {
            if (isset($this->attributes[$field])) {
                $decrypted[$field] = $this->getEncryptedAttribute($field);
            }
        }

        return $decrypted;
    }

    /**
     * Convert model to array with decrypted sensitive fields
     */
    public function toArrayWithDecryption(): array
    {
        $array = $this->toArray();
        
        foreach ($this->getEncryptedFields() as $field) {
            if (isset($array[$field])) {
                $array[$field] = $this->getEncryptedAttribute($field);
            }
        }

        return $array;
    }

    /**
     * Check if model has any encrypted fields
     */
    public function hasEncryptedFields(): bool
    {
        return !empty($this->getEncryptedFields());
    }

    /**
     * Check if model has any hashed fields
     */
    public function hasHashedFields(): bool
    {
        return !empty($this->getHashedFields());
    }

    /**
     * Get field encryption status
     */
    public function getFieldEncryptionStatus(): array
    {
        $status = [];
        
        foreach ($this->getEncryptedFields() as $field) {
            $status[$field] = [
                'type' => 'encrypted',
                'is_encrypted' => $this->isFieldEncrypted($field),
                'has_value' => !empty($this->attributes[$field] ?? null)
            ];
        }

        foreach ($this->getHashedFields() as $field) {
            $status[$field] = [
                'type' => 'hashed',
                'is_hashed' => $this->isFieldHashed($field),
                'has_value' => !empty($this->attributes[$field] ?? null)
            ];
        }

        return $status;
    }

    /**
     * Validate encryption integrity
     */
    public function validateEncryptionIntegrity(): array
    {
        $issues = [];

        foreach ($this->getEncryptedFields() as $field) {
            if (isset($this->attributes[$field]) && !empty($this->attributes[$field])) {
                if (!$this->isFieldEncrypted($field)) {
                    $issues[] = "Field '{$field}' should be encrypted but is not";
                } else {
                    // Try to decrypt to verify integrity
                    try {
                        $this->decryptField($this->attributes[$field]);
                    } catch (\Exception $e) {
                        $issues[] = "Field '{$field}' encryption is corrupted";
                    }
                }
            }
        }

        foreach ($this->getHashedFields() as $field) {
            if (isset($this->attributes[$field]) && !empty($this->attributes[$field])) {
                if (!$this->isFieldHashed($field)) {
                    $issues[] = "Field '{$field}' should be hashed but is not";
                }
            }
        }

        return $issues;
    }

    /**
     * Re-encrypt all encrypted fields (useful for key rotation)
     */
    public function reEncryptFields(): void
    {
        foreach ($this->getEncryptedFields() as $field) {
            if (isset($this->attributes[$field]) && $this->isFieldEncrypted($field)) {
                // Decrypt then re-encrypt
                $decryptedValue = $this->decryptField($this->attributes[$field]);
                $this->attributes[$field] = $this->encryptField($decryptedValue);
            }
        }
    }

    /**
     * Mask sensitive data for logging/display
     */
    public function getMaskedAttributes(?array $fields = null): array
    {
        $fieldsToMask = $fields ?? array_merge($this->getEncryptedFields(), $this->getHashedFields());
        $masked = $this->toArray();

        foreach ($fieldsToMask as $field) {
            if (isset($masked[$field]) && !empty($masked[$field])) {
                $value = $masked[$field];
                
                if (strlen($value) <= 4) {
                    $masked[$field] = str_repeat('*', strlen($value));
                } else {
                    $masked[$field] = substr($value, 0, 2) . str_repeat('*', strlen($value) - 4) . substr($value, -2);
                }
            }
        }

        return $masked;
    }
}
