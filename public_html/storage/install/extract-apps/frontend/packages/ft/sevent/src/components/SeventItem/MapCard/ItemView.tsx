/**
 * @type: itemView
 * name: sevent.itemView.mapCard
 */
import actionCreators from '@ft/sevent/actions/seventItemActions';
import { Link, useGlobal, connectItemView } from '@metafox/framework';
import { SeventItemProps as ItemProps } from '@ft/sevent/types';
import { styled } from '@mui/material';
import { get } from 'lodash';
import {
  ItemView
} from '@metafox/ui';
import React from 'react';
import { useSelector } from 'react-redux';

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
    useTheme,
    dispatch
  } = useGlobal();
  const theme = useTheme();

  let link;

  const itemActive = useSelector(state =>
    get(state, 'sevent.seventActive')
  );

  if (!item) return null;
  
  const { id } = item;

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
  const ImgMediaBox = styled('div', { name: 'ImgMedia', slot: 'ItemViewBox' })(
    ({ theme }) => ({ 
      position: 'relative',
      width: '100%', 
      [theme.breakpoints.down('sm')]: {
        width: '100%',
        marginLeft: 0
      },
      '&:hover .right-icon': {
        opacity: 1
      }
    })
  );

  return (
    <ItemView testid={item.resource_name} wrapAs={wrapAs} wrapProps={wrapProps}
       style={{ flexDirection: 'column', borderBottomLeftRadius: '8px',
        borderBottomRightRadius: '8px', marginBottom: '16px', background: theme.palette.background.paper }}
        id={`seventActive${id}`}
        onClick={() => onClickItem(`seventActive${id}`)}
        >
      <ImgMediaBox>
        <span style={{ cursor: 'pointer' }}>
         <img src={cover} style={{ maxWidth: '100%', marginBottom: '16px' }} />
        </span>
      </ImgMediaBox>
      <div style={{ fontSize:'13px', margin: '0 0 8px 0' }}> 
        <Link color={
            itemOnMap && `seventActive${id}` === itemActive
              ? 'primary'
              : 'inherit'
          } to={link} style={{ fontWeight: 'bold' }}>
            {item.title}
        </Link>
      </div>
      <div style={{ fontSize:'13px', margin: '8px 0', color: theme.palette.text.secondary }}>
           {item.short_description}
        </div>
    </ItemView>
  );
}

export default connectItemView(SeventItemSmallCard, actionCreators);
