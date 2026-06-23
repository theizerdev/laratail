<?php

namespace Tests\Feature;

use App\Livewire\Admin\Profile\Index as ProfileIndex;
use App\Livewire\Auth\Login;
use App\Models\User;
use App\Services\TwoFactorService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileAndLogin2FATest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed Spatie roles/permissions so role checks work
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
    }

    /**
     * Test guest cannot access profile.
     */
    public function test_guest_is_redirected_from_profile(): void
    {
        $response = $this->get(route('admin.profile'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test admin user can access profile.
     */
    public function test_admin_can_access_profile(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $response = $this->actingAs($user)->get(route('admin.profile'));
        $response->assertOk();
    }

    /**
     * Test updating profile info via Livewire.
     */
    public function test_can_update_profile_info(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'telefono' => '123456',
        ]);
        $user->assignRole('super-admin');

        Livewire::actingAs($user)
            ->test(ProfileIndex::class)
            ->set('name', 'Updated Name')
            ->set('email', 'updated@example.com')
            ->set('telefono', '987654')
            ->call('updateProfile')
            ->assertHasNoErrors()
            ->assertSee('Tu perfil ha sido actualizado correctamente.');

        $user->refresh();
        $this->assertEquals('Updated Name', $user->name);
        $this->assertEquals('updated@example.com', $user->email);
        $this->assertEquals('987654', $user->telefono);
    }

    /**
     * Test changing password via Livewire.
     */
    public function test_can_change_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);
        $user->assignRole('super-admin');

        Livewire::actingAs($user)
            ->test(ProfileIndex::class)
            ->set('current_password', 'old-password')
            ->set('new_password', 'new-secure-password')
            ->set('new_password_confirmation', 'new-secure-password')
            ->call('updatePassword')
            ->assertHasNoErrors()
            ->assertSee('Tu contraseña ha sido cambiada correctamente.');

        $user->refresh();
        $this->assertTrue(Hash::check('new-secure-password', $user->password));
    }

    /**
     * Test 2FA configuration, code verification, and recovery codes generation.
     */
    public function test_can_enable_two_factor_auth(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $component = Livewire::actingAs($user)
            ->test(ProfileIndex::class)
            ->call('initTwoFactor')
            ->assertSet('isConfiguring2FA', true);

        $tempSecret = $component->get('tempSecret');
        $this->assertNotEmpty($tempSecret);

        // Calculate valid TOTP code
        $timeSlice = (int) floor(time() / 30);
        $reflector = new \ReflectionClass(TwoFactorService::class);
        $method = $reflector->getMethod('calculateCode');
        $method->setAccessible(true);
        $validCode = $method->invoke(null, $tempSecret, $timeSlice);

        // Attempt verification with invalid code first
        $component->set('twoFactorCode', '000000')
            ->call('confirmTwoFactor')
            ->assertHasErrors(['twoFactorCode']);

        $user->refresh();
        $this->assertNull($user->two_factor_confirmed_at);

        // Verify with valid code
        $component->set('twoFactorCode', $validCode)
            ->call('confirmTwoFactor')
            ->assertHasNoErrors()
            ->assertSee('La autenticación de dos pasos (2FA) ha sido habilitada exitosamente.');

        $user->refresh();
        $this->assertNotNull($user->two_factor_confirmed_at);
        $this->assertEquals($tempSecret, Crypt::decryptString($user->two_factor_secret));
        
        $recoveryCodes = json_decode(Crypt::decryptString($user->two_factor_recovery_codes), true);
        $this->assertCount(8, $recoveryCodes);
    }

    /**
     * Test 2FA login flow.
     */
    public function test_login_flow_requires_2fa_and_succeeds_with_correct_code(): void
    {
        $secret = TwoFactorService::generateSecret();
        $recoveryCodes = TwoFactorService::generateRecoveryCodes(8);

        $user = User::factory()->create([
            'email' => 'mfa-user@example.com',
            'password' => Hash::make('secret-password'),
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($recoveryCodes)),
            'two_factor_confirmed_at' => now(),
        ]);
        $user->assignRole('super-admin');

        // Step 1: Submit credentials
        $loginComponent = Livewire::test(Login::class)
            ->set('email', 'mfa-user@example.com')
            ->set('password', 'secret-password')
            ->call('login')
            ->assertSet('show2FA', true); // Prompts for 2FA

        $this->assertFalse(auth()->check());

        // Step 2: Calculate valid TOTP code
        $timeSlice = (int) floor(time() / 30);
        $reflector = new \ReflectionClass(TwoFactorService::class);
        $method = $reflector->getMethod('calculateCode');
        $method->setAccessible(true);
        $validCode = $method->invoke(null, $secret, $timeSlice);

        // Step 3: Enter invalid code
        $loginComponent->set('twoFactorCode', '000000')
            ->call('verify2FA')
            ->assertHasErrors(['twoFactorCode']);

        $this->assertFalse(auth()->check());

        // Step 4: Enter valid code
        $loginComponent->set('twoFactorCode', $validCode)
            ->call('verify2FA')
            ->assertHasNoErrors();

        $this->assertTrue(auth()->check());
        $this->assertEquals($user->id, auth()->id());
    }

    /**
     * Test 2FA login recovery code flow.
     */
    public function test_login_flow_succeeds_with_recovery_code_and_consumes_it(): void
    {
        $secret = TwoFactorService::generateSecret();
        $recoveryCodes = TwoFactorService::generateRecoveryCodes(8);
        $recoveryCodeToUse = $recoveryCodes[0];

        $user = User::factory()->create([
            'email' => 'mfa-recovery@example.com',
            'password' => Hash::make('secret-password'),
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($recoveryCodes)),
            'two_factor_confirmed_at' => now(),
        ]);
        $user->assignRole('super-admin');

        // Step 1: Submit credentials
        $loginComponent = Livewire::test(Login::class)
            ->set('email', 'mfa-recovery@example.com')
            ->set('password', 'secret-password')
            ->call('login')
            ->assertSet('show2FA', true);

        // Step 2: Enter recovery code
        $loginComponent->set('twoFactorCode', $recoveryCodeToUse)
            ->call('verify2FA')
            ->assertHasNoErrors();

        $this->assertTrue(auth()->check());
        $this->assertEquals($user->id, auth()->id());

        // Check recovery code is consumed
        $user->refresh();
        $updatedCodes = json_decode(Crypt::decryptString($user->two_factor_recovery_codes), true);
        $this->assertCount(7, $updatedCodes);
        $this->assertNotContains($recoveryCodeToUse, $updatedCodes);
    }
}
