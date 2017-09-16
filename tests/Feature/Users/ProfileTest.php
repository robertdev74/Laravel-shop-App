<?php

/*
 * This file is part of the Antvel Shop package.
 *
 * (c) Gustavo Ocanto <gustavoocanto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Users\Feature;

use Tests\TestCase;
use Antvel\Users\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Antvel\Users\Events\ProfileWasUpdated;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ProfileTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $this->user = factory(User::class)->create()->first();
    }

    protected function submit($request)
    {
        return $this->patch('user/' . $this->user->id, $request);
    }

    protected function validData($overwrites = [])
    {
        return array_merge([
            'first_name' => 'Gustavo',
            'last_name' => 'Ocanto',
            'gender' => 'male',
            'referral' => 'profile',
            'email' => 'foo@bar.com',
            'nickname' => 'foobar'
        ], $overwrites);
    }

    /** @test */
    function an_authorized_can_see_his_profile_page()
    {
        $this->actingAs($this->user)->get(route('user.index'))->assertSuccessful();
    }

    /** @test */
    function an_unauthorized_user_cannot_manage_profiles()
    {
        $this->submit(['referral' => 'profile', 'email' => 'foo@bar.com', 'nickname' => 'foobar'])
            ->assertStatus(302)
            ->assertRedirect('/login');
    }

    /** @test */
    function an_authorized_user_is_able_to_update_his_profile()
    {
        Event::fake();

        $this->actingAs($this->user)->submit($this->validData());

        Event::assertDispatched(ProfileWasUpdated::class, function ($e) {
            return $e->request['email'] = 'foo@bar.com'
                && $e->request['referal'] = 'profile'
                && $e->request['nickname'] = 'foobar';
        });
    }

    /** @test */
    function the_update_request_requires_the_referral_section_to_authorize_the_petition()
    {
        $this->actingAs($this->user)
            ->submit(['email' => 'foo@bar.com', 'nickname' => 'foobar'])
            ->assertStatus(403);
    }

    /** @test */
    function an_authorized_user_can_update_his_profile_picture()
    {
        Storage::fake('images');

        $this->actingAs($this->user)->patch(route('user.update', ['user' => $this->user]), $this->validData([
            'referral' => 'upload',
            'pictures' => [
                'storing' => [
                    $file = UploadedFile::fake()->image('avatar.jpg'),
                ]
            ],
        ]));

        tap($this->user->fresh(), function ($user) use ($file) {
            $this->assertEquals('images/avatars/' . $file->hashName(), $user->image);
            Storage::disk('images')->assertExists('avatars/' . $file->hashName());
            $this->assertNotNull($user->image);
        });
    }

    /** @test */
    function an_unauthorized_user_cannot_update_his_profile_picture()
    {
        $response = $this->json('PATCH', route('user.update', ['user' => $this->user]), [
            'referral' => 'upload',
            'file' => $file = UploadedFile::fake()->image('avatar.jpg'),
        ]);

        $this->assertEquals($this->user->pic_url, $this->user->fresh()->pic_url);

        $error = $response->decodeResponseJson();
        $this->assertEquals('Unauthenticated.', $error['message']);
        $response->assertStatus(401);
    }
}
