<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use App\Models\Pais;
use App\Models\CustomerAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class AccountAddressesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that guest is redirected to login page.
     */
    public function test_guest_cannot_access_addresses(): void
    {
        $response = $this->get('/mi-cuenta/direcciones');
        $response->assertRedirect('/acceso');
    }

    /**
     * Test addresses page loads, shows form, handles geolocation resolution and validation.
     */
    public function test_user_can_manage_addresses_and_use_geolocation(): void
    {
        // 1. Setup mock data
        $user = User::factory()->create();
        $customer = Customer::create([
            'user_id' => $user->id,
            'nombre' => 'Theizer',
            'email' => $user->email,
            'empresa_id' => null,
            'sucursal_id' => null,
        ]);

        $pais = Pais::create([
            'nombre' => 'Venezuela',
            'codigo_iso2' => 'VE',
            'codigo_iso3' => 'VEN',
            'activo' => true,
        ]);

        // Mock Nominatim OpenStreetMap API response
        Http::fake([
            'https://nominatim.openstreetmap.org/reverse*' => Http::response([
                'display_name' => 'Avenida Principal, Caracas, Distrito Capital, 1010, VE',
                'address' => [
                    'road' => 'Avenida Principal',
                    'city' => 'Caracas',
                    'state' => 'Distrito Capital',
                    'postcode' => '1010',
                    'country_code' => 've',
                ]
            ], 200)
        ]);

        // 2. Test Volt addresses component
        $component = Livewire::actingAs($user)
            ->test('store.account.addresses')
            ->assertSet('showForm', false)
            ->call('openCreate')
            ->assertSet('showForm', true)
            ->assertSet('nombre_destinatario', '')
            ->assertSet('direccion', '')
            ->assertSet('ciudad', '')
            ->assertSet('pais_id', 0);

        // 3. Call geolocation method
        $component->call('setLocation', 10.4806, -66.9036)
            ->assertSet('direccion', 'Avenida Principal, Caracas, Distrito Capital, 1010, VE')
            ->assertSet('ciudad', 'Caracas')
            ->assertSet('estado_region', 'Distrito Capital')
            ->assertSet('codigo_postal', '1010')
            ->assertSet('pais_id', $pais->id);

        // 4. Fill destinatario and save
        $component->set('nombre_destinatario', 'Theizer Gonzalez')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showForm', false)
            ->assertSee('Dirección agregada correctamente.');

        // 5. Verify it is stored in database
        $this->assertDatabaseHas('customer_addresses', [
            'customer_id' => $customer->id,
            'nombre_destinatario' => 'Theizer Gonzalez',
            'direccion' => 'Avenida Principal, Caracas, Distrito Capital, 1010, VE',
            'ciudad' => 'Caracas',
            'pais_id' => $pais->id,
        ]);
    }
}
