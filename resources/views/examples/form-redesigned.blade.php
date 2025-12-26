{{-- 
================================================================================
EXAMPLE: Form Page with Redesigned Styles
================================================================================
This example shows how to create a modern form using the new design system
Location: resources/views/examples/form-redesigned.blade.php
--}}

@extends('admin::index')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin-redesign.css') }}">

<div class="content-wrapper" style="background-color: #f9fafb; padding: 24px;">
    
    {{-- Page Header --}}
    <div class="content-header" style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 28px; font-weight: 700; color: #1f2937; margin-bottom: 8px;">
                    Create New Patient
                </h1>
                <nav style="font-size: 13px; color: #6b7280;">
                    <a href="{{ admin_url('/') }}" style="color: #0ea5e9;">Dashboard</a>
                    <span style="margin: 0 8px; color: #d1d5db;">/</span>
                    <a href="{{ admin_url('patients') }}" style="color: #0ea5e9;">Patients</a>
                    <span style="margin: 0 8px; color: #d1d5db;">/</span>
                    <span style="color: #1f2937; font-weight: 600;">Create</span>
                </nav>
            </div>
            <a href="{{ admin_url('patients') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    {{-- Success/Error Messages --}}
    @if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <div>
            <strong>Success!</strong> {{ session('success') }}
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <div>
            <strong>Error!</strong>
            <ul style="margin: 4px 0 0 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- Main Form Card --}}
    <form action="{{ admin_url('patients') }}" method="POST">
        @csrf

        <div class="row">
            {{-- Left Column --}}
            <div class="col-md-8">
                
                {{-- Personal Information Card --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-user"></i> Personal Information
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            {{-- First Name --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="first_name" class="form-label">
                                        First Name <span style="color: #dc2626;">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control @error('first_name') is-invalid @enderror" 
                                        id="first_name" 
                                        name="first_name" 
                                        placeholder="Enter first name"
                                        value="{{ old('first_name') }}"
                                        required
                                    >
                                    @error('first_name')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Last Name --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="last_name" class="form-label">
                                        Last Name <span style="color: #dc2626;">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control @error('last_name') is-invalid @enderror" 
                                        id="last_name" 
                                        name="last_name" 
                                        placeholder="Enter last name"
                                        value="{{ old('last_name') }}"
                                        required
                                    >
                                    @error('last_name')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Email --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input 
                                        type="email" 
                                        class="form-control @error('email') is-invalid @enderror" 
                                        id="email" 
                                        name="email" 
                                        placeholder="patient@example.com"
                                        value="{{ old('email') }}"
                                    >
                                    @error('email')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Phone --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone" class="form-label">
                                        Phone Number <span style="color: #dc2626;">*</span>
                                    </label>
                                    <input 
                                        type="tel" 
                                        class="form-control @error('phone') is-invalid @enderror" 
                                        id="phone" 
                                        name="phone" 
                                        placeholder="+256 700 000 000"
                                        value="{{ old('phone') }}"
                                        required
                                    >
                                    @error('phone')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Date of Birth --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="dob" class="form-label">Date of Birth</label>
                                    <input 
                                        type="date" 
                                        class="form-control @error('dob') is-invalid @enderror" 
                                        id="dob" 
                                        name="dob" 
                                        value="{{ old('dob') }}"
                                    >
                                    @error('dob')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Gender --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select 
                                        class="form-select @error('gender') is-invalid @enderror" 
                                        id="gender" 
                                        name="gender"
                                    >
                                        <option value="">Select gender</option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('gender')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Medical Information Card --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-notes-medical"></i> Medical Information
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            {{-- Blood Group --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="blood_group" class="form-label">Blood Group</label>
                                    <select class="form-select" id="blood_group" name="blood_group">
                                        <option value="">Select blood group</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Allergies --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="allergies" class="form-label">Known Allergies</label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="allergies" 
                                        name="allergies" 
                                        placeholder="e.g., Penicillin, Peanuts"
                                        value="{{ old('allergies') }}"
                                    >
                                </div>
                            </div>
                        </div>

                        {{-- Medical History --}}
                        <div class="form-group">
                            <label for="medical_history" class="form-label">Medical History</label>
                            <textarea 
                                class="form-control" 
                                id="medical_history" 
                                name="medical_history" 
                                rows="4"
                                placeholder="Enter relevant medical history, chronic conditions, previous surgeries, etc."
                            >{{ old('medical_history') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Address Information Card --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-map-marker-alt"></i> Address Information
                        </h3>
                    </div>
                    <div class="card-body">
                        {{-- Address --}}
                        <div class="form-group">
                            <label for="address" class="form-label">Street Address</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="address" 
                                name="address" 
                                placeholder="Enter street address"
                                value="{{ old('address') }}"
                            >
                        </div>

                        <div class="row">
                            {{-- City --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="city" class="form-label">City</label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="city" 
                                        name="city" 
                                        placeholder="Enter city"
                                        value="{{ old('city') }}"
                                    >
                                </div>
                            </div>

                            {{-- District --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="district" class="form-label">District</label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="district" 
                                        name="district" 
                                        placeholder="Enter district"
                                        value="{{ old('district') }}"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column (Sidebar) --}}
            <div class="col-md-4">
                
                {{-- Status Card --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle"></i> Status
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="status" class="form-label">Patient Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="deceased">Deceased</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Registration Date</label>
                            <div style="padding: 10px; background-color: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb;">
                                <span style="font-size: 14px; color: #4b5563; font-weight: 500;">
                                    {{ date('F d, Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Emergency Contact Card --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-phone-alt"></i> Emergency Contact
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="emergency_name" class="form-label">Contact Name</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="emergency_name" 
                                name="emergency_name" 
                                placeholder="Full name"
                                value="{{ old('emergency_name') }}"
                            >
                        </div>

                        <div class="form-group">
                            <label for="emergency_phone" class="form-label">Contact Phone</label>
                            <input 
                                type="tel" 
                                class="form-control" 
                                id="emergency_phone" 
                                name="emergency_phone" 
                                placeholder="+256 700 000 000"
                                value="{{ old('emergency_phone') }}"
                            >
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="emergency_relationship" class="form-label">Relationship</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="emergency_relationship" 
                                name="emergency_relationship" 
                                placeholder="e.g., Spouse, Parent"
                                value="{{ old('emergency_relationship') }}"
                            >
                        </div>
                    </div>
                </div>

                {{-- Actions Card --}}
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-save"></i> Save Patient
                        </button>
                        
                        <button type="button" class="btn btn-secondary btn-lg btn-block" style="margin-top: 12px;" onclick="window.history.back()">
                            <i class="fas fa-times"></i> Cancel
                        </button>

                        <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #e5e7eb;">
                            <p style="font-size: 12px; color: #6b7280; margin: 0;">
                                <i class="fas fa-info-circle"></i> 
                                Fields marked with <span style="color: #dc2626;">*</span> are required
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection
