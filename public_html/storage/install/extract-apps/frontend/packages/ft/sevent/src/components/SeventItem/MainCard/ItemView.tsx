/**
 * @type: itemView
 * name: sevent.itemView.mainCard
 */
import actionCreators from '@ft/sevent/actions/seventItemActions';
import { Link, useGlobal, connectItemView } from '@metafox/framework';
import { SeventItemProps as ItemProps } from '@ft/sevent/types';
import { Box, styled, Button } from '@mui/material';
import { get } from 'lodash';
import { mappingTimeDisplay } from '@ft/sevent/blocks/ViewSevent/time';
import {
  PendingFlag,
  ItemText,
  ItemTitle,
  ItemAction,
  ItemView
} from '@metafox/ui';
import React from 'react';
import { useSelector } from 'react-redux';
import DiamondIcon from '@mui/icons-material/Diamond';
import MonetizationOnIcon from '@mui/icons-material/MonetizationOn';
import Status from '@ft/sevent/blocks/ViewSevent/Status';

export function SeventItemSmallCard({
  item,
  user,
  identity,
  state,
  itemProps,
  handleAction,
  wrapAs,
  wrapProps
}: ItemProps) {
  const {
    assetUrl,
    ItemActionMenu,
    useTheme,
    useIsMobile,
    dispatch,
    getSetting,
    i18n
  } = useGlobal();
  const theme = useTheme();
  const isMobile = useIsMobile();
  let link;

  const itemActive = useSelector(state =>
    get(state, 'sevent.seventActive')
  );

  if (!item) return null;
  
  const { id } = item;
  const settingTime = getSetting('sevent.time_format') as number;

  const [startTime] = mappingTimeDisplay(
    item.start_date,
    item.end_date,
    settingTime === 24
  );

  const { itemOnMap, itemSimilar } = itemProps;

  if (itemSimilar === 'on')
    link = `${item.link}?scrollTopOnRouteChange=true`;
  else 
    link = item.link;

  const onClickItem = id => {
    if (itemOnMap && `seventActive${id}` !== itemActive) {
      dispatch({ type: 'sevent/hover', payload: id });
    }
  };

  const cover = (item?.image ? item?.image : assetUrl('sevent.no_image'));
  const ImgMedia = styled('div', { name: 'ImgMedia', slot: 'ItemViewBox' })(
    ({ theme }) => ({ 
      width: '100%', 
      paddingBottom: '56.25%',
      background: 'url(' + cover + ')', 
      backgroundSize: 'cover',
      backgroundPosition: 'center',
      borderRadius: '8px',
      borderBottomLeftRadius: 0,
      borderBottomRightRadius: 0
    })
  );
  const ImgMediaBox = styled('div', { name: 'ImgMedia', slot: 'ItemViewBox' })(
    ({ theme }) => ({ 
      position: 'relative',
      width: '100%', 
      background: theme.palette.background.default,
      [theme.breakpoints.down('sm')]: {
        width: '100%',
        marginLeft: 0
      },
      '&:hover .right-icon': {
        opacity: 1
      }
    })
  );
  const BorderBox = styled(Box, { name: 'BorderBox', slot: 'BorderBox' })(
    ({ theme }) => ({
      display: 'flex', 
      width: '100%',
      minHeight: '135px',
      borderTop: 'none',
      padding: '12px 8px',
      [theme.breakpoints.down('sm')]: {
        width: '100%',
        marginLeft: 0
      } 
    })
  );
  const RightAction = styled(Box)({
    position: 'absolute',
    top: '0',
    display: 'flex',
    flexDirection: 'column',
    gap: '10px',
    right: '0',
    background: '#33333369',
    padding: '8px',
    color: '#fff',
    opacity: 0,
    width: '100%',
    height: '100%',
    alignItems: 'center',
    justifyContent: 'center',
    fontSize: '17px',
    transition: 'opacity 0.3s ease-in-out'
  });

  return (
    <ItemView testid={item.resource_name} wrapAs={wrapAs} wrapProps={wrapProps}
       style={{ flexDirection: 'column', borderBottomLeftRadius: '8px',
        boxShadow: theme.palette.mode !== 'light' ? null : '0 1px 5px #ddd',
        borderBottomRightRadius: '8px', background: theme.palette.background.paper }}
        id={`seventActive${id}`}
        onClick={() => onClickItem(`seventActive${id}`)}
        >
      <ImgMediaBox>
      {!itemOnMap ? (
        <Link color={
                itemOnMap && `seventActive${id}` === itemActive
                  ? 'primary'
                  : 'inherit'
              } to={!itemOnMap ? link : ''}>
          <ImgMedia>
            <div style={{ position: 'absolute', 
              height: '26px', top: 0, left: '10px', zIndex: 3, display: 'flex' }}>
                <Status item={item} text={true}/>
                {item.is_featured ? (
                  <div style={{ background: theme.palette.primary.main,
                    color: theme.palette.primary.contrastText,
                    padding: '4px' }}>
                      <DiamondIcon style={{ fontSize: '18px' }}/>
                  </div>
                ) : null}
                {item.is_sponsor ? (
                  <div style={{ background: theme.palette.warning.main,
                    color: theme.palette.primary.contrastText,
                    padding: '4px' }}>
                      <MonetizationOnIcon style={{ fontSize: '18px' }}/>
                  </div>
                ) : null}
            </div>
          </ImgMedia>
        <RightAction className="right-icon">
            <Button 
              startIcon={<i className='ico ico-ticket-o' />}
              variant='contained' color='primary'>
              {i18n.formatMessage({ id: 'sevent_select_tickets' })}
            </Button>
        </RightAction>
        </Link>
      ) : (
        <span style={{ cursor: 'pointer' }}>
          <ImgMedia>
          <div style={{ position: 'absolute', 
              height: '26px', top: 0, left: '10px', zIndex: 3, display: 'flex' }}>
              {item.is_featured ? (
                  <div style={{ background: theme.palette.primary.main,
                    color: theme.palette.primary.contrastText,
                    padding: '4px' }}>
                      <DiamondIcon style={{ fontSize: '18px' }}/>
                  </div>
                ) : null}
                {item.is_sponsor ? (
                  <div style={{ background: theme.palette.warning.main,
                    color: theme.palette.primary.contrastText,
                    padding: '4px' }}>
                      <MonetizationOnIcon style={{ fontSize: '18px' }}/>
                  </div>
                ) : null}
            </div>
          </ImgMedia>
        </span>
      )}
        {!itemOnMap ? (
          <Box display='flex' alignItems='center' justifyContent='space-between'>
              <ItemAction
                placement={'bottom-end'}
                spacing="normal"
                style={{ padding: 0, margin: '0 9px 0 0', top: '0' }}
                sx={isMobile ? { marginBottom: '4px' } : null}
              >
                <ItemActionMenu
                  
                  identity={identity}
                  icon={'ico-dottedmore-o'}
                  state={state}
                  handleAction={handleAction}
                />
              </ItemAction>
        </Box>
          ) : null}
      </ImgMediaBox>
      <BorderBox>
        <ItemText>
          <PendingFlag value={item.is_pending} />
          <ItemTitle lines={2} style={{ fontSize:'16px' }}>
            <Link color={
                itemOnMap && `seventActive${id}` === itemActive
                  ? 'primary'
                  : 'inherit'
              } to={link} style={{ fontWeight: 'bold' }}>
                {item.title}
            </Link>
          </ItemTitle>
          {!item.is_online ? (
            <div style={{ color: theme.palette.text.secondary, display: 'flex', alignItems: 'center',
              gap: '8px', marginTop: '8px', fontSize: '13px' }}> 
                {item.location_name}
            </div>
          ) : (
            <div style={{ color: theme.palette.text.secondary, display: 'flex', alignItems: 'center',
              gap: '8px', marginTop: '8px', fontSize: '13px' }}> 
              {i18n.formatMessage({ id: 'sevent_online_event' })}
          </div>
          )}
          <Box display='flex' alignItems='center' justifyContent='space-between' style={{
            position: 'absolute',
            bottom: 0,
            width: '100%'
          }}>
            <div style={{ color: theme.palette.text.secondary, fontSize: '15px', fontWeight: 'bold' }}> 
                {startTime}
            </div>
            <div style={{ display: 'flex', alignItems: 'center',
              gap: '8px', padding: '3px 5px', color: '#219653', fontSize: '15px', 
              border: '1px solid #219653' }}>
              <i className='ico ico-ticket-o' /> 
              {item.from === 'free' ? (
                  <span>
                    {i18n.formatMessage({ id: 'sevent_free' })}
                  </span>
              ) : (
                <span>
                  {item.from}
                </span>
              )}
            </div>
          </Box>
        </ItemText>
      </BorderBox>
    </ItemView>
  );
}

export default connectItemView(SeventItemSmallCard, actionCreators);
