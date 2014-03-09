# Locker
This is a simple package that aims to facilitate the process of manage user accounts in applications built with Laravel Framework.

## Features
- Sends a confirmation code via email to new users
- Sends temporary passwords via email when the user forgets their password

## Future plans
- Automatic creation of controllers and views to handle with all actions associated with user management
- Login throttling

## How to use
First you need to tell composer how to get the package. In your `composer.json`, add the following git repository:

`git@github.com:beeblebrox3/locker.git`

Your `composer.json` will looks like:

```json
{
    [...]
    "repositories": [
        {
            "type": "git",
            "url": "git@github.com:beeblebrox3/locker.git"
        }
    ],
    "require": {
        "laravel/framework": "4.1.*",
        "beeblebrox3/locker": "1.0.0"
    },
}

```

Now, add the LockerServiceProvider to your application providers. Edit the `app/config/app.php` and add the following line to **providers** array:

`Beeblebrox3\Locker\LockerServiceProvider`

And in the **Aliases** array, add the following key and value:

`'Locker' => 'Beeblebrox3\Locker\LockerFacade'`

The next step is create the config file to the package. To do that, run the following command in your terminal

`php artisan config:publish beeblebrox3/locker`

The file `app/config/packages/beeblebrox3/locker/config.php` will be created (you can do this manually if you want). Lets see how this configuration file works.

Config | Default Value | Description
---|---|---
signup_confirmation_required | true | defines if an email with a confirmation code must to be send to new users
user_model | Beeblebrox3\Locker\LockerUser | the name of model that handle users. the package has one model, but you can specify your own that extends of this (or not ;))
subject_email_confirmation | Please confirm your account | the subject of the email sent to user confirm his account
subject_email_reset | Here is your new and temporaly password | the subject of the email sent to the user when they request a new password
route_login | /users/login | the URL of login page. This is used to create links in the default password recovery email (optional if you use your own views)
route_confirmation | /users/confirm | the URL of confirmation page. This is used to create links in the default account confirmation email (optional if you use your own views)

To customize the default views of emails, you can run the following command:
`php artisan views:publish beeblebrox3/locker`

The following files will be created:
`app/views/packages/beeblebrox3/locker/layouts/default.blade.php`
`app/views/packages/beeblebrox3/locker/emails/confirmation.blade.php`
`app/views/packages/beeblebrox3/locker/emails/reset.blade.php`

We use blade to create templates, but you dont have to. Just customize the files how you want. Laravel knows what to do.

Our API is compatible with Laravel Auth API. We have the same basic methods, to maximize compatibility and the Auth methods still working.

### Methods
#### Locker::register(array $data, $confirmation = null)
Saves a new user. The package contain a migration and a model that works great together. But you can specify your own model in the configuration file. Thats ok, but you need to make sure that your model have at least the following properties: email (varchar not null), password (varchar 60 not null), confirmed (datetime null), confirmation_code (varchar 40 null), change_password (tinyint default 0).

To Locker use your validation rules, your have two options.
A: Your model extends Locker's model and have the validation rules on a $rules property.
B: Your model provides the validations rules through getRules() method.

If validation fails, the method will return the Validator object with the errors (result of Laravel's Validator::make()).

If the new user is saved, the method will return a instance of User model.

The `$data` argument contains the new user data. The `$confirmation` allow you to force send or not the confirmation code via email. If you pass true, the email will be sent. If you pass false, the email will not be sent. This option override the default configuration (in config file). If you pass null, the default configuration will be respected.

#### Locker::sendConfirmationCode(Eloquent $user)
Send the email with the confirmation code. The register method will use this method when it needs. But if your need to resend for any reason, you can use this method.

#### Locker::confirm($code, $login = false)
Confirms a user account by the confirmation_code.
Throws Exceptions when things go wrong.
Throws 404 if the code is not found.
Throws 500 if an error occurs saving the user.
If the `$login` is set to true, the user will be logged.

#### Locker::attempt($email, $password)
Tries to login an user. Throws exceptions when things go wrong.
Throws 404 when the user is not found with this $email.
Throws 400 when the password is wrong.
Throws 401 when the account is not confirmed.

#### Locker::login(Eloquent $user)
Use this method to manually login an user.

#### Locker::logout()
Logout the current user.

#### Locker::resetPassword(Eloquent $user)
This method changes the user password to an temporaly and send via email. This method set the change_password property to 1, indicating that user must change the password in the next login. You may use that information to apply a filter to redirect user to a special page to do this action.

#### Locker::check()
Determines if a user is or not logged.

#### Locker::user()
Returns an instance of User model, that represents the current user.