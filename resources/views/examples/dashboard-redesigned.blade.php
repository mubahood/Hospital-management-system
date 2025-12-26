{{-- 
================================================================================
EXAMPLE: Dashboard Page with Redesigned Styles
================================================================================
This example shows how to apply the new design system to a typical dashboard
Location: resources/views/admin/dashboard.blade.php
--}}

@extends('admin::index')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin-redesign.css') }}">

<div class="content-wrapper" style="background-color: #f9fafb; padding: 24px;">
    
    {{-- Page Header --}}
    <div class="content-header" style="margin-bottom: 24px;">
        <h1 style="font-size: 28px; font-weight: 700; color: #1f2937; margin-bottom: 8px;">
            Dashboard
        </h1>
        <p style="font-size: 14px; color: #6b7280; margin: 0;">
            Welcome back, {{ Admin::user()->name }}! Here's your system overview.
        </p>
    </div>

    {{-- Statistics Cards Row --}}
    <div class="row" style="margin-bottom: 24px;">
        
        {{-- Total Patients Card --}}
        <div class="col-md-3">
            <div class="card stat-card success">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <div class="stat-value">1,234</div>
                        <div class="stat-label">Total Patients</div>
                    </div>
                    <div style="width: 48px; height: 48px; background-color: #d1fae5; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-users" style="font-size: 24px; color: #10b981;"></i>
                    </div>
                </div>
                <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e5e7eb;">
                    <span style="font-size: 12px; color: #10b981; font-weight: 600;">
                        <i class="fas fa-arrow-up"></i> 12.5%
                    </span>
                    <span style="font-size: 12px; color: #6b7280; margin-left: 8px;">vs last month</span>
                </div>
            </div>
        </div>

        {{-- Active Consultations Card --}}
        <div class="col-md-3">
            <div class="card stat-card" style="border-left-color: #0ea5e9 !important;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <div class="stat-value">89</div>
                        <div class="stat-label">Active Consultations</div>
                    </div>
                    <div style="width: 48px; height: 48px; background-color: #dbeafe; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-stethoscope" style="font-size: 24px; color: #0ea5e9;"></i>
                    </div>
                </div>
                <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e5e7eb;">
                    <span style="font-size: 12px; color: #0ea5e9; font-weight: 600;">
                        <i class="fas fa-arrow-up"></i> 8.3%
                    </span>
                    <span style="font-size: 12px; color: #6b7280; margin-left: 8px;">vs last month</span>
                </div>
            </div>
        </div>

        {{-- Pending Appointments Card --}}
        <div class="col-md-3">
            <div class="card stat-card warning">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <div class="stat-value">45</div>
                        <div class="stat-label">Pending Appointments</div>
                    </div>
                    <div style="width: 48px; height: 48px; background-color: #fef3c7; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-calendar-check" style="font-size: 24px; color: #f59e0b;"></i>
                    </div>
                </div>
                <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e5e7eb;">
                    <span style="font-size: 12px; color: #f59e0b; font-weight: 600;">
                        <i class="fas fa-minus"></i> 2.1%
                    </span>
                    <span style="font-size: 12px; color: #6b7280; margin-left: 8px;">vs last month</span>
                </div>
            </div>
        </div>

        {{-- Revenue Card --}}
        <div class="col-md-3">
            <div class="card stat-card" style="border-left-color: #10b981 !important;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <div class="stat-value" style="font-size: 24px;">UGX 4.5M</div>
                        <div class="stat-label">Monthly Revenue</div>
                    </div>
                    <div style="width: 48px; height: 48px; background-color: #d1fae5; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-dollar-sign" style="font-size: 24px; color: #10b981;"></i>
                    </div>
                </div>
                <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e5e7eb;">
                    <span style="font-size: 12px; color: #10b981; font-weight: 600;">
                        <i class="fas fa-arrow-up"></i> 18.7%
                    </span>
                    <span style="font-size: 12px; color: #6b7280; margin-left: 8px;">vs last month</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Row --}}
    <div class="row">
        
        {{-- Recent Patients Table --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 class="card-title">Recent Patients</h3>
                        <a href="{{ admin_url('patients') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i> View All
                        </a>
                    </div>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="table-wrapper" style="border: none; border-radius: 0;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Patient Name</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Last Visit</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <div style="width: 40px; height: 40px; background-color: #dbeafe; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-user" style="color: #0ea5e9;"></i>
                                            </div>
                                            <div>
                                                <div style="font-weight: 600; color: #1f2937; font-size: 13px;">John Doe</div>
                                                <div style="font-size: 11px; color: #6b7280;">ID: #12345</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="color: #4b5563;">+256 700 123 456</td>
                                    <td><span class="badge badge-success">Active</span></td>
                                    <td style="color: #6b7280; font-size: 12px;">2 days ago</td>
                                    <td>
                                        <div class="table-actions">
                                            <button class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-secondary">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <div style="width: 40px; height: 40px; background-color: #fef3c7; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-user" style="color: #f59e0b;"></i>
                                            </div>
                                            <div>
                                                <div style="font-weight: 600; color: #1f2937; font-size: 13px;">Jane Smith</div>
                                                <div style="font-size: 11px; color: #6b7280;">ID: #12346</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="color: #4b5563;">+256 700 123 457</td>
                                    <td><span class="badge badge-warning">Pending</span></td>
                                    <td style="color: #6b7280; font-size: 12px;">5 days ago</td>
                                    <td>
                                        <div class="table-actions">
                                            <button class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-secondary">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body">
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        
                        <a href="{{ admin_url('patients/create') }}" class="btn btn-primary btn-block" style="justify-content: flex-start;">
                            <i class="fas fa-user-plus"></i>
                            <span>Add New Patient</span>
                        </a>
                        
                        <a href="{{ admin_url('consultations/create') }}" class="btn btn-success btn-block" style="justify-content: flex-start;">
                            <i class="fas fa-stethoscope"></i>
                            <span>New Consultation</span>
                        </a>
                        
                        <a href="{{ admin_url('appointments/create') }}" class="btn btn-warning btn-block" style="justify-content: flex-start;">
                            <i class="fas fa-calendar-plus"></i>
                            <span>Schedule Appointment</span>
                        </a>
                        
                        <a href="{{ admin_url('medical-services/create') }}" class="btn btn-secondary btn-block" style="justify-content: flex-start;">
                            <i class="fas fa-medkit"></i>
                            <span>Medical Service</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Alerts Card --}}
            <div class="card" style="margin-top: 20px;">
                <div class="card-header">
                    <h3 class="card-title">System Alerts</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning" style="margin-bottom: 12px;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>
                            <strong>Low Stock!</strong>
                            <p style="margin: 4px 0 0 0; font-size: 12px;">5 items need restocking</p>
                        </div>
                    </div>
                    
                    <div class="alert alert-info" style="margin-bottom: 0;">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>Reminder</strong>
                            <p style="margin: 4px 0 0 0; font-size: 12px;">45 pending appointments</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
