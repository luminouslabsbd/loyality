<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class PageController extends Controller
{
    /**
     * Display the partner index page.
     *
     * @param string $locale
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(string $locale, Request $request): \Illuminate\View\View
    {
        $dashboardBlocks = [];
        /*
        $dashboardBlocks[] = [
            'link' => route('partner.data.list', ['name' => 'account']),
            'icon' => 'user-circle',
            'title' => trans('common.account_settings'),
            'desc' => trans('common.memberDashboardBlocks.account_settings')
        ];
        */

        $dashboardBlocks[] = [
            'link' => route('partner.data.list', ['name' => 'clubs']),
            'icon' => 'funnel',
            'title' => trans('common.clubs'),
            'desc' => trans('common.partnerDashboardBlocks.clubs')
        ];

        $dashboardBlocks[] = [
            'link' => route('partner.data.list', ['name' => 'cards']),
            'icon' => 'qr-code',
            'title' => trans('common.loyalty_cards'),
            'desc' => trans('common.partnerDashboardBlocks.loyalty_cards')
        ];

        $dashboardBlocks[] = [
            'link' => route('partner.data.list', ['name' => 'rewards']),
            'icon' => 'gift',
            'title' => trans('common.rewards'),
            'desc' => trans('common.partnerDashboardBlocks.rewards')
        ];

        $dashboardBlocks[] = [
            'link' => route('partner.data.list', ['name' => 'staff']),
            'icon' => 'briefcase',
            'title' => trans('common.staff'),
            'desc' => trans('common.partnerDashboardBlocks.staff', ['localeSlug' => '<span class="underline">/' . app()->make('i18n')->language->current->localeSlug . '/staff/</span>'])
        ];

        $dashboardBlocks[] = [
            'link' => route('partner.data.list', ['name' => 'members']),
            'icon' => 'user-group',
            'title' => trans('common.members'),
            'desc' => trans('common.partnerDashboardBlocks.members')
        ];

        $dashboardBlocks[] = [
            'link' => route('partner.analytics'),
            'icon' => 'presentation-chart-line',
            'title' => trans('common.analytics'),
            'desc' => trans('common.partnerDashboardBlocks.analytics')
        ];

        // dahsboard
        $userId = auth('partner')->user()->id;
        // $userId  = 75285326327808;

        $viewsTableQuery = DB::table('cards')->where('created_by', $userId)->select('views', 'number_of_points_issued', 'number_of_rewards_redeemed');

        $data = $viewsTableQuery->get(['views', 'number_of_points_issued', 'number_of_rewards_redeemed'])->toArray();

        $countDatas['totalCards'] = $viewsTableQuery->count() ?? 0;

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

        $staffsTotal = DB::table('staff')->where('created_by',$userId)->count();
        $membersTotal = DB::table('members')->where('created_by',$userId)->count();
        $totalPartners = DB::table('partners')->count();

        $countDatas['rewardViews'] = DB::table('rewards')->where('created_by', $userId)->first('views')->views ?? 0;

        return view('partner.index', compact('countDatas','cardsSums','staffsTotal','membersTotal', 'totalPartners'));
    }
}
