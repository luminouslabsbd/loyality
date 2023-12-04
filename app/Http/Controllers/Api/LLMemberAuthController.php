<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use App\Services\Member\MemberService;
use Carbon\Carbon;
use App\Notifications\Member\Registration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use App\Models\Member;
class LLMemberAuthController extends Controller

{
    
    public function get(){
         dd("Hello");
    }
     
    public function registerold(Request $request, MemberService $memberService)
    {
        // Validate request inputs
        $ischeck =  $request->validate([
            'phone' => 'required',
            'name' => 'required|max:64',
            'end_point' => 'required'
        ]);

        if($ischeck && $request->input('end_point') == 'll_api'){

            if (is_numeric($request->input('phone'))) {
                $email  =  $request->input('phone').'@loyaltykeoscx.com';
            }elseif(filter_var($request->input('phone'), FILTER_VALIDATE_EMAIL)){
                $email = $request->input('phone') ;
            }else{
                $errorResponse = [
                'status' => 'error',
                'message' => 'Phone number is invalid.',
                ];
                return response()->json($errorResponse, 202);
            }

            // Get or set default values for optional parameters
            $i18n = app()->make('i18n');
            $locale = $request->input('locale', $i18n->language->current->locale);
            $currency = $request->input('currency', $i18n->currency->id);
            $time_zone = $request->input('time_zone', $i18n->time_zone);
            $send_mail = $request->input('send_mail', 0);
            // Generate password if not provided
            $password = $request->input('password');
            $roomId = $request->input('room_id');
            if (is_null($password)) {
                $password = implode('', Arr::random(range(0, 9), 6));
                // $password = 12345678 ;
            }
            // Prepare response array
            $response = [
                'email' => $email ,
                'name' => $request->input('name'),
                // 'password' => $password,
                'time_zone' => $time_zone,
                'accepts_emails' => (int) $request->input('accepts_emails', 0),
                'send_mail' => (int) $send_mail,
                'locale' => $locale,
                'currency' => $currency,
            ];

            // Prepare member array for storing in the database
            $member = $response;
            $member['password'] = bcrypt($password);

            // 'send_mail' should not be stored in the database
            $member = Arr::except($member, ['send_mail']);

            // Save new member to database
            $newMember = $memberService->store($member);

            $this->sendRocketChat($email,$password,$roomId);

            // Send registration mail if requested
            // if ((int) $send_mail === 1) {
            //     $newMember->notify(new Registration($member['email'], $password, 'member'));
            // }

            // Return a response with member details
            return response()->json($response, 200);

        }else{
            $errorResponse = [
                'status' => 'error',
                'message' => 'validation error',
            ];
            return response()->json($errorResponse, 202);
        }
        
    }
    
    public function register(Request $request, MemberService $memberService)
    {
        // Get the raw content from the request
        $rawData = $request->getContent();
    
        // Decode the raw JSON data
        $requestData = json_decode($rawData, true);
    
        // Check if decoding was successful
        if ($requestData === null) {
            $errorResponse = [
                'status' => 'error',
                'message' => 'Invalid JSON data.',
            ];
            return response()->json($errorResponse, 400); // Use a 400 status code for bad request
        }
    
        // Validate request inputs
        $validator = validator($requestData, [
            'phone' => 'required',
            'name' => 'required|max:64',
            'end_point' => 'required',
        ]);
    
        if ($validator->fails() && $requestData['end_point'] == 'll_api') {
            // Validation failed
            $errorResponse = [
                'status' => 'error',
                'message' => 'Validation error.',
                'errors' => $validator->errors(),
            ];
            return response()->json($errorResponse, 422); // Use a 422 status code for unprocessable entity
        }
    
        // Continue with processing the data
        if ($requestData['end_point'] == 'll_api') {
            
            $phone=$requestData['phone'];
            $email = isset($phone) && is_numeric($phone)
                ? $phone . '@loyaltykeoscx.com'
                : (filter_var($phone, FILTER_VALIDATE_EMAIL) ? $phone : null);
                
                        // Check if email already exists in the database
            if (Member::where('email', $email)->exists()) {
                $errorResponse = [
                    'status' => 'error',
                    'message' => 'Member already exists.',
                ];
                return response()->json($errorResponse, 422);
            }

    
            // Get or set default values for optional parameters
            $i18n = app()->make('i18n');
            $locale = $requestData['locale'] ?? $i18n->language->current->locale;
            $currency = $requestData['currency'] ?? $i18n->currency->id;
            $time_zone = $requestData['time_zone'] ?? $i18n->time_zone;
            $send_mail = $requestData['send_mail'] ?? 0;
    
            // Generate password if not provided
            $password = $requestData['password'] ?? implode('', Arr::random(range(0, 9), 6));
            $roomId=$requestData['room_id'];
    
            // Prepare response array
            $response = [
                'email' => $email,
                'name' => $requestData['name'],
                'time_zone' => $time_zone,
                'accepts_emails' => (int)($requestData['accepts_emails'] ?? 0),
                'send_mail' => (int)$send_mail,
                'locale' => $locale,
                'currency' => $currency,
            ];
    
            // Prepare member array for storing in the database
            $member = $response;
            $member['password'] = bcrypt($password);
    
            // 'send_mail' should not be stored in the database
            $member = Arr::except($member, ['send_mail']);
    
            // Save new member to the database
            $newMember = $memberService->store($member);
    
            // Additional processing and notifications...
               $this->sendRocketChat($email,$password,$roomId);
    
            // Return a response with member details
            return response()->json($response, 200);
        }
    
        // Handle other cases or return an error response if needed
        $errorResponse = [
            'status' => 'error',
            'message' => 'Invalid endpoint.',
        ];
        return response()->json($errorResponse, 422);
    }


