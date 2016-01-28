<?php
/*
 * Copyright 2015 Stormpath, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Stormpath\Tests\Http\Controllers;

use Stormpath\Laravel\Tests\TestCase;
use Stormpath\Stormpath;

class RegisterControllerTest extends TestCase
{

    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        config(['stormpath.web.register.enabled'=>true]);

    }

    /** @test */
    public function it_requires_a_username_if_set_to_required()
    {
        $this->registerWithout('username', 'Username is required.');
    }

    /** @test */
    public function it_requires_a_given_name_if_set_to_required()
    {
        $this->registerWithout('givenName', 'Given name is required.');
    }

    /** @test */
    public function it_requires_a_middle_name_if_set_to_required()
    {
        $this->registerWithout('middleName', 'Middle name is required.');
    }

    /** @test */
    public function it_requires_a_surname_if_set_to_required()
    {
        $this->registerWithout('surname', 'Surname is required.');
    }

    /** @test */
    public function it_requires_a_email_if_set_to_required()
    {
        $this->registerWithout('email', 'Email is required.');
    }

    /** @test */
    public function it_requires_a_password_if_set_to_required()
    {
        $this->registerWithout('password', 'Password is required.');
    }

    /** @test */
    public function it_requires_a_password_confirm_if_set_to_required()
    {
        $this->registerWithout('passwordConfirm', 'Password confirmation is required.');
    }

    /** @test */
    public function it_requires_password_to_be_confirmed_if_password_confirm_set_to_required()
    {
        config(["stormpath.web.register.fields.passwordConfirm.required"=>true]);
        $this->post('register', [
            'username' => 'testUsername',
            'givenName'=>'Test',
            'middleName' => 'Middle',
            'surname' => 'Account',
            'email' => 'test@account.com',
            'password' => 'superP4ss!',
            'password_confirmation' => 'superP4ss'
        ]);
        $this->assertRedirectedTo(config('stormpath.web.register.uri'));
        $this->assertSessionHasErrors(['password'=>'Passwords are not the same.']);
        $this->assertHasOldInput();
        $this->followRedirects();
        $this->seePageIs(config('stormpath.web.register.uri'));
        $this->see('Create Account');

    }

    /** @test */
    public function it_auto_authenticates_during_successful_registration_if_enabled()
    {
        $this->setupStormpathApplication();
        config(["stormpath.web.register.autoAuthorize"=>true]);

        $this->post('register', [
            config('stormpath.web.register.form.fields.username.name') => 'testUsername',
            config('stormpath.web.register.form.fields.givenName.name')=>'Test',
            config('stormpath.web.register.form.fields.middleName.name') => 'Middle',
            config('stormpath.web.register.form.fields.surname.name') => 'Account',
            config('stormpath.web.register.form.fields.email.name') => 'test@account.com',
            config('stormpath.web.register.form.fields.password.name') => 'superP4ss!',
            config('stormpath.web.register.form.fields.passwordConfirm.name') => 'superP4ss!'
        ]);

        $this->seeCookie(config('stormpath.web.accessTokenCookie.name'));
        $this->seeCookie(config('stormpath.web.refreshTokenCookie.name'));

        $this->assertRedirectedTo(config('stormpath.web.register.nextUri'));
    }

    /** @test */
    public function it_does_not_authenticate_during_successful_registration_if_disabled()
    {
        $this->setupStormpathApplication();
        config(["stormpath.web.register.autoAuthorize"=>false]);

        $this->post('register', [
            config('stormpath.web.register.form.fields.username.name') => 'testUsername',
            config('stormpath.web.register.form.fields.givenName.name')=>'Test',
            config('stormpath.web.register.form.fields.middleName.name') => 'Middle',
            config('stormpath.web.register.form.fields.surname.name') => 'Account',
            config('stormpath.web.register.form.fields.email.name') => 'test@account.com',
            config('stormpath.web.register.form.fields.password.name') => 'superP4ss!',
            config('stormpath.web.register.form.fields.passwordConfirm.name') => 'superP4ss!'
        ]);

        $this->seeNotCookie(config('stormpath.web.accessTokenCookie.name'));
        $this->seeNotCookie(config('stormpath.web.refreshTokenCookie.name'));

        $this->assertRedirectedToRoute('stormpath.login', ['status'=>'created']);
        $this->followRedirects();
        $this->see('Login');
        $this->seePageIs(config('stormpath.web.login.uri') . '?status=created');

    }

    /** @test */
    public function it_returns_to_registration_if_login_is_already_taken()
    {
        $this->setupStormpathApplication();
        config(["stormpath.web.register.autoAuthorize"=>false]);

        $account = $this->createAccount(['username'=>'testUsername', 'email' => 'test@account.com', 'password' => 'superP4ss!']);

        $this->post('register', [
            config('stormpath.web.register.form.fields.username.name') => 'testUsername',
            config('stormpath.web.register.form.fields.givenName.name')=>'Test',
            config('stormpath.web.register.form.fields.middleName.name') => 'Middle',
            config('stormpath.web.register.form.fields.surname.name') => 'Account',
            config('stormpath.web.register.form.fields.email.name') => 'test@account.com',
            config('stormpath.web.register.form.fields.password.name') => 'superP4ss!',
            config('stormpath.web.register.form.fields.passwordConfirm.name') => 'superP4ss!'
        ]);

        $this->assertRedirectedTo(config('stormpath.web.register.uri'));
        $this->assertSessionHasErrors(['errors'=>'Account with that email already exists.  Please choose another email.']);
        $this->assertHasOldInput();
        $this->followRedirects();
        $this->seePageIs('register');
        $this->see('Create Account');

        $account->delete();
    }

    /** @test */
    public function it_redirects_to_login_with_unverified_flag_if_directory_requires_verification_of_account()
    {
        $this->setupStormpathApplication();
        $accountStoreMappings = $this->application->accountStoreMappings;

        if ($accountStoreMappings) {
            foreach ($accountStoreMappings as $asm) {
                $directory = $asm->accountStore;
                $acp = $directory->accountCreationPolicy;
                $acp->verificationEmailStatus = Stormpath::ENABLED;
                $acp->save();
            }
        }

        config(["stormpath.web.verifyEmail.enabled"=>true]);
        config(["stormpath.web.register.autoAuthorize"=>true]);

        $this->post('register', [
            config('stormpath.web.register.form.fields.username.name') => 'testUsername',
            config('stormpath.web.register.form.fields.givenName.name')=>'Test',
            config('stormpath.web.register.form.fields.middleName.name') => 'Middle',
            config('stormpath.web.register.form.fields.surname.name') => 'Account',
            config('stormpath.web.register.form.fields.email.name') => 'test@account.com',
            config('stormpath.web.register.form.fields.password.name') => 'superP4ss!',
            config('stormpath.web.register.form.fields.passwordConfirm.name') => 'superP4ss!'
        ]);

        $this->assertRedirectedToRoute('stormpath.login',['status'=>'unverified']);
        $this->followRedirects();
        $this->seePageIs('login?status=unverified');
        $this->see('Login');


    }



    private function registerWithout($field, $errorMessage = '')
    {
        $without = [];
        $fieldName = config("stormpath.web.register.form.fields.{$field}.name");
        $without[$fieldName] = null;

        config(["stormpath.web.register.form.fields.{$field}.enabled"=>true]);
        config(["stormpath.web.register.form.fields.{$field}.required"=>true]);
        $this->post('register', array_merge([
            config('stormpath.web.register.form.fields.username.name') => 'testUsername',
            config('stormpath.web.register.form.fields.givenName.name')=>'Test',
            config('stormpath.web.register.form.fields.middleName.name') => 'Middle',
            config('stormpath.web.register.form.fields.surname.name') => 'Account',
            config('stormpath.web.register.form.fields.email.name') => 'test@account.com',
            config('stormpath.web.register.form.fields.password.name') => 'superP4ss!',
            config('stormpath.web.register.form.fields.passwordConfirm.name') => 'superP4ss!'
        ], $without));

        $this->assertRedirectedTo(config('stormpath.web.register.uri'));
        $this->assertSessionHasErrors([$fieldName=>$errorMessage]);
        $this->assertHasOldInput();
        $this->followRedirects();
        $this->seePageIs(config('stormpath.web.register.uri'));
        $this->see('Create Account');


        return $this;
    }

    /** @test */
    public function request_to_register_with_json_accept_returns_json_response()
    {
        $this->setupStormpathApplication();

        $this->json('get', config('stormpath.web.register.uri'))
            ->seeJson();

        $this->see('csrf');
        $this->see('email');
        $this->see('password');
        $this->see('accountStores');
        $this->assertResponseOk();

    }

    /** @test */
    public function posting_to_register_with_json_returns_account_object_as_json()
    {
        $this->setupStormpathApplication();

        $this->json(
            'post',
            config('stormpath.web.register.uri'),
            [
                '_token' => csrf_token(),
                config('stormpath.web.register.form.fields.username.name') => 'testUsername',
                config('stormpath.web.register.form.fields.givenName.name')=>'Test',
                config('stormpath.web.register.form.fields.middleName.name') => 'Middle',
                config('stormpath.web.register.form.fields.surname.name') => 'Account',
                config('stormpath.web.register.form.fields.email.name') => 'test@account.com',
                config('stormpath.web.register.form.fields.password.name') => 'superP4ss!',
                config('stormpath.web.register.form.fields.passwordConfirm.name') => 'superP4ss!'
            ]
        )
            ->seeJson();

        $this->dontSee('errors');
        $this->see('account');
        $this->see('test@account.com');
        $this->assertResponseOk();


    }


    /** @test */
    public function posting_to_register_with_json_with_missing_fields_returns_json_error_with_validator_errors()
    {
        $this->setupStormpathApplication();

        $this->json(
            'post',
            config('stormpath.web.register.uri'),
            [
                '_token' => csrf_token(),
            ]
        )
            ->seeJson();


        $this->see('errors');
        $this->see('validatonErrors');
        $this->dontSee('account');

        $this->assertResponseStatus(400);
    }

    /** @test */
    public function posting_to_register_with_json_when_SDK_errors_returns_json_with_error()
    {
        $this->setupStormpathApplication();
        $account = $this->createAccount(['login' => 'test@test.com', 'password' => 'superP4ss!']);

        $this->json(
            'post',
            config('stormpath.web.register.uri'),
            [
                '_token' => csrf_token(),
                config('stormpath.web.register.form.fields.username.name') => 'testUsername',
                config('stormpath.web.register.form.fields.givenName.name')=>'Test',
                config('stormpath.web.register.form.fields.middleName.name') => 'Middle',
                config('stormpath.web.register.form.fields.surname.name') => 'Account',
                config('stormpath.web.register.form.fields.email.name') => 'test@test.com',
                config('stormpath.web.register.form.fields.password.name') => 'superP4ss!',
                config('stormpath.web.register.form.fields.passwordConfirm.name') => 'superP4ss!'
            ]
        )
            ->seeJson();


        $this->see('errors');
        $this->dontSee('test@test.com');

        $this->assertResponseStatus(409);
        $account->delete();
    }


    /** @test */
    public function it_saves_custom_fields_in_customData_upon_successful_registration()
    {
        $this->setupStormpathApplication();

        // update the form fields config to add my customData fields
        config([
            'stormpath.web.register.form.fields.customData1' => [
                'enabled' => true,
                'name' => 'customData1',
                'placeholder' => 'customData1',
                'required' => true,
                'type' => 'text',
            ]
        ]);

        config([
            'stormpath.web.register.form.fields.customData2' => [
                'enabled' => true,
                'name' => 'customData2',
                'placeholder' => 'customData2',
                'required' => true,
                'type' => 'text',
            ]
        ]);

        config([
            'stormpath.web.register.form.fields.customData3' => [
                'enabled' => true,
                'name' => 'customData3',
                'placeholder' => 'customData3',
                'required' => true,
                'type' => 'text',
            ]
        ]);



        $this->post('register', [
            config('stormpath.web.register.form.fields.username.name') => 'testUsername',
            config('stormpath.web.register.form.fields.givenName.name')=>'Test',
            config('stormpath.web.register.form.fields.middleName.name') => 'Middle',
            config('stormpath.web.register.form.fields.surname.name') => 'Account',
            config('stormpath.web.register.form.fields.email.name') => 'test@account.com',
            config('stormpath.web.register.form.fields.password.name') => 'superP4ss!',
            config('stormpath.web.register.form.fields.passwordConfirm.name') => 'superP4ss!',

            config('stormpath.web.register.form.fields.customData1.name') => 'a value',
            config('stormpath.web.register.form.fields.customData2.name') => 'another value',
            config('stormpath.web.register.form.fields.customData3.name') => 'something',
        ]);

        // get the application object
        $application = app('stormpath.application');

        // find the account we just created
        $accounts = $application->accounts;
        $accounts->search = [config('stormpath.web.register.form.fields.email.name') => 'test@account.com'];

        // make sure we got exactly 1 account in this search
        $this->assertEquals(1, $accounts->getSize());

        // get the account
        $account = $accounts->getIterator()->current();

        // test the custom data values
        $this->assertEquals('a value', $account->customData->customData1);
        $this->assertEquals('another value', $account->customData->customData2);
        $this->assertEquals('something', $account->customData->customData3);

    }


}
