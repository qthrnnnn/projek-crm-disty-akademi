<?php

namespace App\Providers;

use App\Models\SalesReminder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            if (Auth::check()) {
                /** @var \App\Models\User $user */
                $user = Auth::user();
                if ($user->isSales()) {
                    $unreadRemindersCount = SalesReminder::where('sales_id', $user->id)
                        ->where('is_read', false)
                        ->count();
                    $activeReminders = SalesReminder::with(['lead.product', 'admin', 'commission'])
                        ->where('sales_id', $user->id)
                        ->where(function ($q) {
                            $q->where('is_completed', false)
                              ->orWhere('is_read', false)
                              ->orWhere('type', 'commission_paid');
                        })
                        ->latest()
                        ->take(10)
                        ->get();
                } else {
                    $unreadRemindersCount = 0;
                    $activeReminders = collect();
                }

                $view->with('unreadRemindersCount', $unreadRemindersCount);
                $view->with('activeReminders', $activeReminders);
            }
        });
    }
}