    public function sendRocketChat($email,$password,$roomId){

        $rocketChat =  DB::table('rocket_chat')->select('api_url','api_title','api_token','x_user_id')->first();
        
        if($rocketChat != null){
            // $token = $rocketChat->api_token;
            $token = Crypt::decryptString($rocketChat->api_token);
            $response = Http::withHeaders([
                'X-Auth-Token' => $token,
                'X-User-Id' => $rocketChat->x_user_id,
                'Content-type' => 'application/json',
            ])->post( $rocketChat->api_url,[
                'message' => [
                    'rid' => $roomId,
                    'msg' => "Email: $email\nPassword: $password",
                ],
            ]);
            // $response = Http::withHeaders([
            //     'X-Auth-Token' => "P1YbxbBIqpi8XHL3POoIxfaoRRFqVGntdxZ7WMzsqhx",
            //     'X-User-Id' => "s2GipkwdQoTrbnpPa",
            //     'Content-type' => 'application/json',
            // ])->post( 'https://labdesk.keos.co/api/v1/chat.sendMessage',[
            //     'message' => [
            //         'rid' => 'GENERAL',
            //         'msg' => "Email: $email\nPassword: $password",
            //     ],
            // ]);
            // For example, to get the response body:
            $responseBody = $response->json();
        }
        return true ;
    }

    
    public function login(Request $request)
    {

        $credentials = [];
        $request->validate([
            'phone' => 'required',
            'password' => 'required|min:6|max:48',
        ]);

        if (is_numeric($request->input('phone'))) {
            $email  =  $request->input('phone').'@loyaltykeoscx.com';
            $email = $request->input('email', $email);
        }elseif(filter_var($request->input('phone'), FILTER_VALIDATE_EMAIL)){
            $email = $request->input('phone') ;
            $email = $request->input('email', $email);
        }

        $credentials['email'] = $email ;
        $credentials['password'] = $request->input('password');

        // $credentials = $request->only('email', 'password') ;
        
        if (Auth::guard('member')->attempt($credentials)) {
            
            $user = Auth::guard('member')->user();
            $token =  $user->createToken('MemberAPIToken')->plainTextToken;

            if ($user && $user->is_active == 1) {
                // Update login stats
                $user->email_verified_at = $user->email_verified_at ?? Carbon::now('UTC');
                $user->number_of_times_logged_in++;
                $user->last_login_at = Carbon::now('UTC');
                $user->save();
                return response()->json(['token' => $token], 200);
            } else {
                throw ValidationException::withMessages([
                    'email' => ['This member is not active.'],
                ]);
            }
        } else {
            // throw ValidationException::withMessages([
            //     'email' => ['The provided credentials are incorrect.'],
            // ]);
            return response()->json(['email' => "The provided credentials are incorrect"], 202);
        }
    }

    
    public function logout(Request $request)
    {
        // Retrieve member
        $member = $request->user('member_api');

        // Revoke all tokens
        $member->tokens()->delete();

        return response()->json(['message' => 'Successfully logged out'], 200);
    }

    public function getMember(Request $request)
    {
        // Retrieve member
        $member = $request->user('member_api');

        // Hide sensitive information before exposing data
        $member->hideForPublic();

        return response()->json($member, 200);
    }
}
