<?php

namespace Tests\Feature;

use App\Models\Enterprise;
use App\Models\User;
use App\Models\Consultation;
use App\Models\MedicalService;
use App\Models\Company;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class MobileApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $enterprise;
    protected $user;
    protected $authToken;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test enterprise
        $this->enterprise = Enterprise::factory()->create([
            'name' => 'Test Medical Center',
            'slug' => 'test-medical-center',
            'status' => 'active'
        ]);

        // Create test company for the enterprise
        $company = Company::factory()->create([
            'enterprise_id' => $this->enterprise->id,
            'name' => 'Test Company'
        ]);

        // Create test department
        $department = Department::factory()->create([
            'enterprise_id' => $this->enterprise->id,
            'name' => 'General Medicine'
        ]);

        // Create test user
        $this->user = User::factory()->create([
            'enterprise_id' => $this->enterprise->id,
            'company_id' => $company->id,
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'john.doe',
            'phone_number' => '+256701234567'
        ]);

        // Generate JWT token for authentication
        $this->authToken = JWTAuth::fromUser($this->user);
    }

    /** @test */
    public function test_mobile_login_with_valid_credentials()
    {
        $response = $this->postJson('/api/mobile/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'enterprise_id' => $this->enterprise->id
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'access_token',
                        'token_type',
                        'expires_in',
                        'user' => [
                            'id',
                            'first_name',
                            'last_name',
                            'email',
                            'enterprise_id'
                        ]
                    ]
                ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals('bearer', $response->json('data.token_type'));
    }

    /** @test */
    public function test_mobile_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/mobile/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
            'enterprise_id' => $this->enterprise->id
        ]);

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ]);
    }

    /** @test */
    public function test_mobile_login_with_invalid_enterprise()
    {
        $response = $this->postJson('/api/mobile/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'enterprise_id' => 99999
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['enterprise_id']);
    }

    /** @test */
    public function test_mobile_register_new_user()
    {
        $userData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '+256701234568',
            'enterprise_id' => $this->enterprise->id,
            'username' => 'jane.smith'
        ];

        $response = $this->postJson('/api/mobile/register', $userData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'access_token',
                        'token_type',
                        'expires_in',
                        'user'
                    ],
                    'message'
                ]);

        $this->assertTrue($response->json('success'));
        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'enterprise_id' => $this->enterprise->id
        ]);
    }

    /** @test */
    public function test_mobile_register_validation_errors()
    {
        $response = $this->postJson('/api/mobile/register', [
            'first_name' => '',
            'email' => 'invalid-email',
            'password' => '123', // too short
            'enterprise_id' => 99999 // invalid
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'first_name', 
                    'email', 
                    'password', 
                    'enterprise_id'
                ]);
    }

    /** @test */
    public function test_get_user_profile()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->getJson('/api/mobile/me');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'phone_number',
                        'enterprise_id',
                        'company'
                    ]
                ]);

        $this->assertEquals($this->user->id, $response->json('data.id'));
        $this->assertEquals($this->user->email, $response->json('data.email'));
    }

    /** @test */
    public function test_update_user_profile()
    {
        $updateData = [
            'first_name' => 'John Updated',
            'last_name' => 'Doe Updated',
            'phone_number' => '+256701234569'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->postJson('/api/mobile/update-profile', $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Profile updated successfully'
                ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'first_name' => 'John Updated',
            'last_name' => 'Doe Updated',
            'phone_number' => '+256701234569'
        ]);
    }

    /** @test */
    public function test_get_consultations()
    {
        // Create test consultations
        $consultation1 = Consultation::factory()->create([
            'enterprise_id' => $this->enterprise->id,
            'patient_id' => $this->user->id,
            'consultation_status' => 'completed'
        ]);

        $consultation2 = Consultation::factory()->create([
            'enterprise_id' => $this->enterprise->id,
            'patient_id' => $this->user->id,
            'consultation_status' => 'pending'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->getJson('/api/mobile/consultations');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'consultation_number',
                            'consultation_status',
                            'created_at',
                            'patient'
                        ]
                    ]
                ]);

        $this->assertTrue($response->json('success'));
        $this->assertCount(2, $response->json('data'));
    }

    /** @test */
    public function test_get_consultation_details()
    {
        $consultation = Consultation::factory()->create([
            'enterprise_id' => $this->enterprise->id,
            'patient_id' => $this->user->id,
            'consultation_status' => 'completed'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->getJson("/api/mobile/consultations/{$consultation->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'consultation_number',
                        'consultation_status',
                        'symptoms',
                        'diagnosis',
                        'patient',
                        'medical_services'
                    ]
                ]);

        $this->assertEquals($consultation->id, $response->json('data.id'));
    }

    /** @test */
    public function test_get_medical_services()
    {
        // Create consultation first
        $consultation = Consultation::factory()->create([
            'enterprise_id' => $this->enterprise->id,
            'patient_id' => $this->user->id
        ]);

        // Create medical services
        $service1 = MedicalService::factory()->create([
            'enterprise_id' => $this->enterprise->id,
            'consultation_id' => $consultation->id,
            'status' => 'completed'
        ]);

        $service2 = MedicalService::factory()->create([
            'enterprise_id' => $this->enterprise->id,
            'consultation_id' => $consultation->id,
            'status' => 'pending'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->getJson('/api/mobile/medical-services');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'status',
                            'created_at',
                            'consultation'
                        ]
                    ]
                ]);

        $this->assertTrue($response->json('success'));
        $this->assertCount(2, $response->json('data'));
    }

    /** @test */
    public function test_get_dashboard_data()
    {
        // Create test data
        $consultation = Consultation::factory()->create([
            'enterprise_id' => $this->enterprise->id,
            'patient_id' => $this->user->id
        ]);

        $medicalService = MedicalService::factory()->create([
            'enterprise_id' => $this->enterprise->id,
            'consultation_id' => $consultation->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->getJson('/api/mobile/dashboard');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'user',
                        'statistics' => [
                            'total_consultations',
                            'pending_consultations',
                            'total_medical_services',
                            'pending_medical_services'
                        ],
                        'recent_consultations',
                        'recent_medical_services'
                    ]
                ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals(1, $response->json('data.statistics.total_consultations'));
        $this->assertEquals(1, $response->json('data.statistics.total_medical_services'));
    }

    /** @test */
    public function test_unauthorized_access_returns_401()
    {
        $response = $this->getJson('/api/mobile/me');

        $response->assertStatus(401);
    }

    /** @test */
    public function test_cross_enterprise_data_isolation()
    {
        // Create another enterprise and user
        $otherEnterprise = Enterprise::factory()->create([
            'name' => 'Other Medical Center'
        ]);

        $otherCompany = Company::factory()->create([
            'enterprise_id' => $otherEnterprise->id
        ]);

        $otherUser = User::factory()->create([
            'enterprise_id' => $otherEnterprise->id,
            'company_id' => $otherCompany->id
        ]);

        // Create consultation for other user
        $otherConsultation = Consultation::factory()->create([
            'enterprise_id' => $otherEnterprise->id,
            'patient_id' => $otherUser->id
        ]);

        // Try to access other user's consultation
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->getJson("/api/mobile/consultations/{$otherConsultation->id}");

        $response->assertStatus(404); // Should not find the consultation from another enterprise
    }

    /** @test */
    public function test_api_rate_limiting()
    {
        // Make multiple rapid requests to test rate limiting
        for ($i = 0; $i < 10; $i++) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->authToken,
            ])->getJson('/api/mobile/me');
            
            if ($i < 5) {
                $response->assertStatus(200);
            }
        }
        
        // Note: Actual rate limiting behavior depends on configuration
        // This test structure allows for future rate limiting implementation
    }

    /** @test */
    public function test_jwt_token_expiration_handling()
    {
        // Create an expired token (simulate)
        $expiredToken = JWTAuth::fromUser($this->user);
        
        // In a real scenario, you'd manipulate the token to be expired
        // For now, we test with a valid token structure
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $expiredToken,
        ])->getJson('/api/mobile/me');

        $response->assertStatus(200); // Should work with valid token
    }

    protected function tearDown(): void
    {
        // Clean up any created test data
        parent::tearDown();
    }
}
