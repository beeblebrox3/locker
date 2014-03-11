<?php

return array (
    /**
     * defines if an email with a confirmation code must to be send to new users
     */
    'signup_confirmation_required' => true,

    /**
     * the name of model that handle users. the package has one, but you
     * can specify your own model
     */
    'user_model' => 'Beeblebrox3\Locker\LockerUser',

    /**
     * the url of login page. Is used to build the default reset password email.
     * It is optional if you use your own views
     */
    'route_login' => '/users/login',

    /**
     * the url of login page. Is used to build the default confirm account email.
     * It is optional if you use your own views
     */
    'route_confirmation' => '/users/confirm',
);
