<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Auth\Database\Role;
use Encore\Admin\Auth\Database\Permission;
use Illuminate\Support\Facades\Hash;

class CreateTestAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a test admin user for React app testing';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            // Check if admin user already exists
            $existingAdmin = Administrator::where('username', 'admin')->first();
            
            if ($existingAdmin) {
                $this->info('Test admin user already exists!');
                $this->info('Email: admin@example.com');
                $this->info('Password: password');
                return 0;
            }

            // Create admin user
            $admin = new Administrator();
            $admin->username = 'admin';
            $admin->password = Hash::make('password');
            $admin->name = 'Test Admin';
            $admin->email = 'admin@example.com';
            $admin->save();

            // Get Administrator role
            $role = Role::where('slug', 'administrator')->first();
            if ($role) {
                $admin->roles()->attach($role->id);
            }

            $this->info('Test admin user created successfully!');
            $this->info('Email: admin@example.com');
            $this->info('Password: password');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Error creating test admin: ' . $e->getMessage());
            return 1;
        }
    }
}
