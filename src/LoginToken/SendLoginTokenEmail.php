<?php

namespace Lasallecrm\Lasallecrmemail\Logintoken;

/**
 *
 * Email handling package for the LaSalle Customer Relationship Management package.
 *
 * Based on the Laravel 5 Framework.
 *
 * Copyright (C) 2015 - 2016  The South LaSalle Trading Corporation
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @package    Email handling package for the LaSalle Customer Relationship Management package
 * @link       http://LaSalleCRM.com
 * @copyright  (c) 2015 - 2016, The South LaSalle Trading Corporation
 * @license    http://www.gnu.org/licenses/gpl-3.0.html
 * @author     The South LaSalle Trading Corporation
 * @email      info@southlasalle.com
 *
 */

// LaSalle Software
use Lasallecms\Lasallecmsapi\Repositories\UserRepository;

// Laravel facades
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;


/**
 * Class SendLoginTokenEmail
 * @package Lasallecrm\Lasallecrmemail\Logintoken
 */
class SendLoginTokenEmail
{
    /**
     * @var Lasallecms\Lasallecmsapi\Repositories\UserRepository
     */
    protected $userRepository;


    /**
     * @param Lasallecms\Lasallecmsapi\Repositories\UserRepository $userRepository
     */
    public function construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }


    /**
     * @param  int  $id   "users" table's ID
     * @return void
     */
    public function sendEmail($id) {

        // Getting a fatal exception "Call to a member function getFind() on null".
        // Really pressed for time, error is mysterious, all looks fine.
        // Using DB right here ;-(
        // TODO: fatal exception "Call to a member function getFind() on null"
        //$user = $this->userRepository->getFind($id);
        $user = DB::table('users')->where('id', $id)->first();

        $data = $this->buildEmailData($user);

        // What blade file to use?
        $emailBladeFile = 'lasallecrmemail::email.send_login_token_email';

        // Send da email
        Mail::queue($emailBladeFile, ['data' => $data], function ($message) use ($data) {

            $message->from($data['from_email_address'], $data['from_name']);
            $message->to($data['to_email_address'] , $data['to_name']);
            $message->subject($data['subject']);
        });
    }

    /**
     * @param  object  $user   User object
     * @return array
     */
    public function buildEmailData($user) {

        $data = [];

        $data['login_token_link']   = config('app.url').'/auth/login/token/'.$user->login_token;
        $data['from_name']          = config('lasallecmsfrontend.site_name');
        $data['from_email_address'] = config('lasallecmsusermanagement.administrator_first_among_equals_email');
        $data['to_name']            = $user->name;
        $data['to_email_address']   = $user->email;
        $data['subject']            = "New pictures for you!";

        return $data;
    }
}