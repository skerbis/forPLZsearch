<?php
class plzsearch
{

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
HAVING distance < :distvalue 
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
    $datas = $plzsearch->getArray();
    return $datas[0];
}

// Gibt alle oder die gefundenen Standorte aus, filterbar nach PLZ, Ausgabe als GeoJSON, Dataset, oder als JS-Array (latlon)
public static function getPlaces($table = null, $type = 'json', $plz = null, $title = 'name', $adress = 'strasse', $postcode = 'plz', $city = 'ort', $extra = '')
{
    $jsondata = '';
    $table = rex_yform_manager_table::get($table);
    $query = $table->query();
    if ($plz) {
        $query->whereListContains('plz', $plz);
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
    if ($type == 'latlon') {
        return   implode(", ", $latlon);
    }
}
else { return false;}
}
}


