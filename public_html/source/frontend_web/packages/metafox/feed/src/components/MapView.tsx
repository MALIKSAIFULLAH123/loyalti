/**
 * @type: ui
 * name: ui.mapDisplay
 */
import {
  CORE_GOOGLE_GOOGLE_MAP_API_KEY,
  useGlobal,
  useGoogleMap
} from '@metafox/framework';
import { Box, styled } from '@mui/material';
import React from 'react';

const name = 'MapView';
const Map = styled(Box, { name, slot: 'map' })(({ theme }) => ({
  pointerEvents: 'none',
  display: 'block',
  position: 'relative',
  borderRadius: theme.shape.borderRadius,
  '&:before': {
    content: '""',
    display: 'block',
    paddingBottom: '35%'
  }
}));

const Root = styled('a', { name, slot: 'root' })(({ theme }) => ({
  display: 'block',
  position: 'relative',
  cursor: 'pointer'
}));

function MapView({ location }) {
  const { lat, lng, name } = location || {};
  const mapRef = React.useRef<HTMLDivElement>();
  const { useTheme } = useGlobal();
  const statusGoogleMap = useGoogleMap();
  const readyGoogleMap = statusGoogleMap === 'ready';
  const theme = useTheme();

  React.useEffect(() => {
    if (!mapRef.current || !lat || !lng || !readyGoogleMap) return;

    const map = new google.maps.Map(mapRef.current, {
      zoom: 15,
      center: {
        lat,
        lng
      },
      backgroundColor: theme.palette.background.default,
      disableDefaultUI: true,
      gestureHandling: 'none',
      zoomControl: false,
      scrollwheel: false,
      clickableIcons: false
    });

    new google.maps.Marker({
      position: { lat, lng },
      map,
      title: name
    });
  }, [mapRef.current, lat, lng, readyGoogleMap]);

  const hasKeyMap = CORE_GOOGLE_GOOGLE_MAP_API_KEY;

  if (!hasKeyMap) return null;

  return (
    <Root
      rel="noopener noreferrer"
      href={`https://www.google.com/maps?daddr=${lat},${lng}`}
      target="_blank"
    >
      <Map ref={mapRef} />
    </Root>
  );
}

export default MapView;
