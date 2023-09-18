<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\APIController;
use App\Models\User;
use App\Http\Resources\LogResource;
use App\Services\MyIDService;
use App\Http\Requests\ClientCodeRequest;
use App\Models\Customer;
use App\Http\Resources\CustomerResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use Validator;

class UserController extends APIController
{

    public $successStatus = 200;

    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login()
    {
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
            $user = Auth::user();
            $token = $user->createToken('MyApp');
            $success['token'] = $token->plainTextToken;
            return $this->successResponse($token->plainTextToken);
        } else {
            return $this->errorResponse('Unauthorised');
        }
    }

    public function myid(ClientCodeRequest $request)
    {
        $access_token = MyIDService::getAccessToken($request);

        if ($access_token) {
            $citizen = MyIDService::getCitizen($access_token);
            $citizen = $citizen->object();

            if ($citizen) {
                if (Customer::where('pinfl', $citizen->profile->common_data->pinfl)->exists()){
                    $customer = Customer::where('pinfl', $citizen->profile->common_data->pinfl)->get()->first();
                    $user = $customer->user;
                } else {
                    $user = User::create([
                        'name' => ($citizen->profile->common_data->first_name ?? '') . ' ' . ($citizen->profile->common_data->last_name ?? ''),
                        'email' => $citizen->profile->contacts->email ?? $citizen->profile->common_data->pinfl,
                        'password' => Hash::make(Str::random(16)),
                    ]);

                    $user->roles()->sync([2]);

                    $customer = Customer::create(
                        [
                            'user_id'           => $user->id,
                            'pinfl'             => $citizen->profile->common_data->pinfl,
                            // citizen data
                            'first_name'        => $citizen->profile->common_data->first_name ?? '',
                            'middle_name'       => $citizen->profile->common_data->middle_name ?? '',
                            'last_name'         => $citizen->profile->common_data->last_name ?? '',
                            'first_name_en'     => $citizen->profile->common_data->first_name_en ?? '',
                            'last_name_en'      => $citizen->profile->common_data->last_name_en ?? '',
                            'gender'            => $citizen->profile->common_data->gender ?? '',
                            'birth_place'       => $citizen->profile->common_data->birth_place ?? '',
                            'birth_country'     => $citizen->profile->common_data->birth_country ?? '',
                            'birth_date'        => $citizen->profile->common_data->birth_date ?? '',
                            'citizenship'       => $citizen->profile->common_data->citizenship ?? '',
                            'sdk_hash'          => $citizen->profile->common_data->sdk_hash ?? '',
                            // passport data
                            'pass_data'         => $citizen->profile->doc_data->pass_data ?? '',
                            'issued_by'         => $citizen->profile->doc_data->issued_by ?? '',
                            'issued_date'       => $citizen->profile->doc_data->issued_date ?? '',
                            'expiry_date'       => $citizen->profile->doc_data->expiry_date ?? '',
                            'doc_type'          => $citizen->profile->doc_data->doc_type ?? '',
                            // contacts
                            'phone'             => $citizen->profile->contacts->phone ?? $citizen->profile->common_data->pinfl,
                            'email'             => $citizen->profile->contacts->email ?? '',
                            // address
                            'permanent_address'    => $citizen->profile->address->permanent_address ?? '',
                            'temporary_address'    => $citizen->profile->address->temporary_address ?? '',
                        ]
                    );
                }

                $token = $user->createToken('autoclick');
                $success['token'] = $token->plainTextToken;
                return $this->successResponse($token->plainTextToken);
            } else {
                return $this->errorResponse(['message' => 'Citizen not found']);
            }
        } else {
            return $this->errorResponse(['message' => 'No access token']);
        }
    }

    public function client(Request $request)
    {
        $user = $request->user();
        $customerResource = new CustomerResource($user->customer);
        return $this->successResponse($customerResource);
    }

    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        if (Auth::user()) {
            $user = Auth::user();
            // $user->tokens()->delete();
            return $this->successResponse('All tokens deleted');
        } else {
            return $this->errorResponse('Unauthorised');
        }
    }

    public function username()
    {
        return 'phone';
    }
}
