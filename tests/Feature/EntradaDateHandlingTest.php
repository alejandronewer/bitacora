<?php

namespace Tests\Feature;

use App\Models\EntradaBitacora;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class EntradaDateHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_index_serializes_entry_dates_without_utc_suffix(): void
    {
        $entry = $this->createEntry([
            'fecha_inicio' => '2025-03-10 00:00:00',
            'fecha_fin' => '2025-03-10 01:30:00',
            'publicado' => 1,
        ]);

        $response = $this->getJson('/api/v1/entradas');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $entry->id)
            ->assertJsonPath('data.0.fecha_inicio', '2025-03-10T00:00:00')
            ->assertJsonPath('data.0.fecha_fin', '2025-03-10T01:30:00');
    }

    public function test_public_index_includes_the_whole_end_day_when_filtering_by_date_range(): void
    {
        $early = $this->createEntry([
            'fecha_inicio' => '2025-03-10 00:00:00',
            'publicado' => 1,
        ]);
        $late = $this->createEntry([
            'fecha_inicio' => '2025-03-10 23:30:00',
            'publicado' => 1,
        ]);
        $outside = $this->createEntry([
            'fecha_inicio' => '2025-03-11 00:00:00',
            'publicado' => 1,
        ]);

        $response = $this->getJson('/api/v1/entradas?desde=2025-03-10&hasta=2025-03-10');

        $ids = collect($response->json('data'))->pluck('id')->all();

        $response->assertOk();
        $this->assertContains($early->id, $ids);
        $this->assertContains($late->id, $ids);
        $this->assertNotContains($outside->id, $ids);
    }

    public function test_store_returns_the_same_local_datetime_selected_in_the_form(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::factory()->create();
        $role = Role::findOrCreate('operador', 'web');
        $user->assignRole($role);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/entradas', [
            'titulo' => 'Entrada con fecha local',
            'cuerpo_html' => '<p>Descripcion</p>',
            'cuerpo_texto' => 'Descripcion',
            'fecha_inicio' => '2025-03-10T00:00',
            'tipo_registro' => 'operativo',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.fecha_inicio', '2025-03-10T00:00:00');

        $this->assertDatabaseHas('entradas_bitacora', [
            'titulo' => 'Entrada con fecha local',
            'fecha_inicio' => '2025-03-10 00:00:00',
            'usuario_id' => $user->id,
        ]);
    }

    private function createEntry(array $attributes = []): EntradaBitacora
    {
        $user = User::factory()->create();

        return EntradaBitacora::create(array_merge([
            'titulo' => 'Entrada de prueba',
            'cuerpo_html' => '<p>Contenido</p>',
            'cuerpo_texto' => 'Contenido',
            'fecha_inicio' => '2025-03-10 12:00:00',
            'fecha_fin' => null,
            'usuario_id' => $user->id,
            'tipo_registro' => 'operativo',
            'publicado' => 0,
        ], $attributes));
    }
}
