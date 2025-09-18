/**
 * @type: itemView
 * name: advertise.itemView.invoiceMobileRecord
 * chunkName: advertise
 */

import { useGetItem, useGlobal, Link } from '@metafox/framework';
import { TruncateText } from '@metafox/ui';
import { Box, styled, Typography } from '@mui/material';
import { isEmpty, isString } from 'lodash';
import moment from 'moment';
import * as React from 'react';
import LoadingSkeleton from './LoadingSkeleton';

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

const Item = styled(Box, {
  shouldForwardProp: props => props !== 'center' && props !== 'color'
})<{
  center?: boolean;
  color?: string;
}>(({ theme, center, color }) => ({
  ...(center && { textAlign: 'center' }),
  ...(isString(color) && { color })
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

const ButtonAction = ({ item, identity }: any) => {
  const { ItemActionMenu, useActionControl } = useGlobal();
  const [handleAction] = useActionControl<unknown, unknown>(identity, {});

  const { can_cancel, can_payment } = item.extra;

  if (!can_cancel && !can_payment) return null;

  return (
    <ItemActionMenu
      identity={identity}
      icon={'ico-dottedmore-vertical-o'}
      menuName="itemActionMenu"
      handleAction={handleAction}
      size="smaller"
    />
  );
};

const Invoice = ({ identity }: any) => {
  const { i18n } = useGlobal();
  const item = useGetItem(identity);
  const itemAdvertise = useGetItem(item?.item);

  if (!item) return null;

  const { transaction_id, paid_at, payment_status, price, item_title } = item;

  return (
    <ItemWrapperStyled>
      <WrapActionStyled>
        <ButtonAction item={item} identity={identity} />
      </WrapActionStyled>
      <RowStyled>
        <TitleRowStyle variant="h5">
          {i18n.formatMessage({ id: 'title' })}
        </TitleRowStyle>
        <ValueRowStyled>
          {isEmpty(itemAdvertise) && isEmpty(item_title) ? (
            i18n.formatMessage({ id: 'n_a' })
          ) : (
            <TruncateText lines={2} sx={{ pr: 2 }}>
              {isEmpty(itemAdvertise) ? (
                item_title
              ) : (
                <Link to={itemAdvertise?.link} underline="none" color="primary">
                  {itemAdvertise?.title}
                </Link>
              )}
            </TruncateText>
          )}
        </ValueRowStyled>
      </RowStyled>
      <RowStyled>
        <TitleRowStyle variant="h5">
          {i18n.formatMessage({ id: 'transaction_id' })}
        </TitleRowStyle>
        <ValueRowStyled>
          {transaction_id === null || transaction_id === '' ? (
            <Item>{i18n.formatMessage({ id: 'n_a' })}</Item>
          ) : (
            transaction_id
          )}
        </ValueRowStyled>
      </RowStyled>
      <RowStyled>
        <TitleRowStyle variant="h5">
          {i18n.formatMessage({ id: 'start_date' })}
        </TitleRowStyle>
        <ValueRowStyled>
          {paid_at ? moment(paid_at).format('LL') : ''}
        </ValueRowStyled>
      </RowStyled>
      <RowStyled>
        <TitleRowStyle variant="h5">
          {i18n.formatMessage({ id: 'status' })}
        </TitleRowStyle>
        <ValueRowStyled>
          {payment_status ? (
            <Item color={payment_status?.color}>{payment_status?.label}</Item>
          ) : null}
        </ValueRowStyled>
      </RowStyled>
      <RowStyled>
        <TitleRowStyle variant="h5">
          {i18n.formatMessage({ id: 'price' })}
        </TitleRowStyle>
        <ValueRowStyled>{price}</ValueRowStyled>
      </RowStyled>
    </ItemWrapperStyled>
  );
};

Invoice.LoadingSkeleton = LoadingSkeleton;

export default Invoice;
