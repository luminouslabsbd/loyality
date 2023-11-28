<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    /**
     * Display the admin index page.
     *
     * @param string $locale The locale for the current request.
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(string $locale, Request $request)
    {
        // Define a container for dashboard blocks
        $dashboardBlocks = [];

        if (auth('admin')->user()->role != 1) {
            $dashboardBlocks[] = [
                'link' => route('admin.data.list', ['name' => 'account']),
                'icon' => 'user-circle',
                'title' => trans('common.account_settings'),
                'desc' => trans('common.adminDashboardBlocks.account_settings')
            ];
        }

        if (auth('admin')->user()->role == 1) {
            $dashboardBlocks[] = [
                'link' => route('admin.data.list', ['name' => 'admins']),
                'icon' => 'users',
                'title' => trans('common.administrators'),
                'desc' => trans('common.adminDashboardBlocks.administrators', ['localeSlug' => '<span class="underline">/' . app()->make('i18n')->language->current->localeSlug . '/admin/</span>'])
            ];

            $dashboardBlocks[] = [
                'link' => route('admin.data.list', ['name' => 'networks']),
                'icon' => 'cube-transparent',
                'title' => trans('common.networks'),
                'desc' => trans('common.adminDashboardBlocks.networks')
            ];
        }

        $dashboardBlocks[] = [
            'link' => route('admin.data.list', ['name' => 'partners']),
            'icon' => 'building-storefront',
            'title' => trans('common.partners'),
            'desc' => trans('common.adminDashboardBlocks.partners', ['localeSlug' => '<span class="underline">/' . app()->make('i18n')->language->current->localeSlug . '/partner/</span>'])
        ];

        if (auth('admin')->user()->role == 1) {
            $dashboardBlocks[] = [
                'link' => route('admin.data.list', ['name' => 'members']),
                'icon' => 'user-group',
                'title' => trans('common.members'),
                'desc' => trans('common.adminDashboardBlocks.members')
            ];
        }

        // Check if there are pending database migrations
        $hasMigrations = auth('admin')->user()->role == 1 ? $this->hasPendingMigrations() : false;


        // dashboard
        $viewsTableQuery = DB::table('cards')->select('views', 'number_of_points_issued', 'number_of_rewards_redeemed');
        
        $data = $viewsTableQuery->get(['views', 'number_of_points_issued', 'number_of_rewards_redeemed'])->toArray();
        $rewardViews = DB::table('rewards')->select('views as reward_views')->get()->toArray();

        $totalRewardViews = collect($rewardViews)->sum('reward_views');
        
        $totalCards = $viewsTableQuery->count() ?? 0;
        
        // Define the attributes you want to sum
        $attributes = ['views', 'number_of_points_issued', 'number_of_rewards_redeemed'];
        
        // Initialize an array to store the sums
        $cardsSums = array_fill_keys($attributes, 0);
        
        // Use array_map to iterate through the array and sum up the values for each attribute
        array_map(function ($item) use (&$cardsSums) {
            foreach ($cardsSums as $attribute => &$cardsSums) {
                $cardsSums += $item->$attribute;
            }
        }, $data);
        
        $staffsTotal = DB::table('staff')->count();
        $membersTotal = DB::table('members')->count();
        $totalPartners = DB::table('partners')->count();
        

        return view('admin.index', compact('totalCards', 'totalRewardViews','cardsSums','staffsTotal','membersTotal', 'totalPartners'));
    }

    /**
     * Check if there are pending database migrations.
     *
     * @return bool
     */
    private function hasPendingMigrations(): bool
    {
        $repository = new DatabaseMigrationRepository(app('db'), config('database.migrations'));

        $ran = $repository->getRan();
        $migrations = $files = app('migrator')->getMigrationFiles(database_path('migrations'));
        $pendingMigrations = array_diff(array_keys($migrations), $ran);

        return !empty($pendingMigrations);
    }

    /**
     * Run database migration.
     *
     * @param string $locale The locale for the current request.
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function runMigrations(string $locale, Request $request): RedirectResponse
    {
        try {
            $this->ensureConsoleOutputConstantsDefined();

            // Run the database migrations
            Artisan::call('migrate', ['--force' => true]); 

            // Create a toast message for the result of the migrations
            $toast = [
                'type' => 'success',
                'size' => 'lg',
                'text' => trans('common.database_migrations_success'),
            ];
        } catch (\Exception $e) {
            // Report any exceptions that occur during the migration
            report($e); 

            // Create a toast message for the result of the migrations
            $toast = [
                'type' => 'warning',
                'size' => 'lg',
                'text' => trans('common.database_migrations_failure'),
            ];
        }

        // Redirect to the admin index page with the toast message
        return redirect(route('admin.index'))->with('toast', $toast);
    }

    /**
     * Ensure the STDOUT, STDERR, and STDIN constants are defined for Artisan.
     *
     * @return void
     */
    private function ensureConsoleOutputConstantsDefined(): void
    {
        if (!defined('STDIN')) {
            define('STDIN', fopen('php://stdin', 'rb'));
        }

        if (!defined('STDOUT')) {
            define('STDOUT', fopen('php://stdout', 'wb'));
        }

        if (!defined('STDERR')) {
            define('STDERR', fopen('php://stderr', 'wb'));
        }
    }
}
