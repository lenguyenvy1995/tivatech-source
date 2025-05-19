<?php

use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\QuoteRequestController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\QuoteDomainController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RolePermissionController;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\serpApi\GGSearchController;
use App\Http\Controllers\api\ZaloWebhookController;
use  App\Http\Controllers\AttendanceController;
use  App\Http\Controllers\MessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\OtherCostController;
use App\Http\Controllers\KpiController;
use App\Http\Controllers\DataCustomerController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\HostingController;
use App\Http\Controllers\DesignWebsiteController;
use App\Http\Controllers\KeywordController;
use App\Http\Controllers\KeywordPlannerController;
use App\Http\Controllers\GoogleAdsSearchController;
use App\Http\Controllers\AdsTransparencyController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
//thêm quyền setup quản cáo
// Route::get('/assign-permission', function () {
//     try {
//         // Tìm role và permission
//         Permission::create(['name' => 'setup ads']);
//         $role = Role::findByName('saler');
//         $permission = Permission::findByName('setup ads');

//         // Gán permission cho role
//         $role->givePermissionTo($permission);

//         return response()->json(['message' => 'Permission assigned successfully!']);
//     } catch (\Exception $e) {
//         return response()->json(['error' => $e->getMessage()], 500);
//     }
// });
// Route::get('/assign-permission', function () {
//     try {
//         // Tìm role và permission
//         // Permission::create(['name' => 'google ads']);
//         // $role = Role::findByName('admin');
//         $role = Role::findByName('techads');
//         $permission = Permission::findByName('google ads');
//         // Gán permission cho role
//         $role->givePermissionTo($permission);

