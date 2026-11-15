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
use App\Support\Jwt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            ->update([
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
                // Auto-sync: HANYA pendaftaran yang pembayarannya SUDAH VALID di-ACC admin yang berstatus confirmed
                Registration::where('status', 'registered')
                    ->whereHas('customer.lead.payments', function ($pq) {
                        $pq->where('status', 'valid');
                    })->update(['status' => 'confirmed']);

                $query->with(['customer.lead.assignedUser', 'user', 'product', 'schedule.product', 'schedule.trainer'])->latest();

                // Pembatasan akses: Akun Sales hanya melihat pendaftaran peserta yang ditanganinya / didaftarkannya
                if ($request->user() && $request->user()->isSales()) {
                    $userId = $request->user()->id;
                    $query->where(function ($sub) use ($userId) {
                        $sub->where('user_id', $userId)
                            ->orWhereHas('customer.lead', fn ($l) => $l->where('assigned_to', $userId));
                    });
                } elseif ($request->filled('sales_id')) {
                    $salesId = $request->sales_id;
                    $query->where(function ($sub) use ($salesId) {
                        $sub->where('user_id', $salesId)
                            ->orWhereHas('customer.lead', fn ($l) => $l->where('assigned_to', $salesId));
                    });
                }

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
                    // Hanya calon peserta yang sudah didaftarkan ke produk & belum memiliki pembayaran valid
                    $query->whereNotNull('product_id')
                        ->whereNotIn('status', ['lost', 'inactive'])
                        ->whereDoesntHave('payments', function ($p) {
                            $p->where('status', 'valid');
                        });
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

                // Pembatasan akses: Akun Sales hanya melihat customer dari lead yang ditugaskan kepadanya atau pendaftaran miliknya
                if ($request->user() && $request->user()->isSales()) {
                    $userId = $request->user()->id;
                    $query->where(function ($sub) use ($userId) {
                        $sub->whereHas('lead', fn ($l) => $l->where('assigned_to', $userId))
                            ->orWhereHas('registrations', fn ($r) => $r->where('user_id', $userId));
                    });
                }

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
                $query->withCount('customers')->latest();
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

        $perPage = min(max($request->integer('per_page', 20), 1), 100);

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
                $query->with(['customer.lead.assignedUser', 'user', 'product', 'schedule.product', 'schedule.trainer']);
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
                $query->with(['user', 'lead.product', 'payment', 'lead.payments']);
                break;
            case 'follow-ups':
                $query->with(['lead.product', 'assignedUser']);
                break;
            case 'customers':
                $query->with(['institution', 'lead.product', 'registrations.product']);
                break;
            case 'institutions':
                $query->with(['customers'])->withCount('customers');
                break;
            case 'reminders':
                $query->with(['lead.product', 'admin', 'sales']);
                break;
        }

        $record = $query->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $record,
        ]);
    }

    public function store(Request $request, string $resource): JsonResponse
    {
        $model = $this->modelFor($resource);
        $data = $this->validatedAttributes($request, $model);

        if ($resource === 'follow-ups') {
            $leadId = $request->input('lead_id') ?? ($data['lead_id'] ?? null);
            $lead = $leadId ? Lead::find($leadId) : null;

            if ($request->filled('type') && empty($data['method'])) {
                $data['method'] = $request->input('type');
            }
            if ($request->filled('scheduled_at') && empty($data['follow_up_at'])) {
                $data['follow_up_at'] = $request->input('scheduled_at');
            }
            if ($request->filled('notes') && empty($data['description'])) {
                $data['description'] = $request->input('notes');
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
            if ($request->filled('institution') && empty($data['company'])) {
                $data['company'] = $request->institution;
            }
            if ($request->filled('phone') && empty($data['whatsapp'])) {
                $data['whatsapp'] = $request->phone;
            } elseif ($request->filled('whatsapp') && empty($data['phone'])) {
                $data['phone'] = $request->whatsapp;
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
            if ($request->filled('company') && ! isset($data['institution_id'])) {
                $inst = Institution::firstOrCreate(['name' => trim($request->company)]);
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
            if ($request->input('participant_mode') === 'manual' || $request->filled('participant_name')) {
                $operatorId = $request->user()?->id ?: (auth()->id() ?: 1);
                $data['user_id'] = $operatorId;
                $name = $request->input('participant_name');
                $phone = $request->input('participant_phone') ?: '-';
                $nik = $request->input('participant_nik');
                $birthDate = $request->input('participant_birth_date');
                $company = $request->input('participant_company');

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
                    'status' => 'active',
                    'source' => 'direct_registration',
                    'notes' => 'Didaftarkan langsung dari Form Pendaftaran Peserta',
                ]);

                $data['customer_id'] = $customer->id;
            }
            // Jika pendaftaran berasal dari calon peserta (Lead / Prospek)
            elseif (! empty($leadId = $request->input('lead_id') ?? ($data['lead_id'] ?? null))) {
                $lead = Lead::find($leadId);
                if ($lead) {
                    if (! empty($lead->assigned_to)) {
                        $data['user_id'] = $lead->assigned_to;
                    }
                    $customer = app(LeadController::class)->ensureCustomerCreated($lead);
                    $data['customer_id'] = $customer->id;

                    // Status pendaftaran HANYA boleh confirmed dan lead HANYA boleh won jika pembayaran SUDAH VALID diverifikasi / di-ACC admin
                    $hasValidPayment = Payment::where('lead_id', $lead->id)->where('status', 'valid')->exists()
                        || $lead->payments()->where('status', 'valid')->exists();

                    if ($hasValidPayment) {
                        $data['status'] = in_array($data['status'] ?? '', ['attended', 'completed']) ? $data['status'] : 'confirmed';
                        $customer->update(['status' => 'active']);
                        $leadUpdate = [
                            'status' => 'won',
                            'converted_at' => $lead->converted_at ?: now(),
                        ];
                        if (! empty($data['product_id'])) {
                            $leadUpdate['product_id'] = $data['product_id'];
                        }
                        $lead->update($leadUpdate);

                        // Otomatis selesaikan follow-up & pengingat pending untuk lead ini
                        $lead->followUps()->where('status', 'pending')->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                            'result_notes' => 'Otomatis selesai: Pendaftaran terkonfirmasi',
                        ]);

                        SalesReminder::where('lead_id', $lead->id)
                            ->where('type', 'follow_up')
                            ->where('is_completed', false)
                            ->update([
                                'is_completed' => true,
                                'completed_at' => now(),
                                'sales_reply' => 'Selesai: Pendaftaran terkonfirmasi',
                            ]);

                        Activity::create([
                            'lead_id' => $lead->id,
                            'user_id' => auth()->id() ?? $lead->assigned_to,
                            'type' => 'status_change',
                            'subject' => 'Pendaftaran Terkonfirmasi - Resmi Menjadi Customer',
                            'description' => 'Peserta didaftarkan dengan status terkonfirmasi dan resmi masuk ke Daftar Customer (Kode: '.$customer->customer_code.').',
                            'activity_at' => now(),
                        ]);
                    } elseif (($data['status'] ?? '') === 'cancelled') {
                        $customer->update(['status' => 'inactive']);
                        $lead->update(['status' => 'lost']);
                    } else {
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
                        if ($request->filled('amount') && (float) $request->amount > 0) {
                            $leadUpdate['deal_price'] = (float) $request->amount;
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
            // Jika pendaftaran berasal dari Customer Terdaftar
            elseif (! empty($custId = $request->input('customer_id') ?? ($data['customer_id'] ?? null))) {
                $customer = Customer::find($custId);
                if ($customer) {
                    $lead = $customer->lead;
                    if ($lead && ! empty($lead->assigned_to)) {
                        $data['user_id'] = $lead->assigned_to;
                    }
                    $hasValidPayment = $lead && ($lead->payments()->where('status', 'valid')->exists() || Payment::where('lead_id', $lead->id)->where('status', 'valid')->exists());

                    if ($hasValidPayment) {
                        $customer->update(['status' => 'active']);
                        if ($lead) {
                            $lead->update([
                                'product_id' => $data['product_id'] ?? $lead->product_id,
                                'deal_price' => $data['amount'] ?? $lead->deal_price,
                                'status' => 'won',
                            ]);
                        }
                        if (! isset($data['status']) || $data['status'] === 'registered') {
                            $data['status'] = 'confirmed';
                        }
                    } else {
                        $customer->update(['status' => 'prospect']);
                        $data['status'] = 'registered';
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
                        } else {
                            $lead->update([
                                'product_id' => $data['product_id'] ?? $lead->product_id,
                                'deal_price' => $data['amount'] ?? $lead->deal_price,
                                'status' => 'negotiation',
                            ]);
                        }
                    }
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
            if ($request->filled('sender_name') && empty($data['account_name'])) {
                $data['account_name'] = $request->sender_name;
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
            elseif (! empty($custId = $request->input('customer_id') ?? ($data['customer_id'] ?? null))) {
                $customer = Customer::find($custId);
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

        $record = $model::create($data);

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
        $model = $this->modelFor($resource);
        $record = $model::query()->findOrFail($id);
        $data = $this->validatedAttributes($request, $model);

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
            $leadId = $request->input('lead_id') ?? ($data['lead_id'] ?? $record->lead_id);
            $lead = $leadId ? Lead::find($leadId) : null;

            if ($request->filled('type') && empty($data['method'])) {
                $data['method'] = $request->input('type');
            }
            if ($request->filled('scheduled_at') && empty($data['follow_up_at'])) {
                $data['follow_up_at'] = $request->input('scheduled_at');
            }
            if ($request->filled('notes') && empty($data['description'])) {
                $data['description'] = $request->input('notes');
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

                $rejectionReasonId = $request->input('rejection_reason_id');
                $leadStatus = $request->input('lead_status');
                $feedbackType = $request->input('feedback_type');
                $resultNotes = $request->input('result_notes') ?? ($data['result_notes'] ?? null);

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
            if ($request->has('institution') && empty($data['company'])) {
                $data['company'] = $request->institution;
            }
            if ($request->filled('phone') && empty($data['whatsapp'])) {
                $data['whatsapp'] = $request->phone;
            } elseif ($request->filled('whatsapp') && empty($data['phone'])) {
                $data['phone'] = $request->whatsapp;
            }

            // Penanganan konsistensi status pipeline prospek
            if (isset($data['status']) && $data['status'] !== $record->status) {
                if ($data['status'] === 'lost') {
                    $data['lost_at'] = now();
                    if ($request->filled('rejection_reason_id')) {
                        $data['rejection_reason_id'] = $request->input('rejection_reason_id');
                    }
                    if ($request->filled('rejection_notes')) {
                        $data['rejection_notes'] = $request->input('rejection_notes');
                    }
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

        if ($resource === 'customers' && $request->filled('company')) {
            $inst = Institution::firstOrCreate(['name' => trim($request->company)]);
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
