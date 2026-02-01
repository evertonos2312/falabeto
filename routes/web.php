<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\CheckoutMock;
use App\Livewire\Dashboard\Debts;
use App\Livewire\Dashboard\Home as DashboardHome;
use App\Livewire\Dashboard\Transactions as DashboardTransactions;
use App\Livewire\EmailVerify;
use App\Livewire\Landing;
use App\Livewire\PlanSelector;
use App\Livewire\SuccessPage;
use App\Livewire\SubscriptionShow;
use App\Livewire\WhatsappVerify;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', Landing::class)->name('home');
Route::view('/terms', 'terms')->name('terms');
Route::view('/privacy', 'privacy')->name('privacy');

Route::middleware('guest:client')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});

Route::post('/logout', function (Request $request) {
    Auth::guard('client')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('home');
})->middleware('auth:client')->name('logout');

Route::middleware(['auth:client', 'onboarding'])->group(function () {
    Route::get('/whatsapp/verify', WhatsappVerify::class)->name('whatsapp.verify');
    Route::post('/whatsapp/verify', function () {
        return redirect()->route('whatsapp.verify');
    })->name('whatsapp.verify.post');

    Route::get('/email/verify', EmailVerify::class)->name('email.verify');

    Route::get('/plans', PlanSelector::class)->name('plans');

    Route::post('/checkout/mock', function (Request $request) {
        $validated = $request->validate([
            'plan_code' => ['required', 'exists:plans,code'],
            'coupon_code' => ['nullable', 'string', 'max:50', 'regex:/^[A-Z0-9_]+$/i'],
        ]);

        $plan = Plan::query()->where('code', $validated['plan_code'])->firstOrFail();

        session([
            'selected_plan_code' => $plan->code,
            'selected_coupon_code' => isset($validated['coupon_code']) ? strtoupper($validated['coupon_code']) : null,
        ]);

        return redirect()->route('checkout.mock');
    })->name('checkout.mock.post');

    Route::get('/checkout/mock', CheckoutMock::class)->name('checkout.mock');

    Route::get('/success', SuccessPage::class)->name('success');

    Route::middleware(['subscription.active'])->group(function () {
        Route::get('/dashboard', DashboardHome::class)->name('dashboard');
        Route::get('/transactions', DashboardTransactions::class)->name('transactions');
        Route::get('/debts', Debts::class)->name('debts');
        Route::get('/dashboard/subscription', SubscriptionShow::class)->name('subscription.show');
    });
});