//         return response()->json(['message' => 'Permission assigned successfully!']);
//     } catch (\Exception $e) {
//         return response()->json(['error' => $e->getMessage()], 500);
//     }
// });
Route::get('/check-permissions', function () {
    return Permission::all(); // hoặc Permission::count()
});
Route::get('/bao-gia/{id}', [QuoteRequestController::class, 'show'])->name('baogia');
Route::post('/api/google-sheet-webhook', function (Request $request) {
    // Log thông tin thay đổi từ webhook (nếu cần kiểm tra)
    Log::info('Google Sheet Updated', $request->all());

    // Trả về phản hồi cho Google Sheets
    return response()->json(['success' => true], 200);
});
// Routes dành cho Admin, chỉ có Role 'admin' mới truy cập được
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Quản lý Permissions
    Route::resource('permissions', PermissionController::class);
    // Quản lý người dùng
    Route::resource('users', UserController::class);

    // Quản lý Roles
    // Quản lý người dùng
    Route::resource('users', UserController::class);

    // Quản lý Roles
    //roles_permisson
    Route::get('/roles', [RolePermissionController::class, 'index'])->name('roles.index');
    Route::get('/roles/data', [RolePermissionController::class, 'getData'])->name('roles.data');
    Route::resource('roles', RoleController::class);
});
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect('/dashboard');
    })->name('home');
    //kế hoạch từ khoá
    Route::get('keyword-planner', [KeywordPlannerController::class, 'index'])->name('keyword-planner.index');
    Route::post('keyword-planner/search', [KeywordPlannerController::class, 'search'])->name('keyword-planner.search');
    //google ads search spi
    Route::get('/ads-search', [GoogleAdsSearchController::class, 'index'])->name('ads.search');
    Route::post('/ads-search/fetch', [GoogleAdsSearchController::class, 'ajax'])->name('ads.ajax');    //google sheet api
    //google ads search transparency
    Route::get('/ads-transparency', [AdsTransparencyController::class, 'index'])->name('ads.transparency');
    Route::post('/ads-transparency/search', [AdsTransparencyController::class, 'search'])->name('ads.transparency.search');
    Route::post('/ads-transparency/ajax', [AdsTransparencyController::class, 'ajax'])->name('ads.transparency.ajax');
    Route::get('/ads-transparency/suggest', [AdsTransparencyController::class, 'suggest'])->name('ads.transparency.suggest');
    //hiệu suất
    Route::get('/intraday-performance', [AdminController::class, 'intradayPerformance'])->name('intradayPerformance');
    //hiệu suất theo ngày nhập vào
    Route::get('/date-performance', [AdminController::class, 'datePerformance'])->name('datePerformance');

    Route::post('/messages', [MessageController::class, 'store'])->name('messages');

    Route::get('/dashboard', function () {
        return view('welcome');
    })->name('admin.home');
    // Dashboard
    Route::get('dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/api/monthly-sales', [AdminController::class, 'getMonthlySalesData'])->name('api.monthly-sales');
    Route::get('/api/weekly-budget-comparison', [AdminController::class, 'getWeeklyBudgetComparison'])->name('api.weekly-budget-comparison');

    Route::get('/api/quote-requests', [QuoteRequestController::class, 'getData'])->name('quote-requests.data');
    Route::get('/keywords/random', [KeywordController::class, 'getRandomKeywords'])->name('keywords.random'); //route cho random hiển thị từ khoá 
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('keywords', KeywordController::class);
    });    
    // Routes cho tất cả người dùng đã đăng nhập
    // profile nhân viên
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // Routes cho Saler
    Route::middleware(['role:saler|quote manager|admin'])->group(function () {
        Route::resource('quote-requests', QuoteRequestController::class);
    });
    // Routes cho Bộ phận báo giá
    Route::middleware(['role:quote manager|admin'])->group(function () {
        Route::get('all-quote-requests', [QuoteRequestController::class, 'allRequests'])->name('quote-requests.all');
        Route::get('quotes/create/{quote_request_id}', [QuoteController::class, 'create'])->name('quotes.create');
        Route::resource('quotes', QuoteController::class)->except(['create']);
        Route::get('zalo/{id}', [QuoteController::class, 'update']);
        //từ chối báo giá
        Route::patch('/quote-requests/{id}/reject', [QuoteRequestController::class, 'reject'])->name('quote-requests.reject');
    });
    //quản lý thiét kế website 
    Route::resource('domains', DomainController::class);
    Route::resource('hostings', HostingController::class);
    //design-website
    Route::get('design-websites/data', [DesignWebsiteController::class, 'getData'])->name('design-websites.data');
    Route::post('design-websites/{designWebsite}/update-status', [App\Http\Controllers\DesignWebsiteController::class, 'updateStatus'])->name('design-websites.update-status');
    Route::post('design-websites/{designWebsite}/update-note', [App\Http\Controllers\DesignWebsiteController::class, 'updateNote'])->name('design-websites.update-note');
    Route::resource('design-websites', DesignWebsiteController::class);
    // thiết kế website 
    Route::get('/quote-domains', [QuoteDomainController::class, 'index'])->name('quote-domains.index');
    Route::get('/quote-domains/create', [QuoteDomainController::class, 'create'])->name('quote-domains.create');
    Route::post('/quote-domains', [QuoteDomainController::class, 'store'])->name('quote-domains.store');
    //hệ thông chấm công nhân viên
    Route::get('/attendance/show-list', [AttendanceController::class, 'showlist'])->name('attendance.showlist');
    Route::post('/attendance/checkin', [AttendanceController::class, 'checkIn'])->name('attendance.checkin');
    Route::get('/attendance/getworkdays', [AttendanceController::class, 'calculateMonthlyWorkDays'])->name('attendance.getWorkDays');
    Route::post('/attendance/checkout/{id}', [AttendanceController::class, 'checkOut'])->name('attendance.checkout');
    Route::get('/attendance/{user}/detail', [AttendanceController::class, 'showDetail'])->name('attendance.detail');
    Route::get('/attendance/list', [AttendanceController::class, 'listByEmployeeAndMonth'])->name('attendance.listByEmployeeAndMonth');
    Route::resource('attendance', AttendanceController::class);
    //google ads
    Route::middleware(['role:quote manager|admin|saler|manager|techads'])->group(function () {
        Route::prefix('websites')->name('websites.')->group(function (): void {
            Route::get('/inactive-campaigns', [WebsiteController::class, 'inactiveCampaigns'])->name('inactive-campaigns');
            Route::view('/check', 'websites.check')->name('check'); // hiển thị view check website
            Route::post('/search-domain', [WebsiteController::class, 'check'])->name('check_post'); //gửi domain check 
            Route::get('{website}/campaigns', [CampaignController::class, 'index'])->name('campaigns'); //list campaign theo website
        });
        Route::resource('websites', WebsiteController::class);

        // data khách hàng tìm kiếm yêu cầu
        Route::prefix('data-customers')->name('dataCustomers.')->group(function (): void {
            Route::get('/', [DataCustomerController::class, 'index'])->name('index');
            Route::post('/store', [DataCustomerController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [DataCustomerController::class, 'edit'])->name('edit');
            Route::post('/{id}/update', [DataCustomerController::class, 'update'])->name('update');
            Route::delete('/{id}', [DataCustomerController::class, 'destroy'])->name('destroy');
        });

        Route::get('campaigns', [CampaignController::class, 'list'])->name('campaigns'); //list campaign theo website
        Route::post('/campaigns/update-status', [CampaignController::class, 'updateStatus'])->name('campaigns.updateStatus');
        Route::post('/campaigns/update-vat', [CampaignController::class, 'updateVat'])->name('campaigns.updateVat');
        Route::post('/campaigns/update-paid', [CampaignController::class, 'updatePaid'])->name('campaigns.updatePaid');
        Route::get('/campaigns/create', [CampaignController::class, 'create'])->name('campaigns.create');
        Route::post('/campaigns/store', [CampaignController::class, 'store'])->name('campaigns.store');
        Route::get('/campaigns/{id}/renew', [CampaignController::class, 'showRenewForm'])->name('campaigns.renew'); //gia hạn
        Route::get('/campaigns/search', [CampaignController::class, 'search'])->name('campaigns.search');

        //list camp tính lương dự kiến
        Route::get('/campaigns/monthly-sales', [CampaignController::class, 'getMonthlySalesData'])->name('campaigns.monthlySales');
        Route::get('/campaigns/{campaign}/note', [CampaignController::class, 'getNote']);
        //ghi chú note
        Route::get('/campaigns/{campaign}/note/list', [NoteController::class, 'index'])->name('campaigns.listNote');
        Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
        Route::put('/notes/{note}', [NoteController::class, 'update'])->name('notes.update');
        Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');
        Route::get('/campaigns/setups', [CampaignController::class, 'setups'])->name('campaigns.setups');
        Route::post('/campaigns/{id}/setup', [CampaignController::class, 'markAsSetup'])->name('campaigns.setup');

        Route::get('/campaigns/{id}', [CampaignController::class, 'show'])->name('campaigns.show');
        Route::get('/campaigns/{id}/edit', [CampaignController::class, 'edit'])->name('campaigns.edit');
        Route::put('/campaigns/{id}/update', [CampaignController::class, 'update'])->name('campaigns.update');
        Route::delete('/campaigns/{id}/delete', [CampaignController::class, 'destroy'])->name('campaigns.destroy');
        Route::get('/campaigns/{campaignsId}/budgets', [CampaignController::class, 'showBudgets'])->name('campaigns.budgets');

        Route::post('/budgets/update-calu', [BudgetController::class, 'updateCalu'])->name('budgets.updateCalu');
        Route::post('/budgets/import', [BudgetController::class, 'importFromGoogleSheets'])->name('budgets.import');
        Route::resource('/budgets', BudgetController::class);
    });
});
Route::prefix('salaries')->name('salaries.')->group(function (): void {
    // Các route yêu cầu quyền admin
    Route::middleware(['auth', 'role:admin'])->group(function (): void {
        Route::post('/store', [SalaryController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [SalaryController::class, 'edit'])->name('edit');
        Route::put('/{id}', [SalaryController::class, 'update'])->name('update');
        Route::delete('/{id}', [SalaryController::class, 'destroy'])->name('destroy');
    });

    // Route chỉ yêu cầu đăng nhập
    Route::middleware(['auth'])->group(function (): void {
        Route::get('/', [SalaryController::class, 'index'])->name('index');
        Route::post('/em-confirm', [SalaryController::class, 'update_em_confirm'])->name('update_em_confirm');
        Route::get('/{id}', [SalaryController::class, 'show'])->name('show');
    });
});
//lấy thông tin tính lương dự kiến cho từng nhân viên
Route::get('/expected-salaries', [SalaryController::class, 'expected'])->name('salaries.expected');

// Hiển thị form tính lương
Route::get('/calculate-salary', [SalaryController::class, 'showCalculateForm'])->name('salaries.show.calculate');
Route::post('/calculate-salary', [SalaryController::class, 'calculateSalary'])->name('salaries.calculate');
Route::get('/calculate-salary/get-kpi', [SalaryController::class, 'getKpi'])->name('salaries.getKpi');

Route::prefix('other-costs')->group(function () {
    Route::get('/', [OtherCostController::class, 'index'])->name('other-costs.index');
    Route::get('/get-other-costs', [OtherCostController::class, 'getOtherCosts'])->name('other-costs.getOtherCosts');
    Route::post('/store', [OtherCostController::class, 'store'])->name('other-costs.store');
    Route::get('/edit/{id}', [OtherCostController::class, 'edit'])->name('other-costs.edit');
    Route::put('/update/{id}', [OtherCostController::class, 'update'])->name('other-costs.update');
    Route::delete('/delete/{id}', [OtherCostController::class, 'destroy'])->name('other-costs.destroy');
});
// Route nhóm cho KPI
Route::prefix('kpis')->name('kpis.')->group(function () {
    // Route::get('/', [KpiController::class, 'index'])->name('index'); // Lấy danh sách KPI
    Route::get('/data', [KpiController::class, 'getData'])->name('data');
    Route::post('/', [KpiController::class, 'store'])->name('store'); // Thêm KPI
    Route::get('/{id}/show', [KpiController::class, 'show'])->name('show'); // Sửa KPI
    Route::put('/{id}', [KpiController::class, 'update'])->name('update'); // Sửa KPI
    Route::delete('/{id}', [KpiController::class, 'destroy'])->name('destroy'); // Xóa KPI
});
Route::get('/quote-domains/{domainName}/quotes/count', [QuoteController::class, 'countQuotesByQuoteDomain'])
    ->name('quotes.countByQuoteDomain')
    ->middleware(['auth']);
Route::get('/ggsearch', [GGSearchController::class, 'search'])->name('serpapi');
Route::get('/ggsearch/form', function () {
    return view('serpapi.form');
})->name('serpapi.form');

Route::post('/zalo-webhook', [ZaloWebhookController::class, 'handleWebhook']);

require __DIR__ . '/auth.php';
