<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\{Request, JsonResponse};
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Mews\Captcha\Facades\Captcha;
use Illuminate\Support\Facades\File;

class AuthController extends Controller
{

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Handle an authentication attempt.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\Response
     */

    //Captcha
    public function reloadCaptcha()
    {
        $captcha = Captcha::create('default', true);
        $img = $captcha['img'];
        $key = $captcha['key'];
        Cache::put('captcha_key', $key);
        return response()->json([
            'captcha' => $img
        ]);
    }
     
    public function login(Request $request)
    {
        try {
            $key = Cache::get('captcha_key');
            $capt = Captcha::check_api(request('captcha'), $key);

            if (!$capt) {
                return response()->json([
                    'status' => 400,
                    'errors' => 'Unauthorized',
                    'message' => 'Login failed, captcha Invalid'
                ], 400);
            }

            $data = [
                'username' => ['required', 'string'],
                'password' => ['required', 'string'],
            ];
            
            $validated = $this->handleValidationException($request, $data);
            if ($validated instanceof JsonResponse) {
                return $validated;
            }

            $username = $validated['username'];
            $password = $validated['password'];
            $query = $this->user->firstWhere('username', $validated['username']);

            $adServers = ["ldap://gysdc01.gyssteel.com", "ldap://gysdc02.gyssteel.com"];

            $ldapConnected = false;
            foreach ($adServers as $server) {
                $ldap = @ldap_connect($server);
                if ($ldap) {
                    ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
                    ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

                    $ldaprdn = "gys\\" . $username;
                    $bind = @ldap_bind($ldap, $ldaprdn, $password);

                    if ($bind) {
                        $ldapConnected = true;
                        break;
                    }
                }
            }

            if ($ldapConnected) {
                $filter = "(sAMAccountName=$username)";
                $result = @ldap_search($ldap, "dc=gyssteel,dc=com", $filter);

                if ($result) {
                    $entries = ldap_get_entries($ldap, $result);
                    if ($entries['count'] > 0) {
                        $userInfo = $entries[0];

                        Session::put('GROUP', $userInfo['memberof'][2] ?? '');
                        Session::put('ext', $userInfo['telephonenumber'][0] ?? '');
                        Session::put('email', $userInfo['mail'][0] ?? '');
                        Session::put('start', time());
                        Session::put('expire', time() + 300);

                        $user = $this->user->firstWhere('username', $validated['username']);
                        $token = $user->createToken('access_token')->plainTextToken;

                        if ($user) {
                            Session::put('role', $user['usr_access']);
                            Session::put('nama', $user['display_name']);
                            Session::put('usr_name', $user['username']);
                            Session::put('usr_id', $user['id']);
                        }

                        return response()->json([
                            'status' => 201,
                            'message' => 'Login successfully',
                            'userAbilityRules' => [
                                [
                                    'action' => 'manage',
                                    'subject' => 'all',
                                ],
                            ],
                            'userData' => $user,
                            'accessToken' => $token
                        ], 201);
                    }
                }
            } else if ($query && password_verify($password, $query->password)) {
                $token = $query->createToken('access_token')->plainTextToken;

                return response()->json([
                    'status' => 201,
                    'message' => 'Login successfully',
                    'userAbilityRules' => [
                        [
                            'action' => 'manage',
                            'subject' => 'all',
                        ],
                    ],
                    'userData' => $query,
                    'accessToken' => $token
                ], 201);
            } else {
                return response()->json([
                    'status' => 400,
                    'errors' => 'Unauthorized',
                    'message' => 'Login failed, username or password is incorrect'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'errors' => $e,
                'message' => 'Failed to login',
            ], 500);
        }
    }

    /**
     * Logout user (Revoke the token)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'status' => 200,
                'message' => 'Logout successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => 'Server Error',
                'message' => 'An error occurred while logging out. Please try again later',
            ], 500);
        }
    }
}
