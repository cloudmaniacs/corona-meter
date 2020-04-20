<?php

# TOTAL/WORLD:
$country = '';

# OR CHOOSE A COUNTRY:
#$country = 'China';
#$country = 'Germany';
#$country = 'Switzerland';
#$country = 'Italy';
#$country = 'United Kingdom';
#$country = 'US';

$countries = ['China', 'United Kingdom', 'US'];

#$base = 'https://services9.arcgis.com/N9p5hsImWXAccRNI/arcgis/rest/services/Z7biAeD8PAkqgmWhxG2A/FeatureServer/1/query';
$base = 'https://services9.arcgis.com/N9p5hsImWXAccRNI/arcgis/rest/services/Nc2JKvYFoAEOFCG5JSI6/FeatureServer/2/query';
$referer = 'https://gisanddata.maps.arcgis.com/apps/opsdashboard/index.html';

$opts = array('http'=>array('header'=>array("Referer: $referer\r\n")));
$context = stream_context_create($opts);

if ($country == '' || in_array($country, $countries)) {

  $values = ['confirmed', 'deaths', 'recovered'];

  if (in_array($country, $countries)) {

    $query = '(Confirmed%20%3E%200)%20AND%20(Country_Region%3D%27' . urlencode($country) . '%27)';

  } else {

    $country = 'TOTAL';
    $query = 'Confirmed%20%3E%200';

  }

  foreach ($values as $value) {

    # hotfix for the US recovered value
    if ($country == 'US' && $value == 'recovered') { $query = 'OBJECTID%3D18'; }

    $url = $base . '?f=json&where=' . $query . '&outFields=*&outStatistics=%5B%7B%22statisticType%22%3A%22sum%22%2C%22onStatisticField%22%3A%22' . $value . '%22%2C%22outStatisticFieldName%22%3A%22value%22%7D%5D';

    $data = file_get_contents($url, false, $context);
    $json = json_decode($data, true);

    $$value = $json['features'][0]['attributes']['value'];

  }

} else {

  $url = $base . '?f=json&where=(Country_Region%3D%27' . urlencode($country) . '%27)&outFields=Country_Region,Confirmed,Deaths,Recovered';

  $data = file_get_contents($url, false, $context);
  $json = json_decode($data, true);

  $attributes = $json['features'][0]['attributes'];

  # DEBUG:
  #print_r($attributes);

  #$country = $attributes['Country_Region'];
  $confirmed = $attributes['Confirmed'];
  $deaths = $attributes['Deaths'];
  $recovered = $attributes['Recovered'];

}

$leds = 8;
$pattern = '';

# choose between round or ceil
$deaths_leds = round(($leds/$confirmed)*$deaths);
$recovered_leds = round(($leds/$confirmed)*$recovered);
$confirmed_leds = $leds - $deaths_leds - $recovered_leds;

for ($i = 1; $i <= $deaths_leds; $i++) { $pattern .= ' R'; }
for ($i = 1; $i <= $confirmed_leds; $i++) { $pattern .= ' B'; }
for ($i = 1; $i <= $recovered_leds; $i++) { $pattern .= ' G'; }

$deaths_percentage = round((100/$confirmed)*$deaths, 2);
$recovered_percentage = round((100/$confirmed)*$recovered, 2);

print($country . ': ' . $confirmed . ' confirmed, ' . $recovered . ' recovered (' . $recovered_percentage . '%), ' . $deaths . ' deaths (' . $deaths_percentage . '%)');
print(' > LEDs: ' . $deaths_leds . '/' . $confirmed_leds . '/' . $recovered_leds . ', Pattern: [' . $pattern . ' ]' . PHP_EOL);

exec('python ' . __DIR__ . '/led.py' . $pattern);
