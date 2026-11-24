<?php

namespace Tests\Feature;

use App\Http\Controllers\ApiController;
use App\Models\Product;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class CrmResourceTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('protectedWrites')]
    public function test_protected_writes_require_admin(string $action, string $resource): void
    {
        $request = Request::create('/');
        $request->setUserResolver(fn () => new User(['role' => 'sales']));
        $controller = new ApiController;

        try {
            match ($action) {
                'store' => $controller->store($request, $resource),
                'update' => $controller->update($request, $resource, 1),
                'destroy' => $controller->destroy($request, $resource, 1),
            };
            $this->fail('Expected the controller to reject a write by Sales.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public static function protectedWrites(): iterable
    {
        foreach (['users', 'products', 'trainers', 'payments', 'commissions'] as $resource) {
            foreach (['store', 'update', 'destroy'] as $action) {
                yield "$resource $action" => [$action, $resource];
            }
        }
    }

    public function test_admin_can_create_update_and_delete_products_on_both_api_prefixes(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));

        foreach (['/api', '/api/v1'] as $index => $prefix) {
            $code = 'CRM-'.($index + 1);
            $id = $this->postJson("$prefix/products", [
                'name' => 'Program Pelatihan',
                'code' => $code,
                'price' => 500000,
            ])->assertCreated()->json('data.id');

            $this->patchJson("$prefix/products/$id", ['code' => $code, 'price' => 0])
                ->assertOk()
                ->assertJsonPath('data.name', 'Program Pelatihan')
                ->assertJsonPath('data.price', '0.00');

            $this->deleteJson("$prefix/products/$id")->assertNoContent();
        }
    }

    public function test_generic_api_returns_field_errors_and_keeps_invalid_writes_out_of_the_database(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));
        $product = Product::create(['name' => 'Program Pelatihan', 'code' => 'CRM', 'price' => 500000]);

        $this->post('/api/v1/trainers', ['email' => 'invalid-email', 'phone' => ['08123']])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'phone']);
        $this->assertDatabaseCount('trainers', 0);

        $this->patchJson("/api/products/{$product->id}", ['price' => -1, 'product_type' => 'other'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['price', 'product_type']);
        $this->assertSame('500000.00', $product->fresh()->price);
    }

    public function test_manual_registration_validates_aliases_before_creating_a_customer(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'sales', 'is_active' => true]));
        $product = Product::create(['name' => 'Program Pelatihan', 'code' => 'CRM', 'price' => 500000]);
        $participant = [
            'participant_mode' => 'manual',
            'participant_name' => 'Peserta Baru',
            'participant_phone' => '08123456789',
            'product_id' => $product->id,
        ];

        $this->postJson('/api/registrations', $participant + ['amount' => -1, 'participant_birth_date' => 'invalid-date'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['amount', 'participant_birth_date']);
        $this->assertDatabaseCount('customers', 0);

        $response = $this->postJson('/api/registrations', $participant)->assertCreated();
        $this->assertDatabaseHas('customers', [
            'id' => $response->json('data.customer_id'),
            'name' => 'Peserta Baru',
        ]);
    }

    public function test_schedule_accepts_times_from_the_form_and_api(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));
        $product = Product::create(['name' => 'Program Pelatihan', 'code' => 'CRM']);
        $trainer = Trainer::create(['name' => 'Trainer CRM']);

        $this->postJson('/api/training-schedules', [
            'product_id' => $product->id,
            'trainer_id' => $trainer->id,
            'training_date' => '2026-12-01',
            'start_time' => '09:00',
            'end_time' => '16:00:00',
        ])->assertCreated();
    }

    public function test_sales_can_view_training_schedules_but_only_admin_can_create_update_or_delete(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $sales = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        $product = Product::create(['name' => 'Program Pelatihan', 'code' => 'CRM-SCHED']);
        $trainer = Trainer::create(['name' => 'Trainer CRM']);

        $scheduleId = $this->actingAs($admin)->postJson('/api/training-schedules', [
            'product_id' => $product->id,
            'trainer_id' => $trainer->id,
            'training_date' => '2026-12-10',
            'start_time' => '09:00',
            'end_time' => '16:00',
        ])->assertCreated()->json('data.id');

        $this->actingAs($sales);

        foreach (['/api', '/api/v1'] as $prefix) {
            $this->getJson("{$prefix}/training-schedules")->assertOk();
            $this->postJson("{$prefix}/training-schedules", [
                'product_id' => $product->id,
                'trainer_id' => $trainer->id,
                'training_date' => '2026-12-12',
                'start_time' => '09:00',
                'end_time' => '16:00',
            ])->assertForbidden();
            $this->patchJson("{$prefix}/training-schedules/{$scheduleId}", [
                'location' => 'Diubah Sales',
            ])->assertForbidden();
            $this->deleteJson("{$prefix}/training-schedules/{$scheduleId}")
                ->assertForbidden();
        }
    }
}
