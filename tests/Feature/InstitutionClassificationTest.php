<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InstitutionClassificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_classifies_campus_names_and_preserves_company_types(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));

        foreach (['Universitas Airlangga', 'INSTITUT Teknologi Sepuluh Nopember', 'politeknik Negeri Surabaya'] as $name) {
            $this->postJson('/api/institutions', ['name' => $name, 'type' => 'company'])
                ->assertCreated()->assertJsonPath('data.type', 'Perguruan Tinggi');
            $this->assertDatabaseHas('institutions', ['name' => $name, 'type' => 'Perguruan Tinggi']);
        }

        $this->postJson('/api/institutions', ['name' => 'PT Wings Surya Surabaya', 'type' => 'company'])
            ->assertCreated()->assertJsonPath('data.type', 'company');
        $this->getJson('/api/institutions?search=Perguruan%20Tinggi')->assertOk()->assertJsonCount(3, 'data');
    }

    public function test_model_creation_and_api_rename_apply_the_same_rule(): void
    {
        $campus = Institution::firstOrCreate(['name' => 'Politeknik Negeri Malang']);
        $this->assertSame('Perguruan Tinggi', $campus->fresh()->type);

        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));
        $institution = Institution::create(['name' => 'Mitra Pelatihan', 'type' => 'company']);
        $this->patchJson('/api/v1/institutions/'.$institution->id, ['name' => 'Institut Teknologi Bandung'])
            ->assertOk()->assertJsonPath('data.type', 'Perguruan Tinggi');
        $this->assertSame('Perguruan Tinggi', $institution->fresh()->type);
    }

    public function test_migration_corrects_existing_rows_without_changing_companies(): void
    {
        DB::table('institutions')->insert([
            ['name' => 'Universitas Negeri Surabaya', 'type' => 'company'],
            ['name' => 'PT Wings Surya Surabaya', 'type' => 'company'],
        ]);
        $migration = require database_path('migrations/2026_11_23_000001_classify_higher_education_institutions.php');
        $migration->up();
        $migration->up();

        $this->assertDatabaseHas('institutions', ['name' => 'Universitas Negeri Surabaya', 'type' => 'Perguruan Tinggi']);
        $this->assertDatabaseHas('institutions', ['name' => 'PT Wings Surya Surabaya', 'type' => 'company']);
    }
}
