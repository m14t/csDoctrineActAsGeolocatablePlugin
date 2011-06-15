<?php


/**
 * Geocode an address using Google's Geocoder API
 *
 * @package    Locatable_Extension
 * @subpackage geocoder
 * @author     Matt Farmer <work@mattfarmer.net>
 * @link       http://code.google.com/apis/maps/documentation/geocoding/index.html
 */
class GoogleGeoCoder
{

  protected
    $geo_url = "http://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=",
    $logger = null,
    $data = array();


  public function __construct( $geocode )
  {
    $this->geocode = $geocode;
    $this->__fetchData();
  }


  protected function __fetchData()
  {
    $url = $this->geo_url . urlencode($this->geocode);

    $session = curl_init($url);
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
    $res = json_decode(curl_exec($session));
    if ( !$res )
    {
      //-- TODO: Pull out the curl response error code here.
      $this->log('err', 'Error looking up location ('.$this->geocode.').  No response given.');
    }
    else
    {
      if ( "OK" != $res->status )
      {
        $this->log('err', 'Error looking up location ('.$this->geocode.').  Status: '.$res->status);
      }
      else
      {
        $this->__parseData( $res->results[0] );
      }
    }
  }

  protected function __parseData($result)
  {
    foreach( $result->address_components as $comp )
    {
      foreach( $comp->types as $googleKey ) {
        switch ( $googleKey ) {
          case 'postal_code':
            $this->data['postal_code'] = $comp->long_name;
            break;
          case 'locality':
          case 'neighborhood':
          case 'sublocality':
            $this->data['city'] = $comp->long_name;
            break;
          case 'administrative_area_level_2':
            $this->data['county'] = $comp->long_name;
            break;
          case 'administrative_area_level_1':
            $this->data['state'] = $comp->long_name;
            $this->data['state_short'] = $comp->short_name;
            break;
          case 'country':
            $this->data['country'] = $comp->long_name;
            $this->data['country_short'] = $comp->short_name;
            break;
        }
      }
    }
    $this->data['latitude'] = $result->geometry->location->lat;
    $this->data['longitude'] = $result->geometry->location->lng;
  }

  protected function getLogger()
  {
    if ( is_null($this->logger) )
    {
      $this->logger = sfContext::getInstance()->getLogger();
    }
    return $this->logger;
  }

  protected function log($levelFunc, $msg)
  {
    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->getLogger()->$levelFunc( 'GoogleGeoCoder: '.$msg );
    }
  }

  public function getPostalCode()
  {
    return isset($this->data['postal_code']) ? $this->data['postal_code'] : null;
  }

  public function getCity()
  {
    return isset($this->data['city']) ? $this->data['city'] : null;
  }

  public function getCounty()
  {
    return isset($this->data['county']) ? $this->data['county'] : null;
  }

  public function getState()
  {
    return isset($this->data['state']) ? $this->data['state'] : null;
  }

  public function getStateShort()
  {
    return isset($this->data['state_short']) ? $this->data['state_short'] : null;
  }

  public function getCountry()
  {
    return isset($this->data['country']) ? $this->data['country'] : null;
  }

  public function getCountryShort()
  {
    return isset($this->data['country_short']) ? $this->data['country_short'] : null;
  }

  public function getLatitude()
  {
    return isset($this->data['latitude']) ? $this->data['latitude'] : null;
  }

  public function getLongitude()
  {
    return isset($this->data['longitude']) ? $this->data['longitude'] : null;
  }

}
