<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\Drivers\AmazonAlexa\AmazonAlexaDriver;

class EventManagerController extends Controller
{
    public function searchEvent(Request $request)
    {
    	DriverManager::loadDriver(AmazonAlexaDriver::class);
		$botman = BotManFactory::create($config = []);

		$botman->hears('SearchEvent', function($bot) {
			$events = [
				[
					'title' => 'MRPL IT CONF 2018',
					'city' => 'Mariupol',
					'category' => 'it'
				],
				[
					'title' => 'MRPL FEST 2019',
					'city' => 'Mariupol',
					'category' => 'music'
				]
			];

			$slots = $bot->getMessage()->getExtras('slots');

			$category = $slots['EventCategory']['value'] ?? false;
			$city = $slots['City']['value'] ?? false;

			$list = [];

			foreach($events as $event) {
				$found = true;

				switch(true) {
					case ($category && $city): 
						$found = (strtolower($event['city']) == strtolower($city) && strtolower($event['category']) == strtolower($category));
						break;
					case ($category):
						$found = (strtolower($event['category']) == strtolower($category));
						break;
					case ($city):
						$found = (strtolower($event['city']) == strtolower($city));
						break;
				}

				if($found) {
					$list[] = $event['title'];
				}
			}

			if($list) {
				$response = 'The upcoming ';
				$response .= $category ? $category . ' ' : '';
				$response .= 'events ';
				$response .= $city ? 'in ' . ucfirst($city) . ' ' : '';
				$response .= 'are:' . PHP_EOL;
				$response .= implode(','.PHP_EOL, $list);
			} else {
				$response = 'I wasn\'t able to find any events';
			}

			$bot->reply($response, [
				'shouldEndSession' => true
			]);
		});

		$botman->listen();
    }
}
