<?php

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Exception\ClientException;
use League\Flysystem\Adapter\Local;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Strategy\GreedyCacheStrategy;
use Kevinrob\GuzzleCache\Storage\FlysystemStorage;

$haltestellen = [
    44402071, # Technologiezentrum
    44402070, # Rosenstraße
    #44402209, # Blumenbrink
    44402035, # Gartenstadt
];
$fahrten = [];

$stack = HandlerStack::create();
$stack->push(
  	new CacheMiddleware(
    	new GreedyCacheStrategy(
      		new FlysystemStorage(
        		new Local("cache")
      		),
      		360
    	)
  	), 
  	"cache"
);

$client = new Client([
    "handler"  => $stack,
]);

foreach ($haltestellen as $haltestelle) {
	try {
		$response = $client->request('GET', 'http://80.146.180.107/companion-vmv/XML_DM_REQUEST?name_dm='.$haltestelle.'&type_dm=any&trITMOTvalue100=10&changeSpeed=normal&exclMOT_0=1&exclMOT_1=1&exclMOT_2=1&mergeDep=1&coordOutputFormat=NAV3&coordListOutputFormat=STRING&useAllStops=1&excludedMeans=checkbox&useRealtime=1&deleteAssignedStops=1&itOptionsActive=1&canChangeMOT=0&mode=direct&ptOptionsActive=1&limit=10&imparedOptionsActive=1&locationServerActive=1&depType=stopEvents&useProxFootSearch=0&maxTimeLoop=2&includeCompleteStopSeq=1');
	} catch (ClientException $e) {
		echo Psr7\str($e->getRequest());
    	echo Psr7\str($e->getResponse());	
	}

	if ($response->getStatusCode() == 200) {
		$station = simplexml_load_string($response->getBody());
		foreach($station->dps->dp as $fahrt) {
			$fahrten[] = [
				'Haltestelle' => (string)$fahrt->n,
				'Datum' => (string)$fahrt->st->da,
				'Uhrzeit' => (string)$fahrt->st->t,
				'UhrzeitAusgabe' => (string)$fahrt->dt->t,
				'Typ' => (string)$fahrt->m->n,
				'Nummer' => (string)$fahrt->m->nu,
				'Ziel' => (string)$fahrt->m->des,
			];
		}
	}
}

// Nach Datum und Uhrzeit sortieren
$datum = [];
$uhrzeit = [];
foreach ($fahrten as $key => $row) {
    $datum[$key]    = $row['Datum'];
    $uhrzeit[$key] = $row['Uhrzeit'];
}
array_multisort($datum, SORT_NUMERIC, SORT_ASC, $uhrzeit, SORT_NUMERIC, SORT_ASC, $fahrten);

header('Content-Type: application/javascript');
echo json_encode($fahrten);
