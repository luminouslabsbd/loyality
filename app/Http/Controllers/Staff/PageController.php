<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Data\DataService;

class PageController extends Controller
{
    /**
     * Display the staff index page.
     *
     * @param string $locale
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(string $locale, Request $request,DataService $dataService): \Illuminate\View\View
    {   
        $dataDefinitionName = 'members';

        // Find the data definition by name and instantiate it if it exists
        $dataDefinition = $dataService->findDataDefinitionByName($dataDefinitionName);
        if ($dataDefinition === null) {
            throw new \Exception('Data definition "'.$dataDefinitionName.'" not found');
        }
        $dataDefinition = new $dataDefinition;

        // Get unique ID for table
        $uniqueId = unique_code(12);

        // Retrieve settings
        $settings = $dataDefinition->getSettings([]);

        // Redirect to edit form, before checking if list view is allowed
        if($settings['redirectListToEdit'] && $settings['redirectListToEditColumn'] !== null) {
            $userId = auth($settings['guard'])->user()->id;
            $primaryKey = $dataDefinition->model->getKeyName();
            $item = $dataDefinition->model->select($primaryKey)->where($settings['redirectListToEditColumn'], $userId)->first();
            if ($item) {
                // Redirect to edit form
                $id = $item->{$primaryKey};
                return redirect()->route($settings['guard'].'.data.edit', ['name' => $dataDefinition->name, 'id' => $id]);
            } else {
                abort(404);
            }
        }

        // Abort if the list view is not allowed based on the settings
        if (! $settings['list']) {
            abort(404);
        }

        // Retrieve the table data for the data definition
        $tableData = $dataDefinition->getData($dataDefinition->name, 'list');
        $totalMember = $tableData['data']->count();

        // $dashboardBlocks = [];

        // $dashboardBlocks[] = [
        //     'link' => route('staff.data.list', ['name' => 'account']),
        //     'icon' => 'user-circle',
        //     'title' => trans('common.account_settings'),
        //     'desc' => trans('common.staffDashboardBlocks.account_settings')
        // ];

        // $dashboardBlocks[] = [
        //     'link' => route('staff.qr.scanner'),
        //     'icon' => 'qr-code',
        //     'title' => trans('common.scan_qr'),
        //     'desc' => trans('common.staffDashboardBlocks.scan_qr')
        // ];

        // $dashboardBlocks[] = [
        //     'link' => route('staff.data.list', ['name' => 'members']),
        //     'icon' => 'user-group',
        //     'title' => trans('common.members'),
        //     'desc' => trans('common.staffDashboardBlocks.members')
        // ];

        return view('staff.index', compact('totalMember'));
    }

    /**
     * Display the QR scanner.
     *
     * @param string $locale
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showQrScanner(string $locale, Request $request): \Illuminate\View\View
    {
        return view('staff.qr.scanner');
    }
}
