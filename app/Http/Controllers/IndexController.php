<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Config;

class IndexController extends BaseController
{
    public function index(Request $request)
    {
        if ($request->session()->has('bearer') === true) {
            return redirect()->action('IndexController@recent');
        } else {
            $request->session()->flush();
            $request->session()->save();
            return redirect()->action('AuthenticationController@signIn');
        }
    }

    public function recent(Request $request)
    {
        $items = null;

        if ($request->session()->has('bearer') === false) {
            $request->session()->flush();
            $request->session()->save();
            return redirect()->action('IndexController@index');
        }

        $client = new Client([
            'base_uri' => Config::get('web.config.api_base_url'),
            'headers' => [
                //'Authorization' => 'Bearer ' . $request->session()->get('bearer'),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        try {
            $response = $client->get(Config::get('web.config.api_uri_recent'));

            if ($response->getStatusCode() === 200) {
                $items = (json_decode($response->getBody(), true));
            }
        } catch (ClientException $e) {
            return redirect()->action('IndexController@index');
        }

        if ($items !== null) {
            return view(
                'recent',
                [
                    'items' => $items
                ]
            );
        }
    }
}
