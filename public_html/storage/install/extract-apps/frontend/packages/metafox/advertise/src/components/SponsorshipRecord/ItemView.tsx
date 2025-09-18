/**
 * @type: itemView
 * name: advertise.itemView.sponsorshipRecord
 * chunkName: advertise
 */

import { APP_NAME, RESOURCE_SPONSOR } from '@metafox/advertise/constants';
import { useGetItem, Link, useGlobal } from '@metafox/framework';
import { FormatNumber, ItemView, TruncateText } from '@metafox/ui';
import { filterShowWhen } from '@metafox/utils';
import { Grid, styled, Switch, Box, Typography } from '@mui/material';
import moment from 'moment';
import * as React from 'react';

const ItemWrapperStyled = styled(Box, { name: 'ItemStyled' })(({ theme }) => ({
  fontSize: 15,
  color: theme.palette.text.secondary,
  marginTop: theme.spacing(2),
  backgroundColor: theme.palette.background.default,
  borderRadius: theme.shape.borderRadius,
  display: 'flex',
  width: '100%',
  padding: theme.spacing(2),
  flexDirection: 'column',
  position: 'relative'
}));

const WrapActionStyled = styled(Box)(({ theme }) => ({
  position: 'absolute',
  right: 8,
  top: 20
}));

const RowStyled = styled(Box, {
  slot: 'row',
  shouldForwardProp: props => props !== 'center' && props !== 'noMargin'
})<{ center?: boolean; noMargin?: boolean }>(({ theme, center, noMargin }) => ({
  marginTop: theme.spacing(1.25),
  ...(center && {
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center'
  }),
  ...(noMargin && {
    margin: 0
  })
}));
const TitleRowStyle = styled(Typography, { slot: 'title' })(
  ({ theme }) => ({})
);
const ValueRowStyled = styled(Box, { slot: 'value' })(({ theme }) => ({}));

const Item = styled(Box, { shouldForwardProp: props => props !== 'center' })<{
  center?: boolean;
}>(({ theme, center }) => ({
  ...(center && {
    textAlign: 'center'
  })
}));

const Sponsorship = ({ identity, wrapAs, wrapProps }: any) => {
  const {
    dispatch,
    useIsMobile,
    i18n,
    ItemActionMenu,
    useActionControl,
    useResourceMenu
  } = useGlobal();
  const item = useGetItem(identity);
  const isMobile = useIsMobile();
  const [activeItem, setActiveItem] = React.useState(item?.is_active || false);
  const [handleAction] = useActionControl<unknown, unknown>(identity, {});
  const menus: any = useResourceMenu(
    APP_NAME,
    RESOURCE_SPONSOR,
    'itemActionMenu'
  );

  if (!item) return null;

  const { title, link, start_date, status, statistic, payment_price, extra } =
    item;

  const handleChangeActive = (event: React.ChangeEvent<HTMLInputElement>) => {
    setActiveItem(event.target.checked);

    dispatch({
      type: 'advertise/activeSponsorItem',
      payload: {
        identity,
        active: event.target.checked ? 1 : 0
      }
    });
  };

  const menuItems = menus.items.map(menu => {
    return {
      ...menu,
      label: i18n.formatMessage({ id: menu.label }, { price: payment_price })
    };
  });

  const itemMenus = filterShowWhen(menuItems || [], {
    item
  });

  if (isMobile) {
    return (
      <ItemWrapperStyled>
        <WrapActionStyled>
          <ItemActionMenu
            items={itemMenus}
            icon={'ico-dottedmore-vertical-o'}
            handleAction={handleAction}
            size="smaller"
          />
        </WrapActionStyled>
        <RowStyled>
          <TitleRowStyle variant="h5">
            {i18n.formatMessage({ id: 'title' })}
          </TitleRowStyle>
          <ValueRowStyled>
            <TruncateText lines={2} sx={{ pr: 2 }}>
              <Link to={link} underline="none" color="primary">
                {title}
              </Link>
            </TruncateText>
          </ValueRowStyled>
        </RowStyled>
        <RowStyled>
          <TitleRowStyle variant="h5">
            {i18n.formatMessage({ id: 'start_date' })}
          </TitleRowStyle>
          <ValueRowStyled>
            {start_date ? moment(start_date).format('LLL') : ''}
          </ValueRowStyled>
        </RowStyled>
        <RowStyled>
          <TitleRowStyle variant="h5">
            {i18n.formatMessage({ id: 'status' })}
          </TitleRowStyle>
          <ValueRowStyled>
            <Item>{status}</Item>
          </ValueRowStyled>
        </RowStyled>
        <RowStyled>
          <TitleRowStyle variant="h5">
            {i18n.formatMessage({ id: 'impressions' })}
          </TitleRowStyle>
          <ValueRowStyled>
            <Item>
              <FormatNumber value={statistic?.current_impressions} />
            </Item>
          </ValueRowStyled>
        </RowStyled>
        <RowStyled>
          <TitleRowStyle variant="h5">
            {i18n.formatMessage({ id: 'clicks' })}
          </TitleRowStyle>
          <ValueRowStyled>
            <Item>
              <FormatNumber value={statistic?.current_clicks} />
            </Item>
          </ValueRowStyled>
        </RowStyled>
        <RowStyled center noMargin>
          <TitleRowStyle variant="h5">
            {i18n.formatMessage({ id: 'active' })}
          </TitleRowStyle>
          <ValueRowStyled>
            <Item>
              <Switch
                disabled={!extra?.can_active}
                variant="ios"
                size="medium"
                checked={activeItem}
                onChange={handleChangeActive}
                inputProps={{ 'aria-label': 'controlled' }}
              />
            </Item>
          </ValueRowStyled>
        </RowStyled>
      </ItemWrapperStyled>
    );
  }

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid="advertise-sponsor">
      <Grid container alignItems="center">
        <Grid item xs={3}>
          <Link to={link} underline="none" color="primary">
            <TruncateText lines={2} sx={{ pr: 2 }}>
              {title}
            </TruncateText>
          </Link>
        </Grid>

        <Grid item xs={2}>
          {start_date ? moment(start_date).format('LL') : ''}
        </Grid>
        <Grid item xs={1.5}>
          <Item center>{status}</Item>
        </Grid>
        <Grid item xs={1.5}>
          <Item center>
            <FormatNumber value={statistic?.current_impressions} />
          </Item>
        </Grid>
        <Grid item xs={1.5}>
          <Item center>
            <FormatNumber value={statistic?.current_clicks} />
          </Item>
        </Grid>
        <Grid item xs={2}>
          <Item center>
            <Switch
              variant="ios"
              size="small"
              disabled={!extra?.can_active}
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

export default Sponsorship;
