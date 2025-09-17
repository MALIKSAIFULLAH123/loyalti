import { useTheme } from '@mui/styles';
import { debounce } from 'lodash';
import React, { useEffect } from 'react';
import { useGoogleMap, BasicPlaceItem } from '@metafox/framework';

export default function useSearchPlaces(
  mapContainerRef: React.MutableRefObject<HTMLDivElement>,
  center: Partial<BasicPlaceItem>,
  searchOptions = {}
): [
  BasicPlaceItem[],
  string,
  React.Dispatch<React.SetStateAction<string>>,
  BasicPlaceItem,
  Record<string, any>
] {
  const statusGoogleMap = useGoogleMap();
  const readyGoogleMap = statusGoogleMap === 'ready';
  const [items, setItems] = React.useState<BasicPlaceItem[]>([]);
  const [meta, setMeta] = React.useState({ loading: true, error: false });

  const [currentItem, setCurrentItem] = React.useState<Record<string, any>>();
  const [query, setQuery] = React.useState<string>('');

  const mapObjectRef = React.useRef<google.maps.Map>();
  const serviceRef = React.useRef<google.maps.places.PlacesService>();
  const geocoderService = React.useRef<google.maps.places.GeocoderService>();
  const theme = useTheme();
  const mounted = React.useRef(null);
  const latLng = React.useRef();

  const initMap = ({ lat, lng }: BasicPlaceItem) => {
    mapObjectRef.current = new google.maps.Map(mapContainerRef.current, {
      zoom: 15,
      center: {
        lat,
        lng
      },
      backgroundColor: theme.palette.background.default
    });

    serviceRef.current = new google.maps.places.PlacesService(
      mapObjectRef.current
    );
    latLng.current = lat && lng ? new google.maps.LatLng(lat, lng) : undefined;

    geocoderService.current = new google.maps.Geocoder();

    if (!center?.full_address && (center?.address || center.name)) {
      // TODO will remove on next ver when api support storage address
      try {
        const latLng = { lat: parseFloat(lat), lng: parseFloat(lng) };

        getDetailPlace({ location: latLng }, results => {
          if (results[0]) {
            setCurrentItem({
              name: center?.address || center.name,
              full_address: results[0]?.formatted_address
            });
          }
        });
      } catch (error) {}
    }

    getSearchDefault();
  };

  const getSearchDefault = () => {
    if (latLng.current) {
      // by default will show near me results
      serviceRef.current.nearbySearch(
        { location: latLng.current, radius: 50, ...searchOptions },
        (results, status) => {
          if ('OK' === status && mounted.current) {
            setItems(
              results
                .filter(x => x.formatted_address || x.vicinity)
                .map((x: any) => ({
                  icon: x.icon,
                  name: x.name,
                  full_address: x.formatted_address || x.vicinity || '',
                  lat: x.lat || x.geometry.location.lat(),
                  lng: x.lng || x.geometry.location.lng()
                }))
            );
          }

          setMeta(meta => ({ ...meta, loading: false }));
        }
      );

      return;
    }

    setMeta(meta => ({ ...meta, loading: false }));
  };

  const initService = React.useCallback(() => {
    if (!mapContainerRef.current || serviceRef.current || !readyGoogleMap)
      return;

    setMeta(meta => ({ ...meta, loading: true }));

    navigator.geolocation.getCurrentPosition(
      (position: GeolocationPosition) => {
        initMap({
          lat: center?.lat || position.coords.latitude,
          lng: center?.lng || position.coords.longitude
        });
      },
      () => {
        initMap({
          lat: center?.lat || 0,
          lng: center?.lng || 0
        });
      }
    );

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [mapContainerRef, readyGoogleMap]);

  const getDetailPlace = React.useCallback((data, callback) => {
    if (!serviceRef.current || !geocoderService.current) {
      return;
    }

    geocoderService.current.geocode(data, (results, status) => {
      if ('OK' === status && mounted.current) {
        callback(results);
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const searchPlaces = React.useMemo(() => {
    function searchPlacesFn(criteria: any) {
      if (!serviceRef.current) {
        return;
      }

      if (!criteria?.query) {
        getSearchDefault();

        return;
      }

      setMeta(meta => ({ ...meta, loading: true }));
      serviceRef.current.textSearch(
        {
          location: latLng.current,
          ...criteria
        },
        (results, status) => {
          if ('OK' === status && mounted.current) {
            setItems(
              results.map((x: any) => ({
                icon: x.icon,
                name: x.name,
                full_address: x.formatted_address,
                lat: x.geometry.location.lat(),
                lng: x.geometry.location.lng()
              }))
            );
          } else {
            setMeta(meta => ({ ...meta, extra: results }));
          }

          setMeta(meta => ({ ...meta, loading: false }));
        }
      );
    }

    return debounce(searchPlacesFn, 200);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // avoid setState when component unmounted
  useEffect(() => {
    mounted.current = true;

    return () => {
      mounted.current = false;
    };
  }, []);

  React.useEffect(() => {
    setImmediate(() => initService());

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [readyGoogleMap]);

  const handleSearch = React.useCallback(
    (query, options = {}) => {
      setQuery(query);
      searchPlaces({ query, ...searchOptions, ...options });
    },
    [searchOptions, searchPlaces]
  );

  return [items, query, handleSearch, currentItem, meta];
}
