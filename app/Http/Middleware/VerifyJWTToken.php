<?php

namespace App\Http\Middleware;

use App\Common\ApiResponse;
use App\Models\User;
use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;


class VerifyJWTToken
{
    use ApiResponse;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = JWTAuth::getToken();
        if(!$token)
        {
            $this->setMeta('500', __('apiMessages.tokenAbsent'));
            return response()->json($this->setResponse());
        }
        try {
            $this->auth($token = false);
        } catch (TokenExpiredException $e) {
            $this->setMeta('500', __('apiMessage.tokenMismatch'));
            return response()->json($this->setResponse(),500);
        } catch (TokenInvalidException $e) {
            $this->setMeta('500', __('apiMessage.tokenMismatch'));
            return response()->json($this->setResponse(),500);
        } catch (JWTException $e) {
            $this->setMeta('500', __('apiMessage.tokenExpired'));
            return response()->json($this->setResponse(), 500);
        }
        $request->merge(compact('user'));
        return $next($request);
    }
    public function auth($token = false)
    {
        $sub = JWTAuth::getPayload($token)->get('sub');
            $id = $sub["id"];
            $user = User::where('id', $id)
                ->first();
            if($user){
                return $user;
            }
        return false;
    }
}
