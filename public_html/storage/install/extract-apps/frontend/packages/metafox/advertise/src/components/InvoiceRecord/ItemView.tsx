/**
 * @type: itemView
 * name: advertise.itemView.invoiceRecord
 * chunkName: advertise
 */

import { useGetItem, useGlobal, Link } from '@metafox/framework';
import { TruncateText, ItemView } from '@metafox/ui';
import { Box, Grid, styled } from '@mui/material';
import { isEmpty, isString } from 'lodash';
import moment from 'moment';
import * as React from 'react';
import LoadingSkeleton from './LoadingSkeleton';

const Item = styled(Box, {
  shouldForwardProp: props => props !== 'center' && props !== 'color'
})<{
  center?: boolean;
  color?: string;
}>(({ theme, center, color }) => ({
  ...(center && { textAlign: 'center' }),
  ...(isString(color) && { color })
}));

const GridItem = styled(Grid)(({ theme }) => ({
  wordBreak: 'break-all',
  paddingRight: theme.spacing(2)
}));

const ButtonAction = ({ item, identity }: any) => {
  const { ItemActionMenu, useActionControl, i18n } = useGlobal();
  const [handleAction] = useActionControl<unknown, unknown>(identity, {});

  const { can_cancel, can_payment } = item.extra;

  if (!can_cancel && !can_payment)
    return <Box sx={{ ml: 0.5 }}>{i18n.formatMessage({ id: 'n_a' })}</Box>;

  return (
    <Box>
      <ItemActionMenu
        identity={identity}
        icon={'ico-dottedmore-vertical-o'}
        menuName="itemActionMenu"
        handleAction={handleAction}
        size="smaller"
      />
    </Box>
  );
};

const Invoice = ({ identity, wrapAs, wrapProps }: any) => {
  const { i18n } = useGlobal();
  const item = useGetItem(identity);
  const itemAdvertise = useGetItem(item?.item);

  if (!item) return null;

  const { transaction_id, paid_at, payment_status, price, item_title } = item;

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid="item-advertise">
      <Grid container alignItems="center">
        <GridItem item xs={3}>
          {isEmpty(itemAdvertise) && isEmpty(item_title) ? (
            i18n.formatMessage({ id: 'n_a' })
          ) : (
            <TruncateText lines={2}>
              {isEmpty(itemAdvertise) ? (
                item_title
              ) : (
                <Link to={itemAdvertise?.link} underline="none" color="primary">
                  {itemAdvertise?.title}
                </Link>
              )}
            </TruncateText>
          )}
        </GridItem>
        <GridItem item xs={2}>
          {transaction_id === null || transaction_id === '' ? (
            <Item>{i18n.formatMessage({ id: 'n_a' })}</Item>
          ) : (
            transaction_id
          )}
        </GridItem>
        <Grid item xs={2}>
          {paid_at ? moment(paid_at).format('LL') : ''}
        </Grid>
        <Grid item xs={2}>
          {payment_status ? (
            <Item color={payment_status?.color}>{payment_status?.label}</Item>
          ) : null}
        </Grid>
        <Grid item xs={2}>
          {price}
        </Grid>
        <Grid item xs={1}>
          <ButtonAction item={item} identity={identity} />
        </Grid>
      </Grid>
    </ItemView>
  );
};

Invoice.LoadingSkeleton = LoadingSkeleton;

export default Invoice;
