<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadInstitutionSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_payload_creates_itb_and_makes_it_visible_in_the_institution_api(): void
    {
        $sales = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        $this->actingAs($sales);

        foreach (['/api', '/api/v1'] as $prefix) {
            $leadId = $this->postJson($prefix.'/leads', [
                'name' => 'Prospek ITB',
                'phone' => '08123456789',
                'institution' => 'Institut Teknologi Bandung',
                'company' => 'Institut Teknologi Bandung',
            ])->assertCreated()->json('data.id');

            $this->assertDatabaseHas('activities', ['lead_id' => $leadId, 'subject' => 'Lead baru ditambahkan']);
        }

        $this->assertDatabaseCount('institutions', 1);
        $this->assertDatabaseHas('institutions', ['name' => 'Institut Teknologi Bandung', 'type' => 'Perguruan Tinggi']);
        $this->assertDatabaseCount('customers', 0);
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));
        $this->getJson('/api/institutions?search=Institut%20Teknologi%20Bandung')
            ->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Institut Teknologi Bandung')
            ->assertJsonPath('data.0.type', 'Perguruan Tinggi');
    }

    public function test_editing_lead_institution_syncs_without_duplicate_or_overwriting_existing_details(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'sales', 'is_active' => true]));
        $existing = Institution::create([
            'name' => 'Institut Teknologi Bandung', 'city' => 'Bandung', 'province' => 'Jawa Barat',
        ]);
        $leadId = $this->postJson('/api/leads', ['name' => 'Prospek', 'institution' => 'Pribadi'])
            ->assertCreated()->json('data.id');
        $this->patchJson('/api/v1/leads/'.$leadId, [
            'institution' => '  institut teknologi bandung  ', 'city' => 'Jakarta',
        ])->assertOk();
        $this->assertDatabaseCount('institutions', 1);
        $this->assertSame('Bandung', $existing->fresh()->city);

        $this->patchJson('/api/leads/'.$leadId, ['institution' => 'Politeknik Negeri Bandung'])
            ->assertOk();
        $this->assertDatabaseHas('institutions', ['name' => 'Politeknik Negeri Bandung', 'type' => 'Perguruan Tinggi']);
    }

    public function test_empty_personal_and_invalid_leads_do_not_create_institutions(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'sales', 'is_active' => true]));
        foreach ([null, '   ', '-', 'Pribadi', 'Personal', 'Tidak ada'] as $institution) {
            $this->postJson('/api/leads', ['name' => 'Prospek', 'institution' => $institution])->assertCreated();
        }
        $this->postJson('/api/leads', ['institution' => 'Institut Teknologi Bandung'])
            ->assertUnprocessable();
        $this->assertDatabaseCount('institutions', 0);
    }
}
