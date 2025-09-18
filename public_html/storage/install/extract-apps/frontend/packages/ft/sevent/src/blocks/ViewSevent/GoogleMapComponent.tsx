import React from 'react';
import { GoogleMap, LoadScript, Marker } from '@react-google-maps/api';

const GoogleMapComponent = ({ item, useGlobal }) => {
  if (!item.google_map_api_key || !item.lat || !item.lng)
    return false;

  const lat = Number(item.lat);
  const lng = Number(item.lng);

  const mapContainerStyle = {
    width: '100%',
    height: '300px' 
  };
  
  const center = {
    lat, 
    lng
  };

  return (
    <LoadScript googleMapsApiKey={item.google_map_api_key}>
      <GoogleMap
        mapContainerStyle={mapContainerStyle}
        center={center}
        zoom={10} 
      >
        <Marker position={center} />
      </GoogleMap>
    </LoadScript>
  );
};

export default GoogleMapComponent;
