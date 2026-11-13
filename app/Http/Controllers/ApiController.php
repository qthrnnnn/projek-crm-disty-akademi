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
        $query = $model::query();

        // Eager loading & specialized query per resource
        switch ($resource) {
            case 'training-schedules':
                $query->with(['product', 'trainer'])->withCount('registrations')->latest('training_date');
                if ($request->filled('status')) {
                    $query->where('status', $request->status);
                }
                if ($request->filled('product_id')) {
                    $query->where('product_id', $request->product_id);
                }
                if ($request->filled('trainer_id')) {
                    $query->where('trainer_id', $request->trainer_id);
                }
                if ($request->filled('search')) {
                    $q = $request->search;
                    $query->where(function ($sub) use ($q) {
                        $sub->where('location', 'like', "%{$q}%")
                            ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%"))
                            ->orWhereHas('trainer', fn ($t) => $t->where('name', 'like', "%{$q}%"));
                    });
                }
                break;

            case 'registrations':
                $query->with(['customer', 'product', 'schedule.product', 'schedule.trainer'])->latest();
                if ($request->filled('status')) {
                    $query->where('status', $request->status);
                }
                if ($request->filled('customer_id')) {
                    $query->where('customer_id', $request->customer_id);
                }
                if ($request->filled('product_id')) {
                    $query->where('product_id', $request->product_id);
                }
                if ($request->filled('search')) {
                    $q = $request->search;
                    $query->where(function ($sub) use ($q) {
                        $sub->where('registration_number', 'like', "%{$q}%")
                            ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$q}%"))
                            ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$q}%"))
                            ->orWhereHas('schedule.trainer', fn ($t) => $t->where('name', 'like', "%{$q}%"));
                    });
                }
                break;

            case 'products':
                $query->withCount('leads')->latest();
                if ($request->filled('category')) {
                    $query->where('category', $request->category);
                }
                if ($request->filled('product_type')) {
                    $query->where('product_type', $request->product_type);
                }
                if ($request->has('is_active') && $request->is_active !== '') {
                    $query->where('is_active', $request->boolean('is_active'));
                }
                if ($request->filled('search')) {
                    $q = $request->search;
                    $query->where(function ($s) use ($q) {
                        $s->where('name', 'like', "%{$q}%")
                            ->orWhere('code', 'like', "%{$q}%")
                            ->orWhere('description', 'like', "%{$q}%");
                    });
                }
                break;

            case 'leads':
                $query->with(['product', 'source', 'assignedUser', 'rejectionReason'])->latest();
                if ($request->user() && $request->user()->isSales()) {
                    $query->where('assigned_to', $request->user()->id);
                }
                if ($request->filled('status')) {
                    $query->where('status', $request->status);
                }
                if ($request->filled('product_id')) {
                    $query->where('product_id', $request->product_id);
                }
                if ($request->filled('search')) {
                    $q = $request->search;
                    $query->where(function ($s) use ($q) {
                        $s->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%")
                            ->orWhere('phone', 'like', "%{$q}%")
                            ->orWhere('lead_code', 'like', "%{$q}%")
                            ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$q}%"));
                    });
                }
                break;

            case 'payments':
                $query->with(['product', 'lead.product', 'verifiedByUser'])->latest();
                if ($request->user() && $request->user()->isSales()) {
                    $userId = $request->user()->id;
                    $query->whereHas('lead', fn ($lead) => $lead->where('assigned_to', $userId));
                }
                if ($request->filled('status')) {
                    $query->where('status', $request->status);
                }
                break;

            case 'commissions':
                $query->with(['user', 'lead.product', 'payment'])->latest();
                if ($request->user() && $request->user()->isSales()) {
                    $query->where('user_id', $request->user()->id);
                }
                if ($request->filled('status')) {
                    $query->where('status', $request->status);
                }
                break;

            case 'follow-ups':
                $query->with(['lead.product', 'assignedUser'])->latest('follow_up_at');
                if ($request->user() && $request->user()->isSales()) {
                    $query->where('assigned_to', $request->user()->id);
                }
                if ($request->filled('status')) {
                    $query->where('status', $request->status);
                }
                break;

            case 'customers':
                $query->with(['institution'])->latest();
                if ($request->filled('status')) {
                    $query->where('status', $request->status);
                }
                if ($request->filled('search')) {
                    $q = $request->search;
                    $query->where(function ($s) use ($q) {
                        $s->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%")
                            ->orWhere('phone', 'like', "%{$q}%")
                            ->orWhere('customer_code', 'like', "%{$q}%");
                    });
                }
                break;

            case 'trainers':
                $query->withCount('schedules')->latest();
                if ($request->has('is_active') && $request->is_active !== '') {
                    $query->where('is_active', $request->boolean('is_active'));
                }
                break;

            default:
                $query->latest();
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
                break;
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
        $query = $model::query();

        switch ($resource) {
            case 'training-schedules':
                $query->with(['product', 'trainer', 'registrations.customer']);
                break;
            case 'registrations':
                $query->with(['customer', 'product', 'schedule.product', 'schedule.trainer']);
                break;
            case 'products':
                $query->withCount('leads');
                break;
            case 'leads':
                $query->with(['product', 'source', 'assignedUser', 'rejectionReason', 'activities', 'followUps']);
                break;
            case 'payments':
                $query->with(['product', 'lead.product', 'verifiedByUser']);
                break;
            case 'commissions':
                $query->with(['user', 'lead.product', 'payment']);
                break;
            case 'follow-ups':
                $query->with(['lead.product', 'assignedUser']);
                break;
            case 'customers':
                $query->with(['institution', 'leads.product', 'registrations.product']);
                break;
        }

        $record = $query->findOrFail($id);

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

        if ($resource === 'registrations') {
            if (! isset($data['registration_number'])) {
                $data['registration_number'] = Registration::generateNumber();
            }
            if (! empty($data['training_schedule_id'])) {
                $sched = TrainingSchedule::find($data['training_schedule_id']);
                if ($sched) {
                    $data['product_id'] = $sched->product_id;
                    $data['training_date'] = $sched->training_date;
                    if (! isset($data['amount']) || $data['amount'] <= 0) {
                        $data['amount'] = $sched->product?->price ?? 0;
                    }
                }
            }
        }

        $record = $model::create($data);

        return response()->json(['data' => $record], 201);
    }

    public function update(Request $request, string $resource, int $id): JsonResponse
    {
        $model = $this->modelFor($resource);
        $record = $model::query()->findOrFail($id);
        $data = $this->validatedAttributes($request, $model);

        if ($resource === 'registrations' && ! empty($data['training_schedule_id'])) {
            $sched = TrainingSchedule::find($data['training_schedule_id']);
            if ($sched) {
                $data['product_id'] = $sched->product_id;
                $data['training_date'] = $sched->training_date;
            }
        }

        $record->update($data);

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
