<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Commission;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Institution;
use App\Models\ImportBatch;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\Payment;
use App\Models\Product;
use App\Models\RejectionReason;
use App\Models\Registration;
use App\Models\SalesReminder;
use App\Models\Trainer;
use App\Models\TrainingSchedule;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ApiController extends Controller
{
    private const RESOURCES = [
        'activities' => Activity::class,
        'commissions' => Commission::class,
        'customers' => Customer::class,
        'follow-ups' => FollowUp::class,
        'institutions' => Institution::class,
        'import-batches' => ImportBatch::class,
        'lead-sources' => LeadSource::class,
        'leads' => Lead::class,
        'payments' => Payment::class,
        'products' => Product::class,
        'rejection-reasons' => RejectionReason::class,
        'registrations' => Registration::class,
        'reminders' => SalesReminder::class,
        'trainers' => Trainer::class,
        'training-schedules' => TrainingSchedule::class,
        'users' => User::class,
    ];

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);
        $user = User::where('email', $credentials['email'])->where('is_active', true)->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        $token = $user->createToken($credentials['device_name'] ?? 'api-client')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Token berhasil dicabut.']);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()]);
    }

    public function index(Request $request, string $resource): JsonResponse
    {
        $model = $this->modelFor($resource);
        $query = $model::query()->latest();

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $columns = $model::getConnectionResolver()
                ->connection()->getSchemaBuilder()->getColumnListing((new $model)->getTable());
            $searchable = array_values(array_intersect(['name', 'email', 'code', 'lead_code', 'customer_code'], $columns));

            if ($searchable !== []) {
                $query->where(function ($builder) use ($search, $searchable): void {
                    foreach ($searchable as $column) {
                        $builder->orWhere($column, 'like', "%{$search}%");
                    }
                });
            }
        }

        $perPage = min(max($request->integer('per_page', 20), 1), 100);

        return response()->json($query->paginate($perPage)->withQueryString());
    }

    public function activities(Request $request): JsonResponse
    {
        $query = Activity::query()
            ->select(['id', 'lead_id', 'user_id', 'type', 'subject', 'description', 'activity_at'])
            ->with([
                'lead:id,name',
                'user:id,name',
            ])
            ->latest('activity_at');

        if ($request->user()->isSales()) {
            $userId = $request->user()->id;
            $query->where(function ($builder) use ($userId): void {
                $builder->where('user_id', $userId)
                    ->orWhereHas('lead', fn ($lead) => $lead->where('assigned_to', $userId));
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }

        return response()->json($query->paginate(25)->withQueryString());
    }

    public function show(string $resource, int $id): JsonResponse
    {
        $model = $this->modelFor($resource);
        $record = $model::query()->findOrFail($id);

        return response()->json(['data' => $record]);
    }

    public function store(Request $request, string $resource): JsonResponse
    {
        $model = $this->modelFor($resource);
        $data = $this->validatedAttributes($request, $model);

        if ($resource === 'leads' && ! isset($data['lead_code'])) {
            $data['lead_code'] = Lead::generateCode();
        }

        if ($resource === 'customers' && ! isset($data['customer_code'])) {
            $data['customer_code'] = Customer::generateCode();
        }

        $record = $model::create($data);

        return response()->json(['data' => $record], 201);
    }

    public function update(Request $request, string $resource, int $id): JsonResponse
    {
        $model = $this->modelFor($resource);
        $record = $model::query()->findOrFail($id);
        $record->update($this->validatedAttributes($request, $model));

        return response()->json(['data' => $record->refresh()]);
    }

    public function destroy(string $resource, int $id): JsonResponse
    {
        $model = $this->modelFor($resource);
        $record = $model::query()->findOrFail($id);
        $record->delete();

        return response()->json([], 204);
    }

    private function validatedAttributes(Request $request, string $model): array
    {
        $fillable = (new $model)->getFillable();
        $data = $request->only($fillable);

        if ($data === []) {
            throw ValidationException::withMessages([
                'data' => 'Minimal satu field yang valid harus dikirim.',
            ]);
        }

        return $data;
    }

    private function modelFor(string $resource): string
    {
        abort_unless(isset(self::RESOURCES[$resource]), 404, 'Resource API tidak ditemukan.');

        return self::RESOURCES[$resource];
    }
}
