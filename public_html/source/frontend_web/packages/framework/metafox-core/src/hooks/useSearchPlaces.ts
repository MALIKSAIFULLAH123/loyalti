import { useTheme } from '@mui/styles';
import { debounce } from 'lodash';
import React, { useEffect } from 'react';
import { BasicPlaceItem } from '../types';
import useGoogleMap from './useGoogleMap';

export default function useSearchPlaces(
  mapContainerRef: React.MutableRefObject<HTMLDivElement>,
  center: Partial<BasicPlaceItem>,
  searchOptions
): [
  BasicPlaceItem[],
  string,
  React.Dispatch<React.SetStateAction<string>>,
  BasicPlaceItem,
  (item: BasicPlaceItem) => void,
  (location: BasicPlaceItem, callback: (data: any) => void) => void,
  Record<string, any>
] {
  const searchOptionsNearBy = { radius: 5000 };
  const statusGoogleMap = useGoogleMap();
  const readyGoogleMap = statusGoogleMap === 'ready';
  const [items, setItems] = React.useState<BasicPlaceItem[]>([]);
  const [meta, setMeta] = React.useState({ loading: true, error: false });

  const [currentItem, setCurrentItem] = React.useState<BasicPlaceItem>();
  const [query, setQuery] = React.useState<string>(center?.address);

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

    if (center?.address || center.name) {
      serviceRef.current.textSearch(
        {
          query: center?.address || center.name,
          location: latLng.current,
          ...(searchOptions || {})
        },
        (results, status) => {
          if ('OK' === status && mounted.current) {
            setCurrentItem({
              name: results[0].name,
              address: results[0].formatted_address,
              lat: results[0].geometry.location.lat(),
              lng: results[0].geometry.location.lng()
            });
            setItems([
              {
                icon: results[0].icon,
                name: results[0].name,
                address: results[0].formatted_address,
                lat: results[0].geometry.location.lat(),
                lng: results[0].geometry.location.lng()
              }
            ]);
            setMeta(meta => ({ ...meta, loading: false }));
          }
        }
      );

      return;
    }

    getSearchDefault();
  };

  const getSearchDefault = () => {
    if (latLng.current) {
      // by default will show near me results
      serviceRef.current.nearbySearch(
        { location: latLng.current, radius: 50, ...(searchOptions || {}) },
        (results, status) => {
          if ('OK' === status && mounted.current) {
            setItems(
              results
                .filter(x => x.formatted_address || x.vicinity)
                .map((x: any) => ({
                  icon: x.icon,
                  name: x.name,
                  address: x.formatted_address || x.vicinity || '',
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

  const setCenter = React.useCallback((item: { lat: number; lng: number }) => {
    if (mapObjectRef.current) {
      mapObjectRef.current.setCenter({ lat: item.lat, lng: item.lng });
    }
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
          ...(latLng.current && !searchOptions
            ? { location: latLng.current, ...searchOptionsNearBy }
            : searchOptions),
          ...criteria
        },
        (results, status) => {
          if ('OK' === status && mounted.current) {
            setItems(
              results.map((x: any) => ({
                icon: x.icon,
                name: x.name,
                address: x.formatted_address,
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

  const getDetailPlace = React.useCallback((criteria, callback) => {
    if (!serviceRef.current || !geocoderService.current) {
      return;
    }

    geocoderService.current.geocode(
      { location: criteria },
      (results, status) => {
        if ('OK' === status && mounted.current) {
          callback(results[results.length - 1]);
        }
      }
    );
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

  React.useEffect(() => {
    searchPlaces({ query, ...(searchOptions || {}) });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [query]);

  return [items, query, setQuery, currentItem, setCenter, getDetailPlace, meta];
}
