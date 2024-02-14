<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PartnerAuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/{locale}/v1/partner/login",
     *     operationId="loginPartner",
     *     tags={"Partner", "Authentication"},
     *     summary="Log in a partner",
     *     description="This endpoint allows a partner to log in by providing their email and password.",
     *
     *     @OA\Parameter(
     *         name="locale",
     *         in="path",
     *         description="The locale (e.g. `en-us`)",
     *         required=true,
     *         @OA\Schema(
     *           type="string",
     *           default="en-us"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="The email of the partner",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         description="The password of the partner",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/PartnerLoginSuccess")
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     * 
     *     @OA\Response(
     *         response=422,
     *         description="Validation errors",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     *
     *     security={
     *         {"partner_auth_token": {}}
     *     }
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:96',
            'password' => 'required|min:6|max:48',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::guard('partner')->attempt($credentials)) {
            $user = Auth::guard('partner')->user();
            $token =  $user->createToken('PartnerAPIToken')->plainTextToken;
            return response()->json(['token' => $token], 200);
        } else {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
    }

    /**
     * Log out a partner.
     *
     * The endpoint ("/{locale}/v1/partner/logout") revokes all access tokens for the authenticated partner.
     *
     * @OA\Post(
     *     path="/{locale}/v1/partner/logout",
     *     operationId="logoutPartner",
     *     tags={"Partner", "Authentication"},
     *     summary="Log out a partner",
     *     description="This endpoint revokes all access tokens for the authenticated partner.",
     * 
     *     @OA\Parameter(
     *         name="locale",
     *         in="path",
     *         description="The locale (e.g., `en-us`)",
     *         required=true,
     *         @OA\Schema(
     *           type="string",
     *           default="en-us"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Partner logged out successfully",
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     *
     *     security={
     *         {"partner_auth_token": {}}
     *     }
     * )
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Retrieve partner
        $partner = $request->user('partner_api');

        // Revoke all tokens
        $partner->tokens()->delete();

        return response()->json(['message' => 'Successfully logged out'], 200);
    }

    /**
     * Get partner data.
     *
     * The endpoint ("/{locale}/v1/partner") retrieves the data of the authenticated partner.
     * 
     * @OA\Get(
     *     path="/{locale}/v1/partner",
     *     operationId="getPartner",
     *     tags={"Partner", "Authentication"},
     *     summary="Get partner data",
     *     description="This endpoint retrieves the data of the authenticated partner.",
     * 
     *     @OA\Parameter(
     *         name="locale",
     *         in="path",
     *         description="The locale (e.g., `en-us`)",
     *         required=true,
     *         @OA\Schema(
     *           type="string",
     *           default="en-us"
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Partner data retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Partner")
     *     ),
     * 
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     security={
     *         {"partner_auth_token": {}}
     *     }
     * )
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPartner(Request $request)
    {
        // Retrieve partner
        $partner = $request->user('partner_api');

        // Hide sensitive information before exposing data
        $partner->hideForPublic();

        return response()->json($partner, 200);
    }
}
