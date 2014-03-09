<?php

namespace Beeblebrox3\Locker;

class Locker
{

    private $model = null;

    public function __construct()
    {
        $modelName = \Config::get('locker::user_model');
        if (!class_exists($modelName)) {
            throw new \Exception("The model {$modelName} doesn't exist");
        }

        $this->model = new $modelName;
    }

    /**
     * @param array $data
     * @param boolean $confirmation force to send or not the confirmation code
     * @return Eloquent the new user
     */
    public function register(array $data, $confirmation = null)
    {
        $validation = \Validator::make($data, $this->model->getValidationRules());
        if ($validation->fails()) {
            return $validation;
        }

        if (is_null($confirmation)) {
            $confirmation = \Config::get('locker::signup_confirmation_required');
        }

        if ($confirmation === true) {
            $data['confirmation_code'] = md5(uniqid(rand(), true));
        } else {
            $data['confirmed'] = date('Y-m-d H:i:s');
        }

        if (!isset($data['change_password'])) {
            $data['change_password'] = 0;
        }

        $data['password'] = \Hash::make($data['password']);
        $newUser = $this->model->create($data);

        if ($confirmation === true) {
            $this->sendConfirmationCode($newUser);
        }

        return $newUser;

    }

    /**
     * @param string $email
     * @return boolean
     */
    public function sendConfirmationCode(\Eloquent $user)
    {
        \Mail::send(
            'locker::emails.confirmation',
            array('user' => $user, 'subject' => \Config::get('locker::subject_email_confirmation')),
            function ($message) use ($user) {
                $message->to($user->email, $user->name)->subject(\Config::get('locker::subject_email_confirmation'));
            }
        );
    }

    /**
     * @param string $code
     * @return boolean
     */
    public function confirm ($code, $login = false)
    {
        $user = $this->model->where('confirmation_code', '=', $code)->first();

        if (!$user) {
            throw new Exception("Code not found", 404);
        }

        $user->confirmed = date('Y-m-d H:i:s');
        $user->confirmation_code = null;

        $save = $user->save();
        if ($save && $login === true) {
            $this->login($user);
        } elseif (!$save) {
            throw new Exception("Error saving user", 500);
        }
    }

    /**
     * @param string $email
     * @param string $password
     * @return boolean
     */
    public function attempt($email, $password)
    {
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
     * @param string $email
     * @return boolean
     */
    public function resetPassword(\Eloquent $user)
    {
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
                $message->to($user->email, $user->name)->subject(\Config::get('locker::subject_email_confirmation'));
            }
        );
    }

    public function login(\Eloquent $user)
    {
        \Auth::login($user);
    }

    public function logout()
    {
        \Auth::logout();
    }

    public function check()
    {
        return \Auth::check();
    }

    private function generateNewPassword()
    {
        return substr(md5(uniqid(rand(), true)), 2, 6);
    }

    public function user()
    {
        return \Auth::user();
    }
}
