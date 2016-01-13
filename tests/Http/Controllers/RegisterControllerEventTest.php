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


/*
it fires the userIsRegistering event before a successful registration
it fires the userHasRegistered event after successful registration
*/
    /** @test */
    public function it_fires_the_userIsRegistering_event_before_a_successful_registration()
    {
        $this->expectsEvents(\Stormpath\Laravel\Events\UserIsRegistering::class);

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




}
