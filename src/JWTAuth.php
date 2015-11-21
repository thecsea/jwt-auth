<?php

namespace Tymon\JWTAuth;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\InvalidClaimException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Providers\Auth\AuthInterface;
use Tymon\JWTAuth\Providers\User\UserInterface;
use \Illuminate\Contracts\Auth\Authenticatable;

class JWTAuth
{
    /**
     * @var \Tymon\JWTAuth\JWTManager
     */
    protected $manager;

    /**
     * @var \Tymon\JWTAuth\Providers\User\UserInterface
     */
    protected $user;

    /**
     * @var \Tymon\JWTAuth\Providers\Auth\AuthInterface
     */
    protected $auth;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $identifier = 'id';

    /**
     * @var \Tymon\JWTAuth\Token
     */
    protected $token;


    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    protected $userModel = null;

    /**
     * @param \Tymon\JWTAuth\JWTManager                   $manager
     * @param \Tymon\JWTAuth\Providers\User\UserInterface $user
     * @param \Tymon\JWTAuth\Providers\Auth\AuthInterface $auth
     * @param \Illuminate\Http\Request                    $request
     */
    public function __construct(JWTManager $manager, UserInterface $user, AuthInterface $auth, Request $request)
    {
        $this->manager = $manager;
        $this->user = $user;
        $this->auth = $auth;
        $this->request = $request;
    }

    /**
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function getUserModel()
    {
        return $this->userModel;
    }

    /**
     * @param \Illuminate\Contracts\Auth\Authenticatable $userModel
     */
    public function setUserModel(Authenticatable $userModel)
    {
        $this->userModel = $userModel;
    }



    /**
     * Find a user using the user identifier in the subject claim.
     *
     * @param bool|string $token
     *
     * @return mixed
     */
    public function toUser($token = false)
    {
        if(!$token && $this->getUserModel())
            return $this->getUserModel();

        $payload = $this->getPayload($token);

        if (! $user = $this->user->getBy($this->identifier, $payload['sub'])) {
            return false;
        }

        $this->setUserModel($user);
        return $user;
    }

    /**
     * Generate a token using the user identifier as the subject claim.
     *
     * @param mixed $user
     * @param array $customClaims
     *
     * @return string
     */
    public function fromUser($user, array $customClaims = [])
    {
        $payload = $this->makePayload($user->{$this->identifier}, $customClaims);

        return $this->manager->encode($payload)->get();
    }

    /**
     * Attempt to authenticate the user and return the token.
     *
     * @param array $credentials
     * @param array $customClaims
     *
     * @return false|string
     * @throws JWTException
     */
    public function attempt(array $credentials = [], array $customClaims = [])
    {
        if (! $this->auth->byCredentials($credentials)) {
            return false;
        }

        return $this->fromUser($this->auth->user(), $customClaims);
    }

    /**
     * Authenticate a user via a token.
     *
     * @param mixed $token
     * @param Array $custom custom claims that must be equals (all custom fields indicated must be equals in token, this doesn't entail that the token must have only these claims)
     * @return mixed
     */
    public function authenticate($token = false, $custom = [])
    {
        if(!$token && $this->getUserModel())
            return $this->getUserModel();

        $payload = $this->getPayload($token);
        $id = $payload->get('sub');

        foreach($custom as $customK => $customV)
            if(!isset($payload[$customK]) || $customV != $payload[$customK])
                return new InvalidClaimException('custom fields are wrong');


        if (! $this->auth->byId($id)) {
            return false;
        }

        $user = $this->auth->user();
        $this->setUserModel($user);
        return $user;
    }

    /**
     * Refresh an expired token.
     *
     * @param mixed $token
     * @param Array $custom
     *
     * @return string
     */
    public function refresh($token = false, $custom = [])
    {
        $this->requireToken($token);

        return $this->manager->refresh($this->token, $custom)->get();
    }

    /**
     * Invalidate a token (add it to the blacklist).
     *
     * @param mixed $token
     *
     * @return boolean
     */
    public function invalidate($token = false)
    {
        $this->requireToken($token);

        return $this->manager->invalidate($this->token);
    }

    /**
     * Get the token.
     *
     * @return boolean|string
     */
    public function getToken()
    {
        if (! $this->token) {
            try {
                $this->parseToken();
            } catch (JWTException $e) {
                return false;
            }
        }

        return $this->token;
    }

    /**
     * Get the raw Payload instance.
     *
     * @param mixed $token
     *
     * @return \Tymon\JWTAuth\Payload
     */
    public function getPayload($token = false)
    {
        $this->requireToken($token);

        return $this->manager->decode($this->token);
    }

    /**
     * Parse the token from the request.
     * @param string $method
     * @param string $header
     * @param string $query
     * @return JWTAuth
     * @throws JWTException
     */
    public function parseToken($method = 'bearer', $header = 'authorization', $query = 'token')
    {
        if (! $token = $this->parseAuthHeader($header, $method)) {
            if (! $token = $this->request->query($query, false)) {
                throw new JWTException('The token could not be parsed from the request', 400);
            }
        }

        return $this->setToken($token);
    }

    /**
     * Parse token from the authorization header.
     *
     * @param string $header
     * @param string $method
     *
     * @return false|string
     */
    protected function parseAuthHeader($header = 'authorization', $method = 'bearer')
    {
        $header = $this->request->headers->get($header);

        if (! starts_with(strtolower($header), $method)) {
            return false;
        }

        return trim(str_ireplace($method, '', $header));
    }

    /**
     * Create a Payload instance.
     *
     * @param mixed $subject
     * @param array $customClaims
     *
     * @return \Tymon\JWTAuth\Payload
     */
    protected function makePayload($subject, array $customClaims = [])
    {
        return $this->manager->getPayloadFactory()->make(
            array_merge($customClaims, ['sub' => $subject])
        );
    }

    /**
     * Set the identifier.
     *
     * @param string $identifier
     *
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Get the identifier.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set the token.
     *
     * @param string $token
     *
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = new Token($token);

        return $this;
    }

    /**
     * Ensure that a token is available.
     *
     * @param mixed $token
     *
     * @return JWTAuth
     *
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     */
    protected function requireToken($token)
    {
        if (! $token = $token ?: $this->token) {
            throw new JWTException('A token is required', 400);
        }

        return $this->setToken($token);
    }

    /**
     * Set the request instance.
     *
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Get the JWTManager instance.
     *
     * @return \Tymon\JWTAuth\JWTManager
     */
    public function manager()
    {
        return $this->manager;
    }

    /**
     * Magically call the JWT Manager.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (method_exists($this->manager, $method)) {
            return call_user_func_array([$this->manager, $method], $parameters);
        }

        throw new \BadMethodCallException("Method [$method] does not exist.");
    }
}
