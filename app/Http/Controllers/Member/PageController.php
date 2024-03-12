<?php

namespace App\Http\Controllers\Member;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use App\Services\MarkdownService;
use App\Services\Card\CardService;
use App\Http\Controllers\Controller;

class PageController extends Controller
{
    /**
     * Handle the incoming request and display the home page.
     *
     * @param string $locale
     * @param Request $request
     * @param CardService $cardService
     *
     * @return \Illuminate\View\View
     */
    public function index(string $locale, Request $request, CardService $cardService)
    {
        // Fetch all active cards visible by default
        $cards = $cardService->findActiveCardsVisibleByDefault();

        // If user is authenticated, add followed cards to the collection and order by balance, issue_date
        if (auth('member')->check()) {
            $followedCards = $cardService->findActiveCardsFollowedByMember(auth('member')->user()->id);
            $cardsWithTransactions = $cardService->findActiveCardsWithMemberTransactions(auth('member')->user()->id);

            $cards = $cards->concat($followedCards)
                ->concat($cardsWithTransactions)
                ->unique('id')
                ->sortByDesc(function ($card) {
                    return [$card->getMemberBalance(null), $card->issue_date];
                });
        }

        // Pass the collection to the view
        return view('member.home', compact('cards'));
    }

    /**
     * Handle the incoming request and display the dashboard page.
     *
     * @param string $locale
     * @param Request $request
     *
     * @return \Illuminate\View\View
     */
    public function dashboard(string $locale, Request $request, CardService $cardService)
    {
        //return "member dashbaord";

        // Define dashboard blocks
        $dashboardBlocks = [
            [
                'link' => route('member.data.list', ['name' => 'account']),
                'icon' => 'cog-6-tooth',
                'title' => trans('common.account_settings'),
                'desc' => trans('common.memberDashboardBlocks.account_settings')
            ],
            [
                'link' => route('member.index'),
                'icon' => 'gift',
                'title' => trans('common.loyalty_cards'),
                'desc' => trans('common.memberDashboardBlocks.cards')
            ]
        ];

        // Fetch all active cards visible by default
        $cards = $cardService->findActiveCardsVisibleByDefault();

        // If user is authenticated, add followed cards to the collection and order by balance, issue_date
        if (auth('member')->check()) {
            $followedCards = $cardService->findActiveCardsFollowedByMember(auth('member')->user()->id);
            $cardsWithTransactions = $cardService->findActiveCardsWithMemberTransactions(auth('member')->user()->id);

            $cards = $cards->concat($followedCards)
                ->concat($cardsWithTransactions)
                ->unique('id')
                ->sortByDesc(function ($card) {
                    return [$card->getMemberBalance(null), $card->issue_date];
                });
        }

        // Pass the blocks to the view
        return view('member.dashboard', compact("followedCards", "cards"));
    }

    public function getChartData(string $locale, Request $request, CardService $cardService){

        $cards = $cardService->findActiveCardsVisibleByDefault();
        // If user is authenticated, add followed cards to the collection and order by balance, issue_date
        if (auth('member')->check()) {

            $followedCards = $cardService->findActiveCardsFollowedByMember(auth('member')->user()->id);

            $last7DaysFollowedCardCount = $this->getLast7DaysCardData($followedCards);

            $cardsWithTransactions = $cardService->findActiveCardsWithMemberTransactions(auth('member')->user()->id);

            $cards = $cards->concat($followedCards)
                    ->concat($cardsWithTransactions)
                    ->unique('id')
                    ->sortByDesc(function ($card) {
                        return [$card->getMemberBalance(null), $card->issue_date];
                    });

            $last7DaysTrxCardsCount =  $this->getLast7DaysCardData($cards);
        }

        return response()->json([
            'last7DaysFollowedCardCount' => $last7DaysFollowedCardCount,
            'last7DaysTrxCardsCount' => $last7DaysTrxCardsCount,
        ]);

    }

    private function getLast7DaysCardData($items)
    {
        // Get the start and end dates for the last 7 days
        $endDate = now();
        $startDate = $endDate->copy()->subDays(6);

        // Generate an array of dates for the last 7 days
        $dates = collect(CarbonPeriod::create($startDate, $endDate)->toArray());

        // Initialize an array to store the result
        $result = [];

        // Iterate through each date and count items for that date
        foreach ($dates as $date) {
            $formattedDate = $date->format('Y-m-d');

            $count = $items->filter(function ($item) use ($formattedDate) {
                $createdAt = Carbon::parse($item->created_at)->format('Y-m-d');
                return $createdAt === $formattedDate;
            })->count();

            $result[] = [
                'date' => $formattedDate,
                'count' => $count,
            ];
        }

        return $result;
    }

    /**
     * Handle the incoming request and display a page.
     *
     * @param string $locale
     * @param Request $request
     * @param string $page The markdown file to be displayed.
     *
     * @return \Illuminate\View\View
     */
    private function displayPage(string $locale, Request $request, string $page, MarkdownService $markdownService)
    {
        $content = $markdownService->trans($page);
        return view("member.content.$page", compact('content'));
    }

    public function about(string $locale, Request $request, MarkdownService $markdownService)
    {
        return $this->displayPage($locale, $request, 'about', $markdownService);
    }

    public function contact(string $locale, Request $request, MarkdownService $markdownService)
    {
        return $this->displayPage($locale, $request, 'contact', $markdownService);
    }

    public function faq(string $locale, Request $request)
    {
        return view('member.content.faq');
    }

    public function terms(string $locale, Request $request, MarkdownService $markdownService)
    {
        return $this->displayPage($locale, $request, 'terms', $markdownService);
    }

    public function privacy(string $locale, Request $request, MarkdownService $markdownService)
    {
        return $this->displayPage($locale, $request, 'privacy', $markdownService);
    }
}
