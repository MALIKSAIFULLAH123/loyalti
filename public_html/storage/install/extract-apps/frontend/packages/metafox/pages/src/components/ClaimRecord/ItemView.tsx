/**
 * @type: itemView
 * name: page.itemView.claimRecord
 * chunkName: page
 */

import { useGetItem, Link, useGlobal } from '@metafox/framework';
import { TruncateText, ItemView } from '@metafox/ui';
import { filterShowWhen } from '@metafox/utils';
import { Box, Grid, Typography, styled } from '@mui/material';
import moment from 'moment';
import * as React from 'react';
import LoadingSkeleton from './LoadingSkeleton';
import { APP_PAGE, RESOURCE_CLAIM } from '@metafox/pages/constant';

const Item = styled(Box, {
  shouldForwardProp: props => props !== 'center' && props !== 'color'
})<{
  center?: boolean;
  color?: string;
}>(({ theme, center, color }) => ({
  wordBreak: 'break-word',
  ...(center && {
    textAlign: 'center'
  }),
  ...(color && {
    color
  })
}));

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

const ClaimRecord = ({ identity, wrapAs, wrapProps }: any) => {
  const {
    ItemActionMenu,
    useActionControl,
    useResourceMenu,
    useIsMobile,
    i18n
  } = useGlobal();
  const item = useGetItem(identity);
  const isMobile = useIsMobile(true);
  const [handleAction] = useActionControl<unknown, unknown>(identity, {});

  const menus: any = useResourceMenu(
    APP_PAGE,
    RESOURCE_CLAIM,
    'itemActionMenu'
  );

  const page = useGetItem(item?.page);

  if (!item) return null;

  const { created_at, updated_at, status_info, description } = item;

  const itemMenus = filterShowWhen(menus.items || [], {
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
            {i18n.formatMessage({ id: 'page' })}
          </TitleRowStyle>
          <ValueRowStyled>
            <Link to={page?.link} underline="none" color="primary">
              <TruncateText lines={2} sx={{ pr: 2 }}>
                {page?.title}
              </TruncateText>
            </Link>
          </ValueRowStyled>
        </RowStyled>
        <RowStyled>
          <TitleRowStyle variant="h5">
            {i18n.formatMessage({ id: 'created_date' })}
          </TitleRowStyle>
          <ValueRowStyled>
            {created_at ? moment(created_at).format('LLL') : ''}
          </ValueRowStyled>
        </RowStyled>
        <RowStyled>
          <TitleRowStyle variant="h5">
            {i18n.formatMessage({ id: 'modified_date' })}
          </TitleRowStyle>
          <ValueRowStyled>
            {updated_at ? moment(updated_at).format('LLL') : ''}
          </ValueRowStyled>
        </RowStyled>
        <RowStyled>
          <TitleRowStyle variant="h5">
            {i18n.formatMessage({ id: 'status' })}
          </TitleRowStyle>
          <ValueRowStyled>
            <Item color={status_info?.color}>{status_info?.label}</Item>
          </ValueRowStyled>
        </RowStyled>
        <RowStyled>
          <TitleRowStyle variant="h5">
            {i18n.formatMessage({ id: 'description' })}
          </TitleRowStyle>
          <ValueRowStyled>
            <Item>
              <TruncateText lines={2} sx={{ pr: 2 }}>
                {description}
              </TruncateText>
            </Item>
          </ValueRowStyled>
        </RowStyled>
      </ItemWrapperStyled>
    );
  }

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid="item-advertise">
      <Grid container alignItems="center">
        <Grid item xs={2.5}>
          <Link to={page?.link} underline="none" color="primary">
            <TruncateText lines={2} sx={{ pr: 2 }}>
              {page?.title}
            </TruncateText>
          </Link>
        </Grid>
        <Grid item xs={2.5}>
          {created_at ? moment(created_at).format('LLL') : ''}
        </Grid>
        <Grid item xs={2.5}>
          {updated_at ? moment(updated_at).format('LLL') : ''}
        </Grid>
        <Grid item xs={1.5}>
          <Item color={status_info?.color}>{status_info?.label}</Item>
        </Grid>
        <Grid item xs={2.5}>
          <TruncateText lines={2} sx={{ pr: 2 }}>
            {description}
          </TruncateText>
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

ClaimRecord.LoadingSkeleton = LoadingSkeleton;

export default ClaimRecord;
