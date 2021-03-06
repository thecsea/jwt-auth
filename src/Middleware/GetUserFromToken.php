<?php

namespace Tymon\JWTAuth\Middleware;

use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class GetUserFromToken extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     * If an user mode is set I don't check custom
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  String $custom custom claims that must be equals (format: key1-ele1;key2-ele2)
     * @return mixed
     */
    public function handle($request, \Closure $next, $custom = '')
    {
        $custom = $this->convertToArray($custom);
        if($token = $this->auth->setRequest($request)->getToken()) {
        }else if ($this->auth->getUserModel()){
            $token = $this->auth->fromUser($this->auth->getUserModel(), $custom);
        }else {
            return $this->respond('tymon.jwt.absent', 'token_not_provided', 401);
        }

        try {
            $user = $this->auth->authenticate($token, $custom);
        } catch (TokenExpiredException $e) {
            return $this->respond('tymon.jwt.expired', 'token_expired', $e->getStatusCode(), [$e]);
        } catch(InvalidClaimException $e) {
            return $this->respond('tymon.jwt.invalid', 'claim_invalid', $e->getStatusCode(), [$e]);
        } catch (JWTException $e) {
            return $this->respond('tymon.jwt.invalid', 'token_invalid', $e->getStatusCode(), [$e]);
        }

        if (! $user) {
            return $this->respond('tymon.jwt.user_not_found', 'user_not_found', 404);
        }

        $this->events->fire('tymon.jwt.valid', $user);

        return $next($request);
    }
}
