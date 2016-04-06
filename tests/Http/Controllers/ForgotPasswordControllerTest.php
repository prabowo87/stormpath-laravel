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

class ForgotPasswordControllerTest extends TestCase
{
    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        config(['stormpath.web.forgotPassword.enabled'=>true]);
    }

    /** @test */
    public function it_will_show_forgot_password_form()
    {
        $this->visit(route('stormpath.forgotPassword'))
            ->see('Forgot your password?');
    }

    /** @test */
    public function it_returns_with_notice_if_login_is_not_valid()
    {
        $this->setupStormpathApplication();
        $this->post(route('stormpath.forgotPassword'), ['email'=>'randomLogin@something.ddd']);
        $this->assertRedirectedTo(config('stormpath.web.forgotPassword.uri'));
        $this->assertSessionHasErrors(['errors'=>'Could not find an account with this email address']);
        $this->assertHasOldInput();
    }

    /** @test */
    public function a_valid_email_will_redirect_to_login_screen_with_status_forgot_and_display_message()
    {
        $this->setupStormpathApplication();
        $this->createAccount(['email'=>'test@test.com']);
        $this->post(route('stormpath.forgotPassword'), ['email'=>'test@test.com']);

        $this->assertRedirectedTo(config('stormpath.web.forgotPassword.nextUri'));
        $this->followRedirects();
        $this->seePageIs(config('stormpath.web.forgotPassword.nextUri'));
        $this->see('Password Reset Requested');

    }

    /** @test */
    public function a_valid_email_and_json_will_respond_with_a_200()
    {
        $this->setupStormpathApplication();
        $this->createAccount(['email'=>'test@test.com']);
        $this->json('post', route('stormpath.forgotPassword'), ['email'=>'test@test.com']);

        $this->assertResponseStatus(200);

    }

    /** @test */
    public function an_invalid_email_and_json_will_respond_with_a_200()
    {
        $this->setupStormpathApplication();
        $this->json('post', route('stormpath.forgotPassword'), ['email'=>'test@test.com']);

        $this->assertResponseStatus(400);
        $response = $this->decodeResponseJson();

        $this->assertEquals('Could not find an account with this email address', $response['message']);
        $this->assertEquals(400, $response['status']);

    }
}
