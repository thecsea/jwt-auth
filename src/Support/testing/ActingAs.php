<?php
/**
 * Created by PhpStorm.
 * User: Claudio Cardinale <cardi@thecsea.it>
 * Date: 19/11/15
 * Time: 22.48
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

namespace Tymon\JWTAuth\Support\testing;

use \Illuminate\Contracts\Auth\Authenticatable;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Class ActingAs
 * @package Tymon\JWTAuth\Support\testing
 * @author Claudio Cardinale <cardi@thecsea.it>
 * @copyright 2015 Claudio Cardinale
 * @version 1.0.0
 */
trait ActingAs
{
    /**
     * Set the currently logged in user for the application.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  string|null  $driver
     * @return $this
     */
    public function actingAs(Authenticatable $user, $driver)
    {
        JWTAuth::setUserModel($user);

        return $this;
    }
}