/**
 * @type: itemView
 * name: advertise.itemView.addAdsRecord
 * chunkName: advertise
 */

import { APP_NAME, RESOURCE_NAME } from '@metafox/advertise/constants';
import { useGetItem, Link, useGlobal } from '@metafox/framework';
import { TruncateText, ItemView } from '@metafox/ui';
import FormatNumber from '@metafox/ui/FormatNumber';
import { filterShowWhen } from '@metafox/utils';
import { Box, Grid, styled, Switch } from '@mui/material';
import moment from 'moment';
import * as React from 'react';
import LoadingSkeleton from './LoadingSkeleton';

const Item = styled(Box, { shouldForwardProp: props => props !== 'center' })<{
  center?: boolean;
}>(({ theme, center }) => ({
  ...(center && {
    textAlign: 'center'
  })
}));

const AdHome = ({ identity, wrapAs, wrapProps }: any) => {
  const { dispatch, ItemActionMenu, useActionControl, useResourceMenu, i18n } =
    useGlobal();
  const item = useGetItem(identity);
  const [handleAction] = useActionControl<unknown, unknown>(identity, {});
  const placement = useGetItem(item?.placement);

  const [activeItem, setActiveItem] = React.useState(item?.is_active || false);
  const menus: any = useResourceMenu(APP_NAME, RESOURCE_NAME, 'itemActionMenu');

  if (!item) return null;

  const { title, link, start_date, status, statistic, payment_price, extra } =
    item;

  const handleChangeActive = (event: React.ChangeEvent<HTMLInputElement>) => {
    setActiveItem(event.target.checked);

    dispatch({
      type: 'advertise/activeItem',
      payload: {
        identity,
        active: event.target.checked
      }
    });
  };

  const menuItems = menus.items.map(item => {
    return {
      ...item,
      label: i18n.formatMessage({ id: item.label }, { price: payment_price })
    };
  });

  const itemMenus = filterShowWhen(menuItems || [], {
    item
  });

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid="item-advertise">
      <Grid container alignItems="center">
        <Grid item xs={3}>
          <Link to={link} underline="none" color="primary">
            <TruncateText lines={2} sx={{ pr: 2 }}>
              {title}
            </TruncateText>
          </Link>
        </Grid>
        <Grid item xs={2.5}>
          <TruncateText lines={2} sx={{ pr: 2 }}>
            {placement?.title || ''}
          </TruncateText>
        </Grid>
        <Grid item xs={1.5}>
          {start_date ? moment(start_date).format('LLL') : ''}
        </Grid>
        <Grid item xs={1.5}>
          <Item center>{status}</Item>
        </Grid>
        <Grid item xs={1}>
          <Item center>
            <FormatNumber value={statistic?.current_impressions} />
          </Item>
        </Grid>
        <Grid item xs={1}>
          <Item center>
            <FormatNumber value={statistic?.current_clicks} />
          </Item>
        </Grid>
        <Grid item xs={1}>
          <Item center>
            <Switch
              variant="ios"
              disabled={!extra?.can_active}
              size="small"
              checked={activeItem}
              onChange={handleChangeActive}
              inputProps={{ 'aria-label': 'controlled' }}
            />
          </Item>
        </Grid>
        <Grid item xs={0.5}>
          <Item center>
            <ItemActionMenu
              items={itemMenus}
              icon={'ico-dottedmore-vertical-o'}
              handleAction={handleAction}
              size="smaller"
            />
          </Item>
        </Grid>
      </Grid>
    </ItemView>
  );
};

AdHome.LoadingSkeleton = LoadingSkeleton;

export default AdHome;
