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
use Stormpath\Resource\Account;
use Stormpath\Stormpath;

class RegisterControllerEventTest extends TestCase
{
    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        config(['stormpath.web.register.enabled'=>true]);

    }

    /** @test */
    public function it_fires_the_UserIsRegistering_event_before_a_successful_registration()
    {
        $this->expectsEvents(\Stormpath\Laravel\Events\UserIsRegistering::class);

        $this->setupStormpathApplication();
        config(["stormpath.web.register.autoAuthorize"=>true]);


        $this->post('register', [
            'username' => 'testUsername',
            'givenName' =>'Test',
            'middleName' => 'Middle',
            'surname' => 'Account',
            'email' => 'test@account.com',
            'password' => 'superP4ss!',
            'confirmPassword' => 'superP4ss!'
        ]);

        $this->assertTrue(cookie()->hasQueued(config('stormpath.web.accessTokenCookie.name')));
        $this->assertTrue(cookie()->hasQueued(config('stormpath.web.refreshTokenCookie.name')));

        $this->assertRedirectedTo(config('stormpath.web.register.nextUri'));
    }

    /**
     * @test
     * @expectedException \Stormpath\Laravel\Exceptions\ActionAbortedException
    */
    public function it_aborts_registration_when_the_listener_returns_false_on_UserIsRegistering_event()
    {
        \Event::listen(\Stormpath\Laravel\Events\UserIsRegistering::class, function ($event) {
            return false;
        });

        $this->setupStormpathApplication();
        config(["stormpath.web.register.autoAuthorize"=>true]);

        $this->post('register', [
            'username' => 'testUsername',
            'givenName' =>'Test',
            'middleName' => 'Middle',
            'surname' => 'Account',
            'email' => 'test@account.com',
            'password' => 'superP4ss!',
            'confirmPassword' => 'superP4ss!'
        ]);

        $this->assertNotTrue(cookie()->hasQueued(config('stormpath.web.accessTokenCookie.name')));
        $this->assertNotTrue(cookie()->hasQueued(config('stormpath.web.refreshTokenCookie.name')));
    }

    /**
     * @test
     */
    public function UserIsRegistering_event_passes_fields()
    {
        \Event::listen(\Stormpath\Laravel\Events\UserIsRegistering::class, function ($event) {
            $data = $event->getData();

            $this->assertEquals('Test', $data['givenName']);
            $this->assertEquals('Middle', $data['middleName']);
            $this->assertEquals('Account', $data['surname']);
            $this->assertEquals('test@account.com', $data['email']);
            $this->assertEquals('superP4ss!', $data['password']);
            $this->assertEquals('superP4ss!', $data['confirmPassword']);

        });

        $this->setupStormpathApplication();
        config(["stormpath.web.register.autoAuthorize"=>true]);

        $this->post('register', [
            'username' => 'testUsername',
            'givenName' =>'Test',
            'middleName' => 'Middle',
            'surname' => 'Account',
            'email' => 'test@account.com',
            'password' => 'superP4ss!',
            'confirmPassword' => 'superP4ss!'
        ]);

    }

    /** @test */
    public function it_fires_the_UserHasRegistered_event_after_successful_registration()
    {
        $this->expectsEvents(\Stormpath\Laravel\Events\UserHasRegistered::class);

        $this->setupStormpathApplication();
        config(["stormpath.web.register.autoAuthorize"=>true]);

        $this->post('register', [
            'username' => 'testUsername',
            'givenName' =>'Test',
            'middleName' => 'Middle',
            'surname' => 'Account',
            'email' => 'test@account.com',
            'password' => 'superP4ss!',
            'confirmPassword' => 'superP4ss!'
        ]);

        $this->assertTrue(cookie()->hasQueued(config('stormpath.web.accessTokenCookie.name')));
        $this->assertTrue(cookie()->hasQueued(config('stormpath.web.refreshTokenCookie.name')));

        $this->assertRedirectedTo(config('stormpath.web.register.nextUri'));
    }


    /** @test */
    public function UserHasRegistered_event_passes_account_object()
    {
        \Event::listen(\Stormpath\Laravel\Events\UserHasRegistered::class, function ($event) {
            $this->assertInstanceOf(Account::class, $event->getAccount());
        });

        $this->setupStormpathApplication();
        config(["stormpath.web.register.autoAuthorize"=>true]);

        $this->post('register', [
            'username' => 'testUsername',
            'givenName' =>'Test',
            'middleName' => 'Middle',
            'surname' => 'Account',
            'email' => 'test@account.com',
            'password' => 'superP4ss!',
            'confirmPassword' => 'superP4ss!'
        ]);
        

        $this->assertRedirectedTo(config('stormpath.web.register.nextUri'));
    }




}
