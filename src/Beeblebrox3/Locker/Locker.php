<?php

namespace Beeblebrox3\Locker;

class Locker {

    private $model = null;

    public function __construct() {
        // get User model
        $modelName = \Config::get('locker::use_model');
        if (!class_exists($modelName)) {
            throw new \Exception("The model {$modelName} doesn't exist");
        }

        $this->model = new $modelName;
    }

    /**
     * Register a new user
     * @param array $data user data. The keys depends of the User model, but email, password and password_confirmation are required
     * @return Eloquent the new user
     */
    public function register(array $data, $confirmationEmail = null) {
        // validate
        $validation = \Validator::make($data, $this->model->getValidationRules());
        if ($validation->fails()) {
            return $validation;
        }

        if (is_null($confirmationEmail)) {
            $$confirmationEmail = \Config::get('locker::signup_confirmation_required');
        }

        if ($$confirmationEmail === true) {
            $data['confirmation_code'] = md5(uniqid(rand(), true));
            $data['change_password'] = 0;
        } else {
            $data['confirmed'] = date('Y-m-d H:i:s');
            $data['change_password'] = 0;
        }

        // save
        $data['password'] = \Hash::make($data['password']);
        $newUser = $this->model->create($data);

        if ($$confirmationEmail === true) {
            $this->sendConfirmationCode($data['email']);
        }


        // return
        return $newUser;

    }

    /**
     * Sends an email to user identified by $email with the confirmation code to activate the account
     * @param string $email
     * @return boolean
     */
    public function sendConfirmationCode(\Eloquent $user) {
        \Mail::send(
            'locker::emails.confirmation',
            array('user' => $user, 'subject' => \Config::get('locker::subject_email_confirmation')),
            function ($message) use ($user) {
                $message->to($user->email, $user->name)->subject(\Config::get('locker::subject_email_confirmation'));
            }
        );
    }

    /**
     * Confirm user with confirmation_code equals $code
     * @param string $code
     * @return boolean
     */
    public function confirm ($code) {
        $user = $this->model->where('confirmation_code', '=', $code)->first();

        if (!$user) {
            throw new Exception("Code not found", 404);
        }

        $user->confirmed = date('Y-m-d H:i:s');
        $user->confirmation_code = null;

        return $user->save();
    }

    /**
     * Try to make login
     * @param string $email
     * @param string $password
     * @return boolean
     */
    public function attempt($email, $password) {
        $user = $this->model->where('email', '=', $email)->first();
        if (!$user) {
            throw new \Exception('User not found', 404);
        }

        if (!\Hash::check($password, $user->password)) {
            throw new \Exception("Wrong password", 400);
        }

        if (is_null($user->confirmed)) {
            throw new \Exception("User not confirmed", 401);
        }

        $this->login($user);
        return true;
    }

    /**
     * Change the password of the user identified by $email and sends an e-mail with the new one
     * @param string $email
     * @return boolean
     */
    public function resetPassword(\Eloquent $user) {
        if (is_null($user->confirmed)) {
            throw new Exception('user not confirmed', 401);
        }
        
        $newPassword = $this->generateNewPassword();
        $user->password = \Hash::make($newPassword);
        $user->change_password = 1;
        $user->save();

        \Mail::send(
        'locker::emails.reset',
            array('user' => $user, 'password' => $newPassword, 'subject' => \Config::get('locker::subject_email_reset')),
            function ($message) use ($user) {
                $message->to($user->email, $user->name)->cc('luis.faria@cohros.com.br')->subject(\Config::get('locker::subject_email_confirmation'));
            }
        );
    }

    public function login(\Eloquent $user) {
        \Auth::login($user);
    }

    /**
     *
     */
    public function logout() {
        \Auth::logout();
    }

    /**
     * checks if current user is authenticated
     */
    public function check() {
        return \Auth::check();
    }
    
    private function generateNewPassword() {
        return substr(md5(uniqid(rand(), true)), 2, 6);
    }
}