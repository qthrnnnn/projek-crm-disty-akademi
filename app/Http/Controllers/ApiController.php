<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Commission;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\ImportBatch;
use App\Models\Institution;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Registration;
use App\Models\RejectionReason;
use App\Models\SalesReminder;
use App\Models\Trainer;
use App\Models\TrainingSchedule;
use App\Models\User;
use App\Support\CrmResourceRules;
use App\Support\Jwt;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ApiController extends Controller
{
    private const ADMIN_WRITE_RESOURCES = [
        'users',
        'products',
        'trainers',
        'payments',
        'commissions',
        'training-schedules',
        'lead-sources',
        'rejection-reasons',
        'import-batches',
    ];

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
            'remember' => ['sometimes', 'boolean'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password) || ! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        if ($request->hasSession()) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
        }

        $berlaku = (int) config('jwt.berlaku_detik', 28800);
        $rahasia = (string) config('jwt.rahasia', config('disty.jwt.rahasia', 'default-jwt-secret'));

        $token = Jwt::buat([
            'sub' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ], $rahasia, $berlaku);

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'token' => $token,
            'tokenType' => 'Bearer',
            'expiresIn' => $berlaku,
            'user' => $user,
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        if ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'success' => true,
            'message' => 'Sesi berhasil diakhiri.',
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => $user,
            'user' => $user,
        ]);
    }

    public function updateBankAccount(Request $request): JsonResponse
    {
        $data = $request->validate([
            'bank_name' => 'required|string|max:50',
            'bank_account_number' => 'required|string|max:50',
            'bank_account_name' => 'required|string|max:150',
        ]);

        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user->update([
            'bank_name' => trim($data['bank_name']),
            'bank_account_number' => trim($data['bank_account_number']),
            'bank_account_name' => trim($data['bank_account_name']),
        ]);

        // Perbarui hanya komisi yang BELUM DIBAYAR (pending/approved) agar mengikuti rekening baru sales.
        // Komisi yang SUDAH LUNAS DITRANSFER (status: paid) tetap menjaga snapshot historis rekening lama.
        Commission::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->updateWithHistory([
                'bank_name' => trim($data['bank_name']),
                'bank_account_number' => trim($data['bank_account_number']),
                'bank_account_name' => trim($data['bank_account_name']),
            ]);

        return response()->json([
            'message' => 'Rekening bank pencairan komisi berhasil diperbarui.',
            'data' => $user->fresh(),
        ]);
    }

    public function index(Request $request, string $resource): JsonResponse
    {
        $user = $request->user();
        $salesId = $user?->isSales() ? (int) $user->id : null;
        $query = $this->resourceQuery($resource, $user);

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
                if ($salesId === null && $request->filled('sales_id')) {
                    $query->handledBy($request->integer('sales_id'));
                }

                if ($request->boolean('for_payment')) {
                    $query->awaitingPaymentSubmission();
                    $query->withExists([
                        'customer as is_repeat_order' => fn (Builder $customer) => $customer
                            ->whereHas('lead.payments', fn (Builder $payments) => $payments->where('status', 'valid')),
                    ]);
                }

                $query->with(['customer.lead.assignedUser', 'user', 'product', 'schedule.product', 'schedule.trainer'])->latest();

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
                            ->orWhereHas('schedule.trainer', fn ($t) => $t->where('name', 'like', "%{$q}%"))
                            ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%"))
                            ->orWhereHas('customer.lead.assignedUser', fn ($u) => $u->where('name', 'like', "%{$q}%"));
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
                $query->with(['product', 'source', 'assignedUser', 'rejectionReason', 'payments'])->latest();
                if ($request->user() && $request->user()->isSales()) {
                    $query->where('assigned_to', $request->user()->id);
                } elseif ($request->filled('assigned')) {
                    $query->where('assigned_to', $request->assigned);
                }
                if ($request->boolean('for_payment')) {
                    $query->awaitingPaymentSubmission();
                }
                if ($request->boolean('for_followup')) {
                    // Hanya prospek yang siap difollow-up: bukan yang sudah won dan bukan yang sudah ambil program
                    $query->whereNotIn('status', ['won', 'lost', 'inactive'])
                        ->whereDoesntHave('payments', function ($p) {
                            $p->where('status', 'valid');
                        })
                        ->whereDoesntHave('customer', function ($c) {
                            $c->whereHas('registrations');
                        })
                        ->whereNotIn('phone', function ($sub) {
                            $sub->select('customers.phone')
                                ->from('customers')
                                ->join('registrations', 'registrations.customer_id', '=', 'customers.id')
                                ->whereNotNull('customers.phone');
                        });
                }
                if ($request->filled('status')) {
                    $query->where('status', $request->status);
                }
                if ($request->filled('date')) {
                    $query->whereDate('created_at', $request->date);
                }
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
                } elseif ($request->filled('start_date')) {
                    $query->whereDate('created_at', '>=', $request->start_date);
                } elseif ($request->filled('end_date')) {
                    $query->whereDate('created_at', '<=', $request->end_date);
                }
                if ($request->boolean('unfollowed')) {
                    // Calon customer yang sudah berhasil (won) atau sudah ambil program (memiliki registrasi) TIDAK dimasukkan
                    $query->whereNotIn('status', ['won', 'inactive'])
                        ->whereDoesntHave('payments', function ($p) {
                            $p->where('status', 'valid');
                        })
                        ->whereDoesntHave('customer', function ($c) {
                            $c->whereHas('registrations');
                        })
                        ->whereNotIn('phone', function ($sub) {
                            $sub->select('customers.phone')
                                ->from('customers')
                                ->join('registrations', 'registrations.customer_id', '=', 'customers.id')
                                ->whereNotNull('customers.phone');
                        })
                        ->where(function ($sub) {
                            $sub->where(function ($fresh) {
                                $fresh->whereNull('first_contacted_at')
                                    ->whereNotIn('status', ['won', 'lost', 'inactive']);
                            })
                                ->orWhere('status', 'new')
                                ->orWhere(function ($q) {
                                    $q->whereNotIn('status', ['won', 'lost', 'inactive'])
                                        ->where('last_contacted_at', '<', now()->subDays(7));
                                });
                        });
                }
                if ($request->filled('product_id')) {
                    $query->where('product_id', $request->product_id);
                }
                if ($request->filled('search')) {
                    $q = $request->search;
                    $query->where(function ($s) use ($q) {
                        $s->where('name', 'like', "%{$q}%")
                            ->orWhere('nik', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%")
                            ->orWhere('phone', 'like', "%{$q}%")
                            ->orWhere('whatsapp', 'like', "%{$q}%")
                            ->orWhere('company', 'like', "%{$q}%")
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
                $query->with(['user:id,name,email,phone,bank_name,bank_account_number,bank_account_name', 'lead.product', 'payment', 'lead.payments'])->latest();
                if ($request->user() && $request->user()->isSales()) {
                    $query->where('user_id', $request->user()->id);
                } elseif ($request->filled('user_id')) {
                    $query->where('user_id', $request->user_id);
                }
                if ($request->filled('status')) {
                    $query->where('status', $request->status);
                }
                if ($request->filled('date')) {
                    $request->validate(['date' => ['date_format:Y-m-d']]);
                    $day = \Carbon\CarbonImmutable::parse($request->input('date'), 'Asia/Jakarta')->startOfDay();
                    $start = $day->setTimezone(config('app.timezone'));
                    $end = $day->addDay()->setTimezone(config('app.timezone'));
                    $query->where(function ($sub) use ($start, $end) {
                        foreach (['paid_at', 'approved_at', 'created_at'] as $column) {
                            $sub->orWhere(fn ($date) => $date->where($column, '>=', $start)->where($column, '<', $end));
                        }
                    });
                }
                if ($request->filled('search')) {
                    $q = trim((string) $request->search);
                    $query->where(function ($s) use ($q) {
                        $s->whereHas('lead', fn ($l) => $l->where('name', 'like', "%{$q}%"))
                            ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%"))
                            ->orWhereHas('lead.product', fn ($p) => $p->where('name', 'like', "%{$q}%"));
                    });
                }
                break;

            case 'follow-ups':
                $query->with(['lead.product', 'assignedUser'])->latest('follow_up_at');
                if ($request->user() && $request->user()->isSales()) {
                    $query->where('assigned_to', $request->user()->id);
                } elseif ($request->filled('assigned')) {
                    $query->where('assigned_to', $request->assigned);
                }
                if ($request->filled('lead_id')) {
                    $query->where('lead_id', $request->lead_id);
                }
                if ($request->filled('status')) {
                    $query->where('status', $request->status);
                }
                break;

            case 'customers':
                $query->with(['institution'])->latest();

                if ($request->filled('institution_id')) {
                    $query->where('institution_id', $request->institution_id);
                } else {
                    // Hanya yang sudah terkonfirmasi bayar (memiliki pendaftaran confirmed/attended ATAU pembayaran valid)
                    // dan BUKAN yang berstatus registered atau cancelled
                    $query->where(function ($sub) {
                        $sub->whereHas('registrations', function ($r) {
                            $r->whereIn('status', ['confirmed', 'attended']);
                        })
                            ->orWhereHas('lead.payments', function ($p) {
                                $p->where('status', 'valid');
                            })
                            ->orWhere(function ($m) {
                                $m->where('source', 'manual')->where('status', 'active');
                            });
                    });
                }
                if ($request->filled('status')) {
                    $query->where('status', $request->status);
                }
                if ($request->filled('date')) {
                    $query->whereDate('created_at', $request->date);
                }
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
                } elseif ($request->filled('start_date')) {
                    $query->whereDate('created_at', '>=', $request->start_date);
                } elseif ($request->filled('end_date')) {
                    $query->whereDate('created_at', '<=', $request->end_date);
                }
                if ($request->filled('search')) {
                    $q = $request->search;
                    $query->where(function ($s) use ($q) {
                        $s->where('name', 'like', "%{$q}%")
                            ->orWhere('nik', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%")
                            ->orWhere('phone', 'like', "%{$q}%")
                            ->orWhere('customer_code', 'like', "%{$q}%")
                            ->orWhereHas('institution', fn ($i) => $i->where('name', 'like', "%{$q}%"));
                    });
                }
                break;

            case 'institutions':
                $query->withCount(['customers' => fn (Builder $customers) => $customers
                    ->when($salesId, fn (Builder $handled) => $handled->handledBy($salesId))])->latest();
                if ($request->filled('type')) {
                    $query->where('type', $request->type);
                }
                if ($request->filled('search')) {
                    $q = $request->search;
                    $query->where(function ($sub) use ($q) {
                        $sub->where('name', 'like', "%{$q}%")
                            ->orWhere('city', 'like', "%{$q}%")
                            ->orWhere('province', 'like', "%{$q}%")
                            ->orWhere('type', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%")
                            ->orWhere('phone', 'like', "%{$q}%");
                    });
                }
                break;

            case 'trainers':
                $query->withCount('schedules')->latest();
                if ($request->has('is_active') && $request->is_active !== '') {
                    $query->where('is_active', $request->boolean('is_active'));
                }
                if ($request->filled('search')) {
                    $q = trim((string) $request->search);
                    $query->where(function ($s) use ($q) {
                        $s->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%")
                            ->orWhere('phone', 'like', "%{$q}%")
                            ->orWhere('notes', 'like', "%{$q}%");
                    });
                }
                break;

            case 'reminders':
                $query->with(['lead.product', 'admin', 'sales'])->latest();
                if ($request->user() && $request->user()->isSales()) {
                    $query->where('sales_id', $request->user()->id);
                } elseif ($request->filled('sales_id')) {
                    $query->where('sales_id', $request->sales_id);
                }
                if ($request->has('is_read') && $request->is_read !== '') {
                    $query->where('is_read', $request->boolean('is_read'));
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

        $perPage = min(max($request->integer('per_page', 20), 1), 500);

        if ($resource === 'commissions') {
            $baseQuery = Commission::query();
            if ($request->user() && $request->user()->isSales()) {
                $baseQuery->where('user_id', $request->user()->id);
            } elseif ($request->filled('user_id')) {
                $baseQuery->where('user_id', $request->user_id);
            }

            $totalEarned = (clone $baseQuery)->sum('commission_amount');
            $totalPending = (clone $baseQuery)->where('status', 'pending')->sum('commission_amount');
            $totalApproved = (clone $baseQuery)->where('status', 'approved')->sum('commission_amount');
            $totalPaid = (clone $baseQuery)->where('status', 'paid')->sum('commission_amount');

            $paginated = $query->paginate($perPage)->withQueryString();
            $data = $paginated->toArray();
            $data['summary'] = [
                'total_earned' => (float) $totalEarned,
                'total_pending' => (float) $totalPending,
                'total_approved' => (float) $totalApproved,
                'total_paid' => (float) $totalPaid,
                'total_unpaid' => (float) ($totalPending + $totalApproved),
            ];
            $data['total_pending'] = (float) ($totalPending + $totalApproved);
            $data['total_paid'] = (float) $totalPaid;

            return response()->json($data);
        }

        return response()->json($query->paginate($perPage)->withQueryString());
    }

    public function activities(Request $request): JsonResponse
    {
        $query = Activity::query()->visibleTo($request->user())
            ->select(['id', 'lead_id', 'user_id', 'type', 'subject', 'description', 'activity_at',
                'entity_type', 'entity_id', 'entity_name', 'actor_name', 'lead_name', 'changes'])
            ->with([
                'lead:id,name,deleted_at',
                'user:id,name',
            ])
            ->latest('activity_at')->latest('id');

        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }

        return response()->json($query->paginate(min(max($request->integer('per_page', 25), 1), 100))->withQueryString());
    }

    public function show(Request $request, string $resource, int $id): JsonResponse
    {
        $user = $request->user();
        $salesId = $user?->isSales() ? (int) $user->id : null;
        $query = $this->resourceQuery($resource, $user);

        switch ($resource) {
            case 'training-schedules':
                $query->with([
                    'product',
                    'trainer',
                    'registrations' => fn ($registrations) => $registrations
                        ->when($salesId, fn (Builder $handled) => $handled->handledBy($salesId))
                        ->with('customer'),
                ]);
                break;
            case 'registrations':
                $query->with(['customer.lead.assignedUser', 'user', 'product', 'schedule.product', 'schedule.trainer']);
                break;
            case 'products':
                $query->withCount('leads');
                break;
            case 'leads':
                $query->with(['product', 'source', 'assignedUser', 'rejectionReason', 'activities', 'followUps']);
                break;
            case 'payments':
                $query->with(['product', 'lead.product', 'lead.assignedUser', 'verifiedByUser']);
                break;
            case 'commissions':
                $query->with(['user', 'lead.product', 'payment', 'lead.payments']);
                break;
            case 'follow-ups':
                $query->with(['lead.product', 'assignedUser']);
                break;
            case 'customers':
                $query->with([
                    'institution',
                    'lead.product',
                    'lead.assignedUser',
                    'registrations' => fn ($registrations) => $registrations
                        ->when($salesId, fn (Builder $handled) => $handled->handledBy($salesId))
                        ->with(['product', 'trainingSchedule.trainer', 'trainingSchedule.product', 'user']),
                ]);
                break;
            case 'institutions':
                $customers = fn ($customers) => $customers
                    ->when($salesId, fn (Builder $handled) => $handled->handledBy($salesId));
                $query->with(['customers' => $customers])->withCount(['customers' => $customers]);
                break;
            case 'reminders':
                $query->with(['lead.product', 'admin', 'sales']);
                break;
        }

        $record = $query->findOrFail($id);

        if ($resource === 'customers') {
            $leadIds = Lead::where(fn (Builder $lead) => $lead
                ->whereKey($record->lead_id)
                ->when($record->phone, fn (Builder $samePhone) => $samePhone->orWhere('phone', $record->phone)))
                ->when($salesId, fn (Builder $assignedLead) => $assignedLead->where('assigned_to', $salesId))
                ->pluck('id');
            $allPayments = Payment::with(['verifiedByUser', 'commission'])->whereIn('lead_id', $leadIds)->get();

            foreach ($record->registrations as $reg) {
                // Prioritize matching payment with same product_id
                $matchedPayment = $allPayments->filter(fn ($p) => (int) $p->product_id === (int) $reg->product_id)->sortByDesc('id')->first();
                if (! $matchedPayment) {
                    $matchedPayment = $allPayments->filter(fn ($p) => abs((float) $p->amount - (float) $reg->amount) < 1.0)->first();
                }
                if (! $matchedPayment && $allPayments->isNotEmpty()) {
                    $matchedPayment = $allPayments->first();
                }
                $reg->setAttribute('payment', $matchedPayment);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $record,
        ]);
    }

    public function store(Request $request, string $resource): JsonResponse
    {
        $model = $this->modelFor($resource);
        $this->authorizeWrite($request, $resource);
        abort_if(
            $resource === 'registrations' && ! $request->user()?->isSales(),
            403,
            'Akses ditolak. Hanya Sales yang dapat mendaftarkan peserta.',
        );
        $data = $this->validatedAttributes($request, $resource);

        if ($resource === 'follow-ups') {
            $lead = Lead::find($data['lead_id']);

            if (isset($data['type']) && empty($data['method'])) {
                $data['method'] = $data['type'];
            }
            if (isset($data['scheduled_at']) && empty($data['follow_up_at'])) {
                $data['follow_up_at'] = $data['scheduled_at'];
            }
            if (isset($data['notes']) && empty($data['description'])) {
                $data['description'] = $data['notes'];
            }

            if (empty($data['title'])) {
                $methodLabel = match ($data['method'] ?? 'whatsapp') {
                    'call' => 'Telepon',
                    'email' => 'Email',
                    'meeting' => 'Pertemuan',
                    default => 'WhatsApp',
                };
                $leadName = $lead ? $lead->name : 'Peserta';
                $data['title'] = "Follow-Up ({$methodLabel}) - {$leadName}";
            }

            if (empty($data['priority'])) {
                $data['priority'] = 'normal';
            }
            if (empty($data['status'])) {
                $data['status'] = 'pending';
            }

            if (empty($data['assigned_to'])) {
                $data['assigned_to'] = $request->user()?->id ?: ($lead?->assigned_to ?: 1);
            }
            if ($request->user() && $request->user()->isSales()) {
                $data['assigned_to'] = $request->user()->id;
            }

            if (empty($data['follow_up_at'])) {
                $data['follow_up_at'] = now()->addDays(1);
            }

            unset($data['type'], $data['scheduled_at'], $data['notes']);
        }

        if ($resource === 'leads') {
            if (! isset($data['lead_code'])) {
                $data['lead_code'] = Lead::generateCode();
            }
            if (isset($data['institution']) && empty($data['company'])) {
                $data['company'] = $data['institution'];
            }
            if (isset($data['phone']) && empty($data['whatsapp'])) {
                $data['whatsapp'] = $data['phone'];
            } elseif (isset($data['whatsapp']) && empty($data['phone'])) {
                $data['phone'] = $data['whatsapp'];
            }
            if (empty($data['assigned_to']) && $request->user()) {
                $data['assigned_to'] = $request->user()->id;
            }
            if (empty($data['status'])) {
                $data['status'] = 'new';
            }
        }

        if ($resource === 'customers') {
            if (! isset($data['customer_code'])) {
                $data['customer_code'] = Customer::generateCode();
            }
            if (isset($data['company']) && ! isset($data['institution_id'])) {
                $inst = Institution::firstOrCreate(['name' => trim($data['company'])]);
                $data['institution_id'] = $inst->id;
            }
        }

        if ($resource === 'registrations') {
            if (! isset($data['registration_number'])) {
                $data['registration_number'] = Registration::generateNumber();
            }
            if (! isset($data['user_id'])) {
                $data['user_id'] = $request->user()?->id ?: (auth()->id() ?: 1);
            }

            // Jika peserta diinput manual secara langsung di form pendaftaran
            if (isset($data['participant_name'])) {
                $operatorId = $request->user()?->id ?: (auth()->id() ?: 1);
                $data['user_id'] = $operatorId;
                $name = $data['participant_name'];
                $phone = $data['participant_phone'] ?? '-';
                $nik = $data['participant_nik'] ?? null;
                $birthDate = $data['participant_birth_date'] ?? null;
                $company = $data['participant_company'] ?? null;

                $institutionId = null;
                if (! empty($company)) {
                    $inst = Institution::firstOrCreate(['name' => trim($company)]);
                    $institutionId = $inst->id;
                }

                $customer = Customer::create([
                    'customer_code' => Customer::generateCode(),
                    'name' => $name,
                    'phone' => $phone,
                    'nik' => $nik,
                    'document_number' => $nik,
                    'birth_date' => $birthDate,
                    'institution_id' => $institutionId,
                    'status' => 'prospect',
                    'source' => 'direct_registration',
                    'notes' => 'Didaftarkan langsung dari Form Pendaftaran Peserta',
                ]);

                $data['customer_id'] = $customer->id;
            }
            // Jika pendaftaran berasal dari calon peserta (Lead / Prospek)
            elseif (isset($data['lead_id'])) {
                $lead = Lead::find($data['lead_id']);
                if ($lead) {
                    if (! empty($lead->assigned_to)) {
                        $data['user_id'] = $lead->assigned_to;
                    }
                    $customer = app(LeadController::class)->ensureCustomerCreated($lead);
                    $data['customer_id'] = $customer->id;

                    $hasValidPayment = $lead->payments()->where('status', 'valid')->exists();

                    if (! $hasValidPayment && ($data['status'] ?? '') === 'cancelled') {
                        $customer->update(['status' => 'inactive']);
                        $lead->update(['status' => 'lost']);
                    } elseif (! $hasValidPayment) {
                        // Belum ada pembayaran valid yang di-ACC admin:
                        // Pendaftaran berstatus 'registered' (terdaftar, menunggu pembayaran/verifikasi)
                        // Customer berstatus 'prospect' (belum resmi closing)
                        // Lead berstatus 'negotiation' (menunggu verifikasi pembayaran admin), BUKAN 'won'
                        $data['status'] = 'registered';
                        $customer->update(['status' => 'prospect']);
                        $leadUpdate = [
                            'status' => 'negotiation',
                        ];
                        if (! empty($data['product_id'])) {
                            $leadUpdate['product_id'] = $data['product_id'];
                        }
                        if (($data['amount'] ?? 0) > 0) {
                            $leadUpdate['deal_price'] = (float) $data['amount'];
                        }
                        $lead->update($leadUpdate);

                        Activity::create([
                            'lead_id' => $lead->id,
                            'user_id' => auth()->id() ?? $lead->assigned_to,
                            'type' => 'note',
                            'subject' => 'Pendaftaran Peserta (Menunggu Verifikasi Pembayaran Admin)',
                            'description' => 'Calon peserta didaftarkan ke program pelatihan (Status: Registered). Status prospek akan resmi menjadi Berhasil (Closing) setelah pembayaran diverifikasi valid oleh admin.',
                            'activity_at' => now(),
                        ]);
                    }
                }
            }
            // Jika pendaftaran berasal dari Customer Terdaftar (Repeat Order / Pembelian Program Baru)
            elseif (isset($data['customer_id'])) {
                $customer = Customer::find($data['customer_id']);
                if ($customer) {
                    $salesId = ($request->user() && $request->user()->isSales())
                        ? $request->user()->id
                        : ($customer->lead?->assigned_to ?? ($data['user_id'] ?? (auth()->id() ?: 1)));
                    $data['user_id'] = $salesId;
                }
            }

            if (! empty($data['training_schedule_id'])) {
                $sched = TrainingSchedule::find($data['training_schedule_id']);
                if ($sched) {
                    $data['product_id'] = $sched->product_id;
                    $data['training_date'] = $sched->training_date;
                    if (! isset($data['amount']) || $data['amount'] <= 0) {
                        $base = (float) ($sched->product?->price ?? 0);
                        $data['amount'] = round($base * 1.10, 2);
                    }
                }
            } elseif (! empty($data['product_id'])) {
                $prod = Product::find($data['product_id']);
                if ($prod && (! isset($data['amount']) || $data['amount'] <= 0)) {
                    $base = (float) ($prod->price ?? 0);
                    $data['amount'] = round($base * 1.10, 2);
                }
            }
        }

        if ($resource === 'payments') {
            if ($request->hasFile('proof')) {
                $data['proof_file'] = $request->file('proof')->store('payments', 'public');
            } elseif ($request->hasFile('proof_file')) {
                $data['proof_file'] = $request->file('proof_file')->store('payments', 'public');
            }
            if (isset($data['sender_name']) && empty($data['account_name'])) {
                $data['account_name'] = $data['sender_name'];
            }
            if (empty($data['payment_date'])) {
                $data['payment_date'] = now();
            }
            if (empty($data['status'])) {
                $data['status'] = 'pending';
            }
            if (! empty($data['lead_id'])) {
                $lead = Lead::find($data['lead_id']);
                if ($lead) {
                    if (empty($data['product_id']) && $lead->product_id) {
                        $data['product_id'] = $lead->product_id;
                    }
                    if (empty($data['amount']) || (float) $data['amount'] <= 0) {
                        $base = (float) ($lead->product?->price ?? 0);
                        $data['amount'] = $base > 0 ? round($base * 1.10, 2) : 1831500;
                    }
                }
            }
            // Jika pembayaran dibuat untuk Customer Terdaftar
            elseif (isset($data['customer_id'])) {
                $customer = Customer::find($data['customer_id']);
                if ($customer) {
                    $lead = $customer->lead;
                    if (! $lead) {
                        $lead = Lead::create([
                            'lead_code' => Lead::generateCode(),
                            'name' => $customer->name,
                            'phone' => $customer->phone,
                            'whatsapp' => $customer->phone,
                            'company' => $customer->company,
                            'nik' => $customer->nik,
                            'product_id' => $data['product_id'] ?? null,
                            'deal_price' => $data['amount'] ?? null,
                            'status' => 'negotiation',
                            'assigned_to' => auth()->id() ?: 1,
                        ]);
                        $customer->update(['lead_id' => $lead->id]);
                    }
                    $data['lead_id'] = $lead->id;
                    if (empty($data['product_id']) && $lead->product_id) {
                        $data['product_id'] = $lead->product_id;
                    }
                    if (empty($data['amount']) || (float) $data['amount'] <= 0) {
                        $base = (float) ($lead->product?->price ?? 0);
                        $data['amount'] = $base > 0 ? round($base * 1.10, 2) : 1831500;
                    }
                }
            }
        }

        $record = DB::transaction(function () use ($model, $data, $resource, $request) {
            $record = $model::create(Arr::only($data, (new $model)->getFillable()));

            if ($resource === 'leads') {
                app(LeadController::class)->syncInstitutionFromCompany($record->company, $record->city, $record->province);
            }

            if ($resource === 'follow-ups' && $request->user()?->isAdmin() && ! empty($record->assigned_to)) {
                $salesTarget = User::find($record->assigned_to);
                if ($salesTarget && $salesTarget->isSales()) {
                    SalesReminder::create([
                        'lead_id' => $record->lead_id,
                        'admin_id' => $request->user()->id,
                        'sales_id' => $salesTarget->id,
                        'message' => $record->title.($record->description ? ' — '.$record->description : ''),
                        'priority' => in_array($record->priority ?? 'normal', ['high', 'urgent']) ? 'urgent' : 'normal',
                        'is_read' => false,
                        'is_completed' => false,
                    ]);
                }
            }

            return $record;
        });

        if ($resource === 'payments' && $record->lead) {
            Commission::ensureCommissionCreated($record->lead, (float) $record->amount, $record->id);
            if ($record->status === 'valid') {
                $operatorId = $request->user()?->id ?: (auth()->id() ?: 1);
                $record->processVerification('valid', $operatorId);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $record,
        ], 201);
    }

    public function update(Request $request, string $resource, int $id): JsonResponse
    {
        $this->authorizeWrite($request, $resource);
        $record = $this->resourceQuery($resource, $request->user())->findOrFail($id);
        $data = $this->validatedAttributes($request, $resource, $record);

        if ($resource === 'registrations') {
            if (! empty($data['training_schedule_id'])) {
                $sched = TrainingSchedule::find($data['training_schedule_id']);
                if ($sched) {
                    $data['product_id'] = $sched->product_id;
                    $data['training_date'] = $sched->training_date;
                }
            } elseif (! empty($data['product_id'])) {
                $prod = Product::find($data['product_id']);
                if ($prod && (! isset($data['amount']) || $data['amount'] <= 0)) {
                    $base = (float) ($prod->price ?? 0);
                    $data['amount'] = round($base * 1.10, 2);
                }
            }
        }

        if ($resource === 'follow-ups') {
            $leadId = $data['lead_id'] ?? $record->lead_id;
            $lead = $leadId ? Lead::find($leadId) : null;

            if (isset($data['type']) && empty($data['method'])) {
                $data['method'] = $data['type'];
            }
            if (isset($data['scheduled_at']) && empty($data['follow_up_at'])) {
                $data['follow_up_at'] = $data['scheduled_at'];
            }
            if (isset($data['notes']) && empty($data['description'])) {
                $data['description'] = $data['notes'];
            }

            if (isset($data['status']) && $data['status'] === 'completed' && empty($record->completed_at)) {
                $data['completed_at'] = now();
            }

            // Sinkronisasi status prospek (lead) jika follow-up diperbarui atau diselesaikan
            if ($lead) {
                $leadUpdates = [
                    'last_contacted_at' => now(),
                ];
                if (! $lead->first_contacted_at) {
                    $leadUpdates['first_contacted_at'] = now();
                }

                $rejectionReasonId = $data['rejection_reason_id'] ?? null;
                $leadStatus = $data['lead_status'] ?? null;
                $feedbackType = $data['feedback_type'] ?? null;
                $resultNotes = $data['result_notes'] ?? null;

                if ($rejectionReasonId) {
                    $leadUpdates['status'] = 'lost';
                    $leadUpdates['lost_at'] = now();
                    $leadUpdates['rejection_reason_id'] = $rejectionReasonId;
                    if ($resultNotes) {
                        $leadUpdates['rejection_notes'] = $resultNotes;
                    }
                } elseif ($leadStatus) {
                    $leadUpdates['status'] = $leadStatus;
                    if ($leadStatus === 'lost') {
                        $leadUpdates['lost_at'] = now();
                        if ($resultNotes) {
                            $leadUpdates['rejection_notes'] = $resultNotes;
                        }
                        if ($feedbackType) {
                            $rrId = RejectionReason::where('name', $feedbackType)->value('id');
                            if ($rrId) {
                                $leadUpdates['rejection_reason_id'] = $rrId;
                            }
                        }
                    }
                } elseif ($feedbackType) {
                    if (in_array($feedbackType, ['not_interested', 'not_willing', 'wrong_number', 'unreachable'])) {
                        $leadUpdates['status'] = 'lost';
                        $leadUpdates['lost_at'] = now();
                        if ($resultNotes) {
                            $leadUpdates['rejection_notes'] = $resultNotes;
                        }
                        $rrId = RejectionReason::where('name', $feedbackType)->value('id');
                        if ($rrId) {
                            $leadUpdates['rejection_reason_id'] = $rrId;
                        }
                    } elseif ($feedbackType === 'contacted') {
                        $leadUpdates['status'] = 'contacted';
                    } elseif ($feedbackType === 'interested') {
                        $leadUpdates['status'] = 'active';
                    } elseif ($feedbackType === 'negotiation') {
                        $leadUpdates['status'] = 'negotiation';
                    } elseif ($feedbackType === 'won') {
                        $leadUpdates['status'] = 'won';
                    }
                }

                if (! empty($leadUpdates)) {
                    $lead->update($leadUpdates);

                    if (isset($leadUpdates['status'])) {
                        $statusLabel = match ($leadUpdates['status']) {
                            'new' => 'Baru',
                            'contacted' => 'Dikontak',
                            'active' => 'Aktif',
                            'negotiation' => 'Negosiasi',
                            'won' => 'Berhasil (Won)',
                            'lost' => 'Ditolak (Lost)',
                            default => $leadUpdates['status'],
                        };

                        Activity::create([
                            'lead_id' => $lead->id,
                            'user_id' => $request->user()?->id ?: ($lead->assigned_to ?: 1),
                            'type' => 'status_change',
                            'subject' => "Status Diperbarui ke {$statusLabel}",
                            'description' => "Hasil follow-up dicatat: \"{$resultNotes}\". Status prospek otomatis diperbarui menjadi {$statusLabel}.",
                            'activity_at' => now(),
                        ]);
                    }
                }
            }

            unset($data['type'], $data['scheduled_at'], $data['notes']);
        }

        if ($resource === 'leads') {
            if (array_key_exists('institution', $data) && empty($data['company'])) {
                $data['company'] = $data['institution'];
            }
            if (isset($data['phone']) && empty($data['whatsapp'])) {
                $data['whatsapp'] = $data['phone'];
            } elseif (isset($data['whatsapp']) && empty($data['phone'])) {
                $data['phone'] = $data['whatsapp'];
            }

            // Penanganan konsistensi status pipeline prospek
            if (isset($data['status']) && $data['status'] !== $record->status) {
                if ($data['status'] === 'lost') {
                    $data['lost_at'] = now();
                } else {
                    $data['lost_at'] = null;
                    $data['rejection_reason_id'] = null;
                    $data['rejection_notes'] = null;
                }

                if ($data['status'] === 'won' && empty($record->converted_at)) {
                    $data['converted_at'] = now();
                }

                $statusLabel = match ($data['status']) {
                    'new' => 'Baru',
                    'contacted' => 'Dikontak',
                    'active' => 'Aktif',
                    'negotiation' => 'Negosiasi',
                    'won' => 'Berhasil (Closing)',
                    'lost' => 'Ditolak (Lost)',
                    'inactive' => 'Tidak Aktif',
                    default => $data['status'],
                };

                $operatorId = $request->user()?->id ?: (auth()->id() ?: 1);
                $reasonDesc = ($data['status'] === 'lost' && ! empty($data['rejection_notes'])) ? " Alasan: {$data['rejection_notes']}." : '';

                Activity::create([
                    'lead_id' => $record->id,
                    'user_id' => $operatorId,
                    'type' => 'status_change',
                    'subject' => "Status Pipeline Diubah ke {$statusLabel}",
                    'description' => "Status prospek diperbarui melalui form edit data menjadi {$statusLabel}.{$reasonDesc}",
                    'activity_at' => now(),
                ]);
            }
        }

        if ($resource === 'customers' && isset($data['company'])) {
            $inst = Institution::firstOrCreate(['name' => trim($data['company'])]);
            $data['institution_id'] = $inst->id;
        }

        if ($resource === 'leads' && isset($data['assigned_to']) && $record->assigned_to !== (int) $data['assigned_to']) {
            $data['assigned_at'] = now();
            $salesUser = User::find($data['assigned_to']);
            if ($salesUser) {
                $operatorId = $request->user()?->id ?: (auth()->id() ?: User::where('role', 'admin')->value('id'));

                Activity::create([
                    'lead_id' => $record->id,
                    'user_id' => $operatorId,
                    'type' => 'assigned',
                    'subject' => 'Ditugaskan ke Sales: '.$salesUser->name,
                    'description' => 'Admin menugaskan calon pelanggan ini kepada Sales '.$salesUser->name,
                    'activity_at' => now(),
                ]);

                $leadRecord = Lead::with('product')->find($record->id);
                $progName = $leadRecord?->product?->name ?: ($leadRecord?->target_program ?: 'Pelatihan');
                $phone = $leadRecord?->whatsapp ?: ($leadRecord?->phone ?: '-');

                SalesReminder::create([
                    'lead_id' => $record->id,
                    'admin_id' => $operatorId,
                    'sales_id' => $salesUser->id,
                    'message' => 'Admin menugaskan prospek baru "'.$record->name.'" (WA: '.$phone.') untuk program "'.$progName.'". Segera hubungi dan lakukan follow-up.',
                    'priority' => 'normal',
                    'is_read' => false,
                    'is_completed' => false,
                ]);
            }
        }

        if ($resource === 'payments') {
            if (isset($data['status'])) {
                $operatorId = $request->user()?->id ?: (auth()->id() ?: 1);
                $record->processVerification(
                    $data['status'],
                    $operatorId,
                    $data['verification_notes'] ?? null
                );
            }
        }

        DB::transaction(function () use ($record, $data, $resource) {
            $record->update(Arr::only($data, $record->getFillable()));

            if ($resource === 'leads') {
                app(LeadController::class)->syncInstitutionFromCompany($record->company, $record->city, $record->province);
            }
        });

        return response()->json(['data' => $record->refresh()]);
    }

    public function destroy(Request $request, string $resource, int $id): JsonResponse
    {
        $this->authorizeWrite($request, $resource);
        $record = $this->resourceQuery($resource, $request->user())->findOrFail($id);
        $record->delete();

        return response()->json([], 204);
    }

    private function authorizeWrite(Request $request, string $resource): void
    {
        abort_if($resource === 'activities', 403, 'Riwayat aktivitas tidak dapat diubah atau dihapus. Tambahkan catatan melalui detail lead.');
        abort_if(
            in_array($resource, self::ADMIN_WRITE_RESOURCES, true) && ! $request->user()?->isAdmin(),
            403,
            'Akses ditolak. Hanya Admin yang dapat mengubah resource ini.',
        );
    }

    private function validatedAttributes(Request $request, string $resource, ?Model $record = null): array
    {
        $data = $request->validate(CrmResourceRules::for($resource, $record));

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

    private function resourceQuery(string $resource, ?User $user): Builder
    {
        $model = $this->modelFor($resource);
        $query = $model::query();

        if ($resource === 'activities') {
            $query->visibleTo($user);
        }

        if ($user?->isSales()) {
            match ($resource) {
                'users' => abort(403, 'Akses ditolak. Hanya Admin yang dapat mengakses data pengguna.'),
                'customers', 'registrations' => $query->handledBy((int) $user->id),
                'leads' => $query->where('assigned_to', (int) $user->id),
                'payments' => $query->whereHas('lead', fn (Builder $lead) => $lead->where('assigned_to', (int) $user->id)),
                'commissions' => $query->where('user_id', (int) $user->id),
                'follow-ups' => $query->where('assigned_to', (int) $user->id),
                'reminders' => $query->where('sales_id', (int) $user->id),
                default => null,
            };
        }

        return $query;
    }
}
