@extends('partner.layouts.default')

@section('page_title', trans('common.partner') . config('default.page_title_delimiter') . trans('common.dashboard') . config('default.page_title_delimiter') . config('default.app_name'))

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<section class="">
    {{-- <div class="py-8 px-4 mx-auto sm:py-16 lg:px-6">
        <div class="mx-auto max-w-screen-md text-center mb-8 lg:mb-16">
            <h2 class="mb-4 text-4xl tracking-tight font-extrabold text-gray-900 dark:text-white">{{ trans('common.welcome_user', ['user' => trans('common.partner')]) }} {{ Arr::random(['😎', '🤠', '😊', '😃', '🙃', '🤩', '😉']) }}</h2>
            <p class="font-light text-gray-500 dark:text-gray-400 sm:text-xl">{!! trans('common.partnerDashboardBlocksTitle') !!}</p>
        </div>

        <div class="ll-dashboard-grid space-y-8 md:grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 md:gap-8 xl:gap-8 md:space-y-0">
            @foreach($dashboardBlocks as $block)
            <a href="{{ $block['link'] }}" class="group block p-6 bg-white rounded shadow dark:bg-gray-800">
                <div class="ll-dashboard-icon-wrapper flex justify-center items-center mb-4 w-10 h-10 rounded bg-primary-100 lg:h-12 lg:w-12 dark:bg-primary-900">
                    <x-ui.icon :icon="$block['icon']" class="w-5 h-5 text-primary-600 lg:w-6 lg:h-6 dark:text-primary-300" />
                </div>
                <h3 class="mb-2 text-xl font-bold dark:text-white group-hover:underline">{!! $block['title'] !!}</h3>
                <p class="font-light text-gray-500 dark:text-gray-400">{!! $block['desc'] !!}</p>
            </a>
            @endforeach
        </div>
    </div> --}}

    <div class="px-4 lg:px-6 py-4 lg:py-6 mx-auto">
        <div class="ll-dashboard-info-card-contaienr space-y-8 md:grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 md:gap-7 xl:gap-7 md:space-y-0">
            <div class="ll-dashboard-info-card">
                <h3 class="font-semibold mb-3">Card Views</h3>
                <h1 class="font-extrabold text-3xl">{{ $cardsSums['views'] ?? 0 }}</h1>
            </div>

            <div class="ll-dashboard-info-card">
                <h3 class="font-semibold mb-3">Reward Views</h3>
                <h1 class="font-extrabold text-3xl">{{ $countDatas["rewardViews"] }}</h1>
            </div>

            <div class="ll-dashboard-info-card">
                <h3 class="font-semibold mb-3">Points Issued</h3>
                <h1 class="font-extrabold text-3xl">{{ $cardsSums['number_of_points_issued'] ?? 0 }}</h1>
            </div>

            <div class="ll-dashboard-info-card">
                <h3 class="font-semibold mb-3">Rewards Claimed</h3>
                <h1 class="font-extrabold text-3xl">{{ $cardsSums['number_of_rewards_redeemed'] ?? 0 }}</h1>
            </div>

            <div class="ll-dashboard-info-card">
                <h3 class="font-semibold mb-3">Total Points</h3>
                <h1 class="font-extrabold text-3xl">{{ $totalPartners ?? 0 }}</h1>
            </div>

            <div class="ll-dashboard-info-card">
                <h3 class="font-semibold mb-3">Total Staff</h3>
                <h1 class="font-extrabold text-3xl">{{ $staffsTotal ?? 0 }}</h1>
            </div>

            <div class="ll-dashboard-info-card">
                <h3 class="font-semibold mb-3">Total Members</h3>
                <h1 class="font-extrabold text-3xl">{{ $membersTotal ?? 0 }}</h1>
            </div>

            <div class="ll-dashboard-info-card">
                <h3 class="font-semibold mb-3">Total Cards</h3>
                <h1 class="font-extrabold text-3xl">{{ $countDatas["totalCards"] ?? 0 }}</h1>
            </div>
        </div>

        <div class="ll-dashboard-chart-container mt-8">
            <div class="ll-dashboard-chart-heading mb-7 flex justify-between items-center">
                <h1 class="font-semibold text-2xl">Performance Over Time</h1>
                <div>
                    <button type="button" class="ll-csv-download-btn">Download CSV</button>
                    <select name="filter" id="filter">
                        <option value="1">Last 7 Days</option>
                        <option value="1">Today</option>
                    </select>
                </div>
            </div>
            <div class="ll-dashboard-chart-body">
                <canvas id="ll-custom-dashboard" style="max-height: 400px;"></canvas>
            </div>
        </div>
    </div>
</section>

<script>
    const ctx = document.getElementById('ll-custom-dashboard');

    const labels = ['July 1', 'July 2', 'July 3', 'July 4', 'July 5', 'July 6', 'July 7', 'July 8'];
    const data = {
        labels: labels,
        datasets: [
            {
                label: 'Card Views',
                data: [16, 8, 18, 7, 15, 12, 6, 19],
                borderColor: '#739072',
                backgroundColor: '#739072',
                tension: 0.4
            },
            {
                label: 'Reward Views',
                data: [11, 5, 20, 14, 10, 8, 17, 9],
                borderColor: '#FD8D14',
                backgroundColor: '#FD8D14',
                tension: 0.4
            },
            {
                label: 'Points Issued',
                data: [7, 16, 14, 10, 18, 9, 6, 11],
                borderColor: '#6C5F5B',
                backgroundColor: '#6C5F5B',
                tension: 0.4
            },
            {
                label: 'Rewards Claimed',
                data: [13, 19, 5, 12, 9, 17, 8, 14],
                borderColor: '#2986cc',
                backgroundColor: '#2986cc',
                tension: 0.4
            },
            {
                label: 'Total Points',
                data: [11, 18, 14, 8, 12, 6, 20, 9],
                borderColor: '#FF6464',
                backgroundColor: '#FF6464',
                tension: 0.4
            },
            {
                label: 'Total Staff',
                data: [6, 18, 11, 7, 15, 19, 9, 14],
                borderColor: '#D0A2F7',
                backgroundColor: '#D0A2F7',
                tension: 0.4
            },
            {
                label: 'Total Members',
                data: [19, 6, 14, 8, 17, 12, 9, 15],
                borderColor: '#d8caa9',
                backgroundColor: '#d8caa9',
                tension: 0.4
            },
            {
                label: 'Total Cards',
                data: [11, 22, 25, 19, 12, 15, 17, 25],
                borderColor: '#A1CCD1',
                backgroundColor: '#A1CCD1',
                tension: 0.4
            }
        ]
    };

    new Chart(ctx, {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            plugins: {
            legend: {
                position: 'top',
            }
            }
        }
    });
</script>
@stop
