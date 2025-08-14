<?php
/**
 * Enhanced form field component with improved styling and validation
 * 
 * @param string $label - Field label
 * @param string $name - Field name attribute
 * @param string $type - Input type (text, email, password, select, textarea, etc.)
 * @param string $value - Default value
 * @param array $options - For select fields
 * @param bool $required - Whether field is required
 * @param string $placeholder - Placeholder text
 * @param string $help - Help text
 * @param string $icon - Icon class
 * @param array $attributes - Additional attributes
 */

$type = $type ?? 'text';
$value = $value ?? old($name);
$required = $required ?? false;
$placeholder = $placeholder ?? '';
$help = $help ?? '';
$icon = $icon ?? '';
$attributes = $attributes ?? [];
$options = $options ?? [];
$rows = $rows ?? 3;

$fieldId = 'field_' . $name . '_' . rand(1000, 9999);
$hasError = $errors->has($name);
$errorClass = $hasError ? 'is-invalid' : '';
?>

<div class="form-group mb-3">
    @if($label)
        <label for="{{ $fieldId }}" class="form-label fw-semibold">
            @if($icon)
                <i class="{{ $icon }} me-1"></i>
            @endif
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    <div class="input-group">
        @if($icon && $type !== 'select' && $type !== 'textarea')
            <span class="input-group-text">
                <i class="{{ $icon }}"></i>
            </span>
        @endif

        @if($type === 'select')
            <select 
                name="{{ $name }}" 
                id="{{ $fieldId }}" 
                class="form-select {{ $errorClass }}"
                @if($required) required @endif
                @foreach($attributes as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach
            >
                @if($placeholder)
                    <option value="">{{ $placeholder }}</option>
                @endif
                @foreach($options as $optValue => $optLabel)
                    <option value="{{ $optValue }}" {{ $value == $optValue ? 'selected' : '' }}>
                        {{ $optLabel }}
                    </option>
                @endforeach
            </select>

        @elseif($type === 'textarea')
            <textarea 
                name="{{ $name }}" 
                id="{{ $fieldId }}" 
                class="form-control {{ $errorClass }}"
                rows="{{ $rows }}"
                @if($placeholder) placeholder="{{ $placeholder }}" @endif
                @if($required) required @endif
                @foreach($attributes as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach
            >{{ $value }}</textarea>

        @elseif($type === 'checkbox')
            <div class="form-check">
                <input 
                    type="checkbox" 
                    name="{{ $name }}" 
                    id="{{ $fieldId }}" 
                    class="form-check-input {{ $errorClass }}"
                    value="1"
                    {{ $value ? 'checked' : '' }}
                    @if($required) required @endif
                    @foreach($attributes as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach
                >
                @if($label)
                    <label class="form-check-label" for="{{ $fieldId }}">
                        {{ $label }}
                    </label>
                @endif
            </div>

        @else
            <input 
                type="{{ $type }}" 
                name="{{ $name }}" 
                id="{{ $fieldId }}" 
                class="form-control {{ $errorClass }}"
                value="{{ $value }}"
                @if($placeholder) placeholder="{{ $placeholder }}" @endif
                @if($required) required @endif
                @foreach($attributes as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach
            >
        @endif
    </div>

    @if($help)
        <div class="form-text">
            <i class="fas fa-info-circle me-1"></i>
            {{ $help }}
        </div>
    @endif

    @if($hasError)
        <div class="invalid-feedback d-block">
            <i class="fas fa-exclamation-circle me-1"></i>
            {{ $errors->first($name) }}
        </div>
    @endif
</div>
