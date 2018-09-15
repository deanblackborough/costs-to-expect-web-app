<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Config;

class AuthenticationController extends BaseController
{
    public function signIn(Request $request)
    {
        return view(
            'sign-in',
            [
                'resource' => 'Resource name'
            ]
        );
    }

    public function processSignIn(Request $request)
    {
        $client = new Client([
            'base_uri' => Config::get('web.config.api_base_url'),
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        try {
            $response = $client->post(
                Config::get('web.config.api_uri_sign_in'),
                [
                    \GuzzleHttp\RequestOptions::JSON => [
                        'email' => $request->input('email'),
                        'password' => $request->input('password')
                    ]
                ]
            );

            if ($response->getStatusCode() === 200) {
                $request->session()->put('bearer', json_decode($response->getBody(), true)['token']);
                return redirect()->action('IndexController@recent');
            } else {
                $request->session()->flush();
                return redirect()->action('AuthenticationController@signIn');
            }
        } catch (ClientException $e) {
            $request->session()->flush();
            $request->session()->save();
            return redirect()->action('AuthenticationController@signIn');
        }
    }
}