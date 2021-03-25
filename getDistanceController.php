<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use App\Models\UserAddress;
use Illuminate\Support\Facades\Auth;

class GetDistanceController extends Controller
{
    public function getAddressDistance()
    {
        if (Auth::check()) {
            $users = UserAddress::all()->toArray();
            return $users;
        }
    }
    public function calculateDistance()
    {
        $users = $this->getAddressDistance();
        $arr = [];
        
        for ($i = 0; $i < count($users); $i++) {
            unset($users[$i]['created_at']);
            unset($users[$i]['updated_at']);
            unset($users[$i]['user_id']);
            unset($users[$i]['id']);
            $arr[] = implode(', ', $users[$i]);
        }

        $origin = array_shift($arr);
        $from = implode("|", $arr);
        $key = config('api.api_key');
        $elements = [];
        $rad = 4000;

        //get response
        $client = new Client([
            'headers' => ['Content-Type' => 'application/json']
        ]);
        $response = $client->post(
            'https://maps.googleapis.com/maps/api/distancematrix/json?origins=' . $origin . '&destinations=' . $from . '&key='.$key,
        );

        // output as json
        $decode = json_decode($response->getBody(), true);

        //print out the element
        foreach ($decode['rows'][0]['elements'] as $element) {
            $elements[] = $element;
        }

        //get destination address based on set radius
        for ($i = 0; $i < count($elements); $i++) {
            if ($elements[$i]['distance']['value'] <= $rad) {
                $location[] =$decode['destination_addresses'][$i]; 
            }
        }
        dd($location);
    }
}