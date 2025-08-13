<?php

    namespace App\Http\Controllers\Data;

    use App\Http\Controllers\Controller;
    use App\Services\Data\DataService;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Validation\Validator;
    use Mockery\Exception;

    class InsertController extends Controller
    {
        public static $crmUrl;
        public function __construct()
        {
            self::$crmUrl = env('CRM_API_URL');
        }

        /**
         * Show the create forFm for the given data definition.
         *
         * @param string $locale The current locale.
         * @param string $dataDefinitionName The name of the data definition to retrieve.
         * @param Request $request The incoming HTTP request.
         * @param DataService $dataService The data service to fetch the data definition.
         * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
         */
        public function showInsertItem(string $locale, string $dataDefinitionName, Request $request, DataService $dataService)
        {
            // Get the data definition instance
            $dataDefinition = $this->getDataDefinitionInstance($dataDefinitionName, $dataService);


            // Fetch the column data for the given data definition (insert mode)
            $form = $dataDefinition->getData($dataDefinition->name, 'insert');

            // Get settings for the data definition
            $settings = $dataDefinition->getSettings([]);
            // Validate user access based on settings and request
            $this->validateAccess($settings, $request);

            // Return the insert view with the required data
            return view('data.insert', compact('dataDefinition', 'form', 'settings'));
        }

        /**
         * Create the record with the submitted data.
         *
         * @param string $locale The current locale.
         * @param string $dataDefinitionName The name of the data definition to retrieve.
         * @param Request $request The incoming HTTP request.
         * @param DataService $dataService The data service to fetch the data definition.
         * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
         */
        public function postInsertItem(string $locale, string $dataDefinitionName, Request $request, DataService $dataService)
        {


            // Get the data definition instance
            $dataDefinition = $this->getDataDefinitionInstance($dataDefinitionName, $dataService);
            // Fetch the column data for the given data definition (insert mode)
            $form = $dataDefinition->getData($dataDefinition->name, 'insert');
            // Retrieve settings for the data definition
            $settings = $dataDefinition->getSettings([]);
            // Validate user access based on settings and request
            $this->validateAccess($settings, $request);

            if ($dataDefinitionName == "partners"){
                $domainStatus = self::isDomainExistInCrm($request->crm_domain);
                if (!$domainStatus){
                    $message = [
                        'type' => 'danger',
                        'size' => 'lg',
                        'text' => trans('common.is_domain_exist_crm'),
                    ] ;
                    return redirect(route($settings['guard'] . '.data.insert', ['name' => $dataDefinitionName]))->with('toast', $message)->withInput();
                }
                $emailStatus = self::isEmailExistInCrm($request->email);
                if (!$emailStatus){
                    $message = [
                        'type' => 'danger',
                        'size' => 'lg',
                        'text' => trans('common.is_email_exist_crm'),
                    ] ;
                    return redirect(route($settings['guard'] . '.data.insert', ['name' => $dataDefinitionName]))->with('toast', $message)->withInput();
                }
            }

            // Handle crm_package_id conversion for partners
            if ($dataDefinitionName == "partners" && $request->has('crm_package_id')) {
                $packageId = $request->crm_package_id;

                // Handle empty selection - set to null
                if ($packageId === '' || $packageId === null) {
                    $request->merge(['crm_package_id' => null]);
                }
                // Convert string package names to numeric IDs when CRM is not available
                elseif (is_string($packageId)) {
                    $packageMapping = [
                        'basic' => 1,
                        'standard' => 2,
                        'premium' => 3
                    ];

                    if (isset($packageMapping[$packageId])) {
                        $request->merge(['crm_package_id' => $packageMapping[$packageId]]);
                    }
                }
            }

            // Call the insertRecord method on the dataService instance to create the record
            $message = $dataService->insertRecord($request, $form, $settings);
            // Check if validation has failed
            if ($message instanceof Validator) {
                // Return the insert view with the required data and validation errors
                return back()->withInput($request->all())->withErrors($message);
            }


            if ($dataDefinitionName == "partners"){
                try {
                    $walletApiUrl = env('WALLET_API_URL');
                    if (!empty($walletApiUrl)) {
                        $response = Http::timeout(10)->post($walletApiUrl."/register",[
                            'name' => $request->name,
                            'email' => $request->email,
                            'password' => $request->password
                        ]);

                        if ($response->ok()){
                            $responseData = json_decode($response->body(), true);
                            $member_id = $responseData['user']['id'] ?? null;
                        }
                    } else {
                        Log::warning('WALLET_API_URL is not configured');
                    }
                }catch (Exception $exception){
                    Log::error("Problem of keos wallet partner creation: " . $exception->getMessage());
                }

                try {
                    $labCrmApiUrl = env('LABCRM_API_URL');
                    if (!empty($labCrmApiUrl)) {
                        $response = Http::timeout(10)->post($labCrmApiUrl.'/create_loyality_customer',[
                            'email' => $request->email,
                            'phonenumber' => $request->phone ?? "*** ** **** **",
                            'company' => 'company'
                        ]);
                        if ($response->ok()){
                            $crm_member_id = $response['crm_customer_id'] ?? null;
                        }
                    } else {
                        Log::warning('LABCRM_API_URL is not configured');
                    }
                }catch (Exception $exception){
                    Log::error("Problem of CRM member creation: " . $exception->getMessage());
                }

                self::registerTenent($request);

                if (!empty($member_id) && !empty($crm_member_id)){
                    DB::table('partners')
                        ->where('email', $request->email)
                        ->update([
                            'keos_passkit_id' => $member_id ?? null,
                            'crm_customer_id' => $crm_member_id ?? '',
                        ]);
                }
            }

            // Redirect the user to the data list view with the result message
            return redirect(route($settings['guard'] . '.data.list', ['name' => $dataDefinitionName]))->with('toast', $message);
        }

        /**
         * Get the data definition instance by name.
         *
         * @param string $dataDefinitionName The name of the data definition to retrieve.
         * @param DataService $dataService The data service to fetch the data definition.
         * @return object The instantiated data definition object.
         *
         * @throws \Exception If the data definition is not found.
         */
        private function getDataDefinitionInstance(string $dataDefinitionName, DataService $dataService)
        {
            // Find the data definition by name
            $dataDefinition = $dataService->findDataDefinitionByName($dataDefinitionName);

            // If the data definition is not found, throw an exception
            if ($dataDefinition === null) {
                throw new \Exception('Data definition "'.$dataDefinitionName.'" not found');
            }

            // Instantiate and return the data definition object
            return new $dataDefinition;
        }

        /**
         * Validate user access based on settings and request.
         *
         * @param array $settings The settings for the data definition.
         * @param Request $request The incoming HTTP request.
         * @return void
         */
        private function validateAccess(array $settings, Request $request)
        {
            // Check if insert is allowed based on the settings
            if (! $settings['insert']) {
                Log::notice('app\Http\Controllers\Data\InsertController.php - Insert not allowed ('.auth($settings['guard'])->user()->email.')');
                abort(404);
            }

            // Obtain the user type from the route name (member, staff, partner, or admin)
            $guard = explode('.', $request->route()->getName())[0];

            // Check if the user type is allowed based on the settings
            if ($settings['guard'] !== $guard) {
                Log::notice('app\Http\Controllers\Data\InsertController.php - View not allowed for '.$guard.', '.$settings['guard'].' required ('.auth($settings['guard'])->user()->email.')');
                abort(404);
            }
        }

        private static function isDomainExistInCrm($domain){
            try {
                if (empty(self::$crmUrl)) {
                    Log::warning('CRM_API_URL is not configured - skipping domain validation');
                    return true; // Skip validation if CRM URL is not configured
                }

                $response = Http::timeout(10)->post(self::$crmUrl."/check_domain_is_exists",[
                    "domain" => $domain
                ]);
                if ($response->successful()) {
                    $data = $response->json();
                    return $data['domain'] ?? false;
                }
                return false;
            }catch (Exception $exception){
                Log::error("Problem of KEOS isDomainExistInCrm Method: " . $exception->getMessage());
                return false;
            }
        }

        private static function isEmailExistInCrm($email){
            try {
                if (empty(self::$crmUrl)) {
                    Log::warning('CRM_API_URL is not configured - skipping email validation');
                    return true; // Skip validation if CRM URL is not configured
                }

                $response = Http::timeout(10)->post(self::$crmUrl."/check_email_is_exists",[
                    "email" => $email,
                ]);
                $partner = DB::table('partners')->where('email',$email)->first();

                if ($response->successful()) {
                    $data = $response->json();
                    if (!($data['email'] ?? false) || !empty($partner)) {
                       return false;
                    }else{
                        return true;
                    }
                }
                return false;
            }catch (Exception $exception){
                Log::error("Problem of KEOS isEmailExistInCrm Method: " . $exception->getMessage());
                return false;
            }
        }

        private static function registerTenent($request)
        {
            try {
                $crmUrl = env('CRM_API_URL');
                if (empty($crmUrl)) {
                    Log::warning('CRM_API_URL is not configured');
                    return false;
                }

                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $crmUrl.'/tenent_user_registion',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => [
                        'name' => $request->name,
                        'email' => $request->email,
                        'domain' => $request->crm_domain,
                        'package_id' => $request->crm_package_id,
                        'billing_cycle' => $request->crm_billing_cycle,
                        'expired_date' => addMonthWithCurrentDate(1),
                        'mobile' => $request->phone,
                        'country' => '49'
                    ],
                    CURLOPT_HTTPHEADER => array(
                        'Cookie: sp_session=35e6h6jmklnkbovrfo11e1obgsp5d90l'
                    ),
                ));

                $response = curl_exec($curl);
                $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);

                if ($response === false || $httpCode >= 400) {
                    Log::warning('CRM tenant registration failed');
                    return false;
                }

                return $response;
            }catch (Exception $exception){
                Log::error("Problem of KEOS registerTenent Method: " . $exception->getMessage());
                return false;
            }
        }
    }
