import {
  BasicPlaceItem,
  useFixedRect,
  useGlobal,
  useResourceAction,
  getPagingSelector,
  getItemsSelector,
  useIsMobile,
  BlockViewProps,
  MFOX_LOCALE,
  CORE_GOOGLE_GOOGLE_MAP_API_KEY,
  GlobalState
} from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import {
  Box,
  ClickAwayListener,
  Collapse,
  Paper,
  Popper,
  Skeleton,
  styled,
  TextField as MuiTextField,
  Tooltip
} from '@mui/material';
import { camelCase, get } from 'lodash';
import React, { useRef, useMemo, useState, useEffect } from 'react';
import { useLocation } from 'react-router-dom';
import { whenParamRules } from '@metafox/utils';
import {
  GoogleMap,
  useLoadScript,
  Marker,
  InfoWindow
} from '@react-google-maps/api';
import { Block } from '@metafox/layout';
import { useSelector } from 'react-redux';
import qs from 'query-string';
import {
  BOUNDS_EAST,
  BOUNDS_NORTH,
  BOUNDS_SOUTH,
  BOUNDS_WEST,
  ZOOM
} from '@metafox/core/constant';

export interface Props extends BlockViewProps {}

const TitleStyled = styled('div', { name: 'titleStyled' })(({ theme }) => ({
  color: theme.palette.grey[700]
}));

const InputLocation = styled('div')(({ theme }) => ({
  position: 'absolute',
  right: theme.spacing(7.5),
  top: theme.spacing(1.2),
  backgroundColor: '#ffffff',
  height: theme.spacing(5),
  borderRadius: theme.spacing(1),
  '[dir="rtl"] &': {
    right: 'inherit',
    left: theme.spacing(7.5)
  },
  '& .MuiInputBase-root': {
    width: '320px',
    height: '40px !important',
    borderRadius: theme.spacing(1)
  },
  '& input': {
    padding: theme.spacing(1.75, 1.75),
    color: 'black',
    marginRight: theme.spacing(3)
  },
  '& input::placeholder': {
    color: theme.palette.grey[700]
  },
  [theme.breakpoints.down('sm')]: {
    position: 'absolute!important',
    top: '-50px',
    width: 'calc(100% - 32px)!important',
    left: theme.spacing(2),
    '& .MuiInputBase-root': {
      width: '100%!important'
    },
    marginRight: theme.spacing(2)
  }
}));

const SuggestAddress = styled('span')(({ theme }) => ({
  paddingLeft: theme.spacing(1),
  color: theme.palette.text.secondary
}));

const SuggestName = styled('span')(({ theme }) => ({
  color: theme.palette.text.primary
}));

const SuggestIcon = styled(LineIcon)(({ theme }) => ({
  color: theme.palette.text.secondary,
  fontSize: theme.mixins.pxToRem(13),
  paddingRight: theme.spacing(1)
}));

const SuggestItem = styled('div')(({ theme }) => ({
  flexDirection: 'row',
  alignItems: 'center',
  padding: theme.spacing(1, 1),
  whiteSpace: 'nowrap',
  fontSize: theme.mixins.pxToRem(15),
  cursor: 'pointer',
  '&:hover': {
    backgroundColor: theme.palette.action.focus
  },
  overflow: 'hidden',
  textOverflow: 'ellipsis',
  WebkitBoxOrient: 'vertical',
  WebkitLineClamp: 1
}));

const PaperWrapper = styled(Paper)(({ theme }) => ({
  padding: theme.spacing(1, 1),
  marginTop: '0px'
}));

const PopperWrapper = styled(Popper)(({ theme }) => ({
  zIndex: 1000,
  width: '320px',
  maxHeight: '300px',
  overflowY: 'auto',
  [theme.breakpoints.down('sm')]: {
    width: '100%'
  }
}));

const IconMyLocation = styled('div')(({ theme }) => ({
  position: 'absolute',
  top: '50%',
  right: 0,
  transform: 'translate(-14px, -50%)',
  cursor: 'pointer',
  color: theme.palette.grey.A700
}));

const MapWrapper = styled('div')(({ theme }) => ({
  display: 'block',
  position: 'relative',
  '& .map': {
    backgroundColor: theme.palette.action.focus,
    height: '100%',
    width: '100%',
    position: 'absolute',
    top: 0,
    left: 0
  },
  '& .gm-ui-hover-effect': {
    background: 'white !important',
    width: '20px !important',
    height: '20px !important',
    right: '2px !important',
    '& span': {
      margin: '0 !important'
    }
  },
  '& .gm-style-iw + button': {
    display: 'none'
  }
}));

export default function EventMap({ fixedView }: Props) {
  const zoomDefault = 15;
  const isMobile = useIsMobile();
  const inputRef = React.useRef<any>();

  const [query, setQuery] = React.useState('');
  const { usePageParams, navigate, dispatch, assetUrl, i18n, usePreference } =
    useGlobal();

  const pageParams = usePageParams();
  const { appName } = pageParams;

  const itemActive = useSelector(state =>
    get(state, `${appName}.${appName}Active`)
  );

  const dataSource = useResourceAction(appName, appName, 'viewItemsOnMap');

  const apiParams = whenParamRules(pageParams, dataSource?.apiRules);
  const { search } = useLocation();

  const searchParams = useMemo(() => new URLSearchParams(search), [search]);
  const serviceRef = React.useRef<google.maps.places.PlacesService>();
  const geocoderService = React.useRef<google.maps.places.GeocoderService>();
  const pagingData = useSelector((state: any) =>
    getPagingSelector(state, `${dataSource?.apiUrl}?${qs.stringify(apiParams)}`)
  );

  const data = useSelector((state: GlobalState) =>
    getItemsSelector(state, pagingData?.ids)
  );
  const { userLanguage } = usePreference();

  const key = CORE_GOOGLE_GOOGLE_MAP_API_KEY;
  const { isLoaded } = useLoadScript({
    googleMapsApiKey: key,
    libraries: ['places'],
    language: userLanguage || MFOX_LOCALE
  });
  const [items, setItems] = React.useState([]);
  const [meta, setMeta] = React.useState({ loading: false, error: false });

  const [open, setOpen] = React.useState<boolean>(false);

  const handleSelect = (loc: BasicPlaceItem) => {
    const postionComma = loc.address.search(',');
    const currentValue = {
      address:
        loc.address.slice(0, postionComma) === loc.name
          ? `${loc.address}`
          : `${loc.name}, ${loc.address}`,
      lat: loc.lat,
      lng: loc.lng
    };

    setOpen(false);
    setQuery(currentValue.address);
    setCenter({ lat: loc.lat, lng: loc.lng });
  };

  const [center, setCenter] = useState({
    lat:
      (parseFloat(searchParams.get(BOUNDS_SOUTH)) +
        parseFloat(searchParams.get(BOUNDS_NORTH))) /
        2 || 0,
    lng:
      (parseFloat(searchParams.get(BOUNDS_EAST)) +
        parseFloat(searchParams.get(BOUNDS_WEST))) /
        2 || 0
  });

  const [map, setMap] = useState<google.maps.Map>(null);

  const zoomMap = useMemo(() => map?.getZoom().toString(), [map]);

  const zoom = useMemo(
    () => parseInt(searchParams.get(ZOOM)) || zoomMap || zoomDefault,
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [searchParams.get(ZOOM), zoomMap]
  );

  const onLoad = React.useCallback((map: google.maps.Map) => {
    setMap(map);
    try {
      geocoderService.current = new google.maps.Geocoder();
      geocoderService.current.geocode({ location: center }).then(response => {
        if (response.results[0]) {
          setQuery(response.results[0].formatted_address);
        } else {
          setQuery('');
        }
      });
    } catch (e) {}

    if (!searchParams.get(BOUNDS_SOUTH))
      navigator.geolocation.getCurrentPosition(
        (position: GeolocationPosition) => {
          setCenter({
            lat: position.coords.latitude,
            lng: position.coords.longitude
          });
        },
        () => {}
      );

    serviceRef.current = new google.maps.places.PlacesService(map);
  }, []);

  const containerStyle = {};

  const divRef = useRef(null);
  const styleMap = useFixedRect(divRef).style;

  const onChange = e => {
    setQuery(e);
    setOpen(true);
  };

  const searchPlaces = React.useCallback(
    criteria => {
      if (!serviceRef.current || !criteria?.query) {
        return;
      }

      setMeta(meta => ({ ...meta, loading: true }));

      serviceRef.current.textSearch(criteria, (results, status) => {
        if ('OK' === status) {
          setItems(
            results.map((x: any) => ({
              icon: x.icon,
              name: x.name,
              address: x.formatted_address,
              lat: x.geometry.location.lat(),
              lng: x.geometry.location.lng()
            }))
          );
          setMeta(meta => ({ ...meta, loading: false }));
        }
      });
    },

    // eslint-disable-next-line react-hooks/exhaustive-deps
    [query]
  );

  React.useEffect(() => {
    searchPlaces({ query });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [query]);

  useEffect(() => {
    if (query) onChangeBounds();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [center]);

  const onChangeBounds = () => {
    if (isLoaded && map) {
      const west = map.getBounds().getSouthWest().lng();
      const east = map.getBounds().getNorthEast().lng();
      const south = map.getBounds().getSouthWest().lat();
      const north = map.getBounds().getNorthEast().lat();

      searchParams.set(BOUNDS_EAST, east.toString());
      searchParams.set(BOUNDS_NORTH, north.toString());
      searchParams.set(BOUNDS_SOUTH, south.toString());
      searchParams.set(BOUNDS_WEST, west.toString());
      searchParams.set(ZOOM, map.getZoom().toString());

      navigate({ search: searchParams.toString() }, { replace: true });
    }
  };

  const onDragEnd = () => {
    onCloseInfoWindow();

    setTimeout(() => {
      onChangeBounds();
    }, 200);
  };

  const handleActiveMarker = id => {
    const element = document.getElementById(id);

    element.scrollIntoView(false);

    if (id === itemActive) {
      return;
    }

    dispatch({ type: `${appName}/hover`, payload: id });
  };

  const onZoomChanged = () => {
    onCloseInfoWindow();

    if (search) {
      setTimeout(() => {
        onChangeBounds();
      }, 200);
    }
  };

  const onCloseInfoWindow = () => {
    dispatch({ type: `${appName}/hover`, payload: '' });
  };

  useEffect(() => {
    return () => {
      divRef.current = null;
    };
  }, []);

  useEffect(() => {
    if (pagingData?.loading) {
      onCloseInfoWindow();
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pagingData?.loading]);

  if (!isLoaded) return;

  const iconMarkerHover: google.maps.Icon = {
    url: assetUrl(`${appName}.map_marker_hover`),
    scaledSize: new google.maps.Size(52, 52)
  };
  const iconMarker: google.maps.Icon = {
    url: assetUrl(`${appName}.map_marker`),
    scaledSize: new google.maps.Size(44, 44)
  };

  const styleMapMobile = isMobile ? { top: 180 } : {};

  const myLocation = () => {
    navigator.geolocation.getCurrentPosition(
      (position: GeolocationPosition) => {
        setCenter({
          lat: position.coords.latitude,
          lng: position.coords.longitude
        });
      },
      () => {}
    );
  };

  return (
    <Block>
      <MapWrapper data-testid="mapSearchView" ref={divRef}>
        <div
          style={
            fixedView
              ? { ...styleMap, ...styleMapMobile }
              : {
                  position: 'static',
                  height: '0',
                  paddingBottom: '100%'
                }
          }
        >
          {isLoaded ? (
            <GoogleMap
              mapContainerStyle={containerStyle}
              center={center}
              zoom={zoom}
              onLoad={onLoad}
              mapContainerClassName="map"
              onDragEnd={onDragEnd}
              onZoomChanged={onZoomChanged}
              options={{
                gestureHandling: 'cooperative'
              }}
            >
              {data.map(({ id, title, location }) => (
                <Marker
                  icon={
                    itemActive === `${appName}Active${id}`
                      ? iconMarkerHover
                      : iconMarker
                  }
                  key={id}
                  position={{
                    lat: parseFloat(location?.lat),
                    lng: parseFloat(location?.lng)
                  }}
                  onClick={() => handleActiveMarker(`${appName}Active${id}`)}
                >
                  {itemActive === `${appName}Active${id}` ? (
                    <InfoWindow
                      position={{
                        lat: parseFloat(location?.lat),
                        lng: parseFloat(location?.lng)
                      }}
                      onCloseClick={() => onCloseInfoWindow()}
                    >
                      <TitleStyled>{title}</TitleStyled>
                    </InfoWindow>
                  ) : null}
                </Marker>
              ))}
            </GoogleMap>
          ) : null}
          <ClickAwayListener onClickAway={() => setOpen(false)}>
            <div>
              <InputLocation>
                <MuiTextField
                  sx={{ width: '100%' }}
                  inputRef={inputRef}
                  onChange={evt => onChange(evt.target.value)}
                  value={query}
                  inputProps={{ 'data-testid': camelCase('field') }}
                  placeholder={i18n.formatMessage({ id: 'search_by_location' })}
                />
                <Tooltip
                  title={i18n.formatMessage({ id: 'your_location' })}
                  placement="bottom"
                >
                  <IconMyLocation onClick={myLocation}>
                    <LineIcon icon="ico-target-o" />
                  </IconMyLocation>
                </Tooltip>
              </InputLocation>
              <PopperWrapper
                open={open}
                anchorEl={inputRef.current}
                placement="bottom-start"
                sx={{ borderRadius: 1 }}
              >
                <PaperWrapper>
                  <Collapse in={open}>
                    {meta.loading ? (
                      <Box>
                        <Skeleton animation="wave" height={30} width="100%" />
                        <Skeleton animation="wave" height={30} width="100%" />
                        <Skeleton animation="wave" height={30} width="100%" />
                      </Box>
                    ) : (
                      items.map(item => {
                        return (
                          <SuggestItem
                            key={item.lat}
                            onClick={() => handleSelect(item)}
                          >
                            <SuggestIcon icon={'ico-checkin'} />
                            <SuggestName>{item.name}</SuggestName>
                            <SuggestAddress>{item.address}</SuggestAddress>
                          </SuggestItem>
                        );
                      })
                    )}
                  </Collapse>
                </PaperWrapper>
              </PopperWrapper>
            </div>
          </ClickAwayListener>
        </div>
      </MapWrapper>
    </Block>
  );
}
