<?php
/**
 * Created by PhpStorm.
 * User: Claudio Cardinale <cardi@thecsea.it>
 * Date: 18/11/15
 * Time: 16.35
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

namespace Tymon\JWTAuth\Support\auth;


use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Password;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Class ResetsPasswords
 * @package Tymon\JWTAuth\Support\auth
 * @author Claudio Cardinale <cardi@thecsea.it>
 * @copyright 2015 Claudio Cardinale
 * @version 1.0.0
 */
trait ResetsPasswords
{

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postEmail(Request $request)
    {
        $this->validate($request, $this->validator());


        $usernames = $this->loginUsername();
        if(!is_array($usernames)) {
            $usernames = [$usernames];
        }

        $response = Password::sendResetLink($request->only($usernames), function (Message $message) {
            $message->subject($this->getEmailSubject());
        });

        switch ($response) {
            case Password::RESET_LINK_SENT:
                return new JsonResponse([], 200);

            case Password::INVALID_USER:
                return new JsonResponse(['error' => trans($response)],422);
            default:
                return new JsonResponse(['error' => trans($response)],422);
        }
    }

    /**
     * Array for validator
     *
     * @return array
     */
    protected function validator()
    {
        return ['email' => 'required|email'];
    }

    /**
     * Get the e-mail subject line to be used for the reset link email.
     *
     * @return string
     */
    protected function getEmailSubject()
    {
        return property_exists($this, 'subject') ? $this->subject : 'Your Password Reset Link';
    }


    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postReset(Request $request)
    {
        $this->validate($request, array_merge($this->validator(), [
            'token' => 'required',
            'password' => 'required|confirmed|min:6',
        ]));

        $usernames = $this->loginUsername();
        if(!is_array($usernames)) {
            $usernames = [$usernames];
        }

        $credentials = $request->only(
            array_merge($usernames , ['password', 'password_confirmation', 'token'])
        );

        $intResp = '';
        $response = Password::reset($credentials, function ($user, $password) use(&$intResp) {
            $intResp = $this->resetPassword($user, $password);
        });

        switch ($response) {
            case Password::PASSWORD_RESET:
                return $intResp;

            default:
                return new JsonResponse(['error' => trans($response)],422);
        }
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return sttring
     */
    protected function resetPassword($user, $password)
    {
        $user->password = bcrypt($password);

        $user->save();

        $token = JWTAuth::fromUser($user, $this->customClaims());

        return new JsonResponse(['token' => $token], 200);

    }

    /**
     * Get the custom claims
     *
     * @return string
     */
    protected function customClaims()
    {
        return property_exists($this, 'custom') ? $this->custom : [];
    }

    /**
     * Get the login username to be used by the controller.
     * it can be an array for multiple username (for example email and phone number)
     *
     * @return string|array
     */
    public function loginUsername()
    {
        return property_exists($this, 'username') ? $this->username : 'email';
    }
}