<?php

namespace App\Http\Controllers\Api;

use App\Common\ApiResponse;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Validator;

class UserController extends Controller
{
    use ApiResponse;

    public function registration(Request $request)
    {
        $validator = Validator::make(
            array(
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
            ),
            array(
                'name' => 'required|max:50',
                'email' => 'required|email|unique:users|max:150',
                'password' => 'required',
            )
        );
        if ($validator->fails()) {
            $errors = $validator->errors();
            if ($errors->first('name')) {
                $message = $errors->first('name');
            } else if ($errors->first('email')) {
                $message = $errors->first('email');
            } else if ($errors->first('password')) {
                $message = $errors->first('password');
            } else {
                $message = __('apiMessages.parametersRequired');
            }
            $this->setMeta($message);
            return response()->json($this->setResponse(), 422);
        }
        try {
            $password = Hash::make($request->password);
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = $password;
            $user->save();
            $token = null;
            $credentials = $request->only('email', 'password');
            if (!$token = JWTAuth::attempt($credentials)) {
                $this->setMeta(__('apiMessages.tokenMismatch'));
                return response()->json($this->setResponse(), 500);
            }
            $user->token = $token;
            $this->setMeta(__('apiMessages.registrationSuccess'));
            $this->setData("user", $user);
            return response()->json($this->setResponse(), 201);
        } catch (JWTException $e) {
            $this->setMeta(__('apiMessages.queryError'));
            return response()->json($this->setResponse(), 500);
        } catch (QueryException $e) {
            $this->setMeta(__('apiMessages.queryError'));
            return response()->json($this->setResponse(), 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make(
            array(
                'email' => $request->email,
                'password' => $request->password,
            ),
            array(
                'email' => 'required',
                'password' => 'required',
            )
        );
        if ($validator->fails()) {
            $errors = $validator->errors();
            if ($errors->first('email')) {
                $message = $errors->first('email');
            } else if ($errors->first('password')) {
                $message = $errors->first('password');
            } else {
                $message = __('apiMessages.parametersRequired');
            }
            $this->setMeta($message);
            return response()->json($this->setResponse(), 422);
        }

        try {
            $user = User::where('email', $request->email)->first();
            $usertoken = $user->token;
            Log::info($usertoken);
            $status = false;
            if ($usertoken != null) {
                Log::info('logging because of login');
                $status = $this->invalidate($usertoken);
            }
            if ($status == true || $usertoken == null) {
                $token = null;
                $credentials = $request->only('email', 'password');
                if (!$token = JWTAuth::attempt($credentials)) {
                    $this->setMeta(__('apiMessages.tokenMismatch'));
                    return response()->json($this->setResponse(), 500);
                }
                Log::info('after getting token' . $token);
                User::where('email', $request->email)
                    ->update([
                        'token' => $token
                    ]);
                $user->token = $token;
            }
            $this->setMeta(__('apiMessages.loginSuccess'));
            $this->setData("user", $user);
            return response()->json($this->setResponse(), 200);

        } catch (JWTException $e) {
            //$this->setMeta(__('apiMessages.queryError'));
            $this->setMeta($e->getMessage());
            return response()->json($this->setResponse(), 500);
        }
    }

    public function getAppUsers(Request $request)
    {
        $validator = Validator::make(
            array(
                'userId' => $request->userId,
            ),
            array(
                'userId' => 'required'
            )
        );
        if ($validator->fails()) {
            $errors = $validator->errors();
            if ($errors->first('userId')) {
                $message = $errors->first('userId');
            } else {
                $message = __('apiMessages.parametersRequired');
            }
            $this->setMeta($message);
            return response()->json($this->setResponse(), 422);
        }
        try {
            $user = $request->user;
            if ($user) {
                $getUsers = User::get();
                $this->setMeta(__('apiMessages.loginSuccess'));
                $this->setData("user", $user);
                $this->setData("users", $getUsers);
                return response()->json($this->setResponse(), 200);
            } else {
                $this->setMeta(__('apiMessages.unprocessableRequest'));
                return response()->json($this->setResponse(), 422);
            }
        } catch (QueryException $exception) {
            $this->setMeta(__('apiMessages.queryError'));
            return response()->json($this->setResponse(), 500);
        }
    }

    public function logout(Request $request)
    {
        $validator = Validator::make(
            array(
                'userId' => $request->userId,
            ),
            array(
                'userId' => 'required'
            )
        );
        if ($validator->fails()) {
            $errors = $validator->errors();
            if ($errors->first('userId')) {
                $message = $errors->first('userId');
            } else {
                $message = __('apiMessages.parametersRequired');
            }
            $this->setMeta($message);
            return response()->json($this->setResponse(), 422);
        }
        $token = JWTAuth::getToken();
        Log::info("Logging bcoz of Logout");
        $this->invalidate($token);
        $this->setMeta(__('apiMessages.logoutSuccess'));
        return response()->json($this->setResponse(), 200);
    }

    public function invalidate($token)
    {
        $id = JWTAuth::getPayload($token)->get('sub');
        User::where('id', $id)
            ->update([
                'token' => null
            ]);
        JWTAuth::invalidate($token);
        return true;
    }
}
