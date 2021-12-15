<?php
class plzsearch
{

/* Für die Suchtabelle sollte eine View erstellt werden, die die Datenmenge reduziert und mit der eigenen Tabelle abgleicht: 
z.B. 

select `rex_kunden`.`id` AS `id`,
`rex_geocodes`.`place_name` AS `place_name`,
`rex_rex_kunden`.`lat` AS `lat`,
`rex_rex_kunden`.`lon` AS `lon`,
`rex_geocodes`.`country_code` AS `country_code`,
`rex_geocodes`.`postal_code` AS `postal_code`,
`rex_kunden`.`plz` AS `plz` 
from (`rex_kunden` join `rex_geocodes`) 
where `rex_geocodes`.`postal_code` = `rex_kunden`.`plz`
*/

// Sucht die entprechenden Postleitzahlen nach Längen und Breiten, es kann eine Distance-Angabe übergeben werden
public static function searchByLatLon($lat = 51.546500, $lon = 6.595200, $distance = 10, $country = 'DE', $table = 'rex_geocodes', $zip = 'postal_code')
{
    if ($distance < 10) 
    {
	    $distance = 10; 
    }
	
    $data = rex_sql::factory();	
    $data->setQuery('SELECT id, '.$zip.', lat, lon, ( 3959 * acos( cos( radians(:latvalue) ) * cos( radians( lat ) ) 
* cos( radians( lon ) - radians(:lonvalue) ) + sin( radians(:latvalue) ) * sin(radians(lat)) )) AS distance 
FROM '.$table.' WHERE country_code = :country
HAVING distance <= :distvalue 
ORDER BY distance', ['latvalue' => $lat, 'lonvalue' => $lon, 'distvalue' => $distance, 'country' => $country]);

    $datas = $data->getArray();
    $plz = [];
    if (count($datas) > 0) {
        foreach ($datas as $geo) {
            $plz[] = $geo[$zip];
        }
        return $plz;
    }
    return false;
}

// Sucht nach der übergebenen PLZ
public static function searchByPostCode($postcode, $country = 'DE')
{
    $plzsearch = rex_sql::factory();
    $plzsearch->setQuery('SELECT lat, lon, place_name FROM rex_geocodes WHERE postal_code = :plz AND country_code = :country LIMIT 1', ['plz' => $postcode, 'country' => $country]);
    if ($datas = $plzsearch->getArray())
	{		
    return $datas[0];
	}
	else return false; 
}

// Gibt alle oder die gefundenen Standorte aus, filterbar nach PLZ, Ausgabe als GeoJSON, Dataset, oder als JS-Array (latlon)
public static function getPlaces($table = null, $type = 'json', $plz = null, $title = 'name', $adress = 'strasse', $postcode = 'plz', $city = 'ort', $extra = '')
{
	$latlon = [];
    $jsondata = '';
    $table = rex_yform_manager_table::get($table);
    $query = $table->query();
    if ($plz) {
     //   $query->whereListContains('plz', $plz);
		$query->whereRaw('CONCAT(",", plz, ",") REGEXP ",(' . implode('|', $plz) . '),"');
    }
    $places = $query->find();
  
	
    if ($places) {
    foreach ($places as $place) {
        $lon = floatval($place->lon);
        $lat = floatval($place->lat);
        $coordinates =  array('type'  => 'Point', 'coordinates' => [$lon, $lat]);
        // GeoJSON
        $points[] = array(
            'type'       =>     'Feature',
            'id'        =>     $place->id,
            'geometry'    =>     $coordinates,
			'latitude'      =>     $lat,
            'longitude'      =>     $lon,
		    'description'      =>      '',
            'descriptionhtml'      =>      '
                <h4>' . $place->$title . '</h4>
                <p>' . $place->$adress . '<br/>
                ' . $place->$postcode . ' ' . $place->$city . '</p>
                <p>' . $extra . '</p>',

        );

        $latlon[] = '[' . $place->lat . ', ' . $place->lon . ']';
    } 
	 if ($type == 'latlon') {
		if (count($latlon) < 1) 
		{
			return '[0,0]' ;
		}
		else { 
        return   implode(", ", $latlon);
		}	
    }	

    if ($type == 'json') {
        $jsondata = 'var geoJsonData = { "count": 1,
        "features": ' . json_encode($points) . '
        }';

        return $jsondata;
    }

    if ($type == 'dataset') {
        return $places;
    }
    // Längen und Breitenpaare als JS-Array
   
}
else { return false;}
}
}


