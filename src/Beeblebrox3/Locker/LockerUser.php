<?php

namespace Beeblebrox3\Locker;

use Illuminate\Auth\UserInterface;

class LockerUser extends \Eloquent implements UserInterface {
    /**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('password');

    protected $fillable = array (
        'name', 'email', 'password', 'confirmed', 'confirmation_code', 'change_password'
    );

    protected $rules = array (
        'name' => 'required|min:4',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:4|confirmed',
        'password_confirmation' => 'min:4',
    );

	/**
	 * Get the unique identifier for the user.
	 *
	 * @return mixed
	 */
	public function getAuthIdentifier()
	{
		return $this->getKey();
	}

	/**
	 * Get the password for the user.
	 *
	 * @return string
	 */
	public function getAuthPassword()
	{
		return $this->password;
	}

    public function getValidationRules () {
        return $this->rules;
    }
}