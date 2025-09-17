import { useGetItem, Link, useGlobal } from '@metafox/framework';
import { TruncateText } from '@metafox/ui';
import { Box, styled, Typography } from '@mui/material';
import moment from 'moment';
import * as React from 'react';
import { AdItemProps } from '../../types';

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

const WrapActionStyled = styled(Box)(() => ({
  position: 'absolute',
  right: 8,
  top: 20
}));

const Item = styled(Box, { shouldForwardProp: props => props !== 'center' })<{
  center?: boolean;
}>(({ center }) => ({
  ...(center && {
    textAlign: 'center'
  })
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
const TitleRowStyle = styled(Typography, { slot: 'title' })(() => ({}));
const ValueRowStyled = styled(Box, { slot: 'value' })(() => ({}));

const ItemView = ({ handleAction, item, identity }: AdItemProps) => {
  const { ItemActionMenu, i18n } = useGlobal();
  const packageItem = useGetItem(item?.package);
  const payment_gateway = useGetItem(item?.payment_gateway);

  if (!item) return null;

  const {
    price,
    item_link,
    status,
    item_type_label,
    item_title,
    creation_date,
    transaction_id
  } = item;

  return (
    <ItemWrapperStyled>
      <WrapActionStyled>
        <ItemActionMenu
          identity={identity}
          icon={'ico-dottedmore-vertical-o'}
          handleAction={handleAction}
          size="smaller"
        />
      </WrapActionStyled>
      <RowStyled>
        <TitleRowStyle variant="h5">
          {i18n.formatMessage({ id: 'item' })}
        </TitleRowStyle>
        <ValueRowStyled>
          <TruncateText lines={2} sx={{ pr: 2 }}>
            <Link to={item_link} underline="none" color="primary">
              {item_title}
            </Link>
          </TruncateText>
        </ValueRowStyled>
      </RowStyled>
      <RowStyled>
        <TitleRowStyle variant="h5">
          {i18n.formatMessage({ id: 'item_type' })}
        </TitleRowStyle>
        <ValueRowStyled>
          <Item>{item_type_label}</Item>
        </ValueRowStyled>
      </RowStyled>
      {packageItem && (
        <RowStyled>
          <TitleRowStyle variant="h5">
            {i18n.formatMessage({ id: 'package' })}
          </TitleRowStyle>
          <ValueRowStyled>
            <Item>{packageItem?.title}</Item>
          </ValueRowStyled>
        </RowStyled>
      )}
      <RowStyled>
        <TitleRowStyle variant="h5">
          {i18n.formatMessage({ id: 'status' })}
        </TitleRowStyle>
        <ValueRowStyled>
          <Item color={status?.color}>{status?.label}</Item>
        </ValueRowStyled>
      </RowStyled>
      {payment_gateway && (
        <RowStyled>
          <TitleRowStyle variant="h5">
            {i18n.formatMessage({ id: 'payment_gateway' })}
          </TitleRowStyle>
          <ValueRowStyled>
            <Item>{payment_gateway?.title}</Item>
          </ValueRowStyled>
        </RowStyled>
      )}
      <RowStyled>
        <TitleRowStyle variant="h5">
          {i18n.formatMessage({ id: 'price' })}
        </TitleRowStyle>
        <ValueRowStyled>
          <Item>{price}</Item>
        </ValueRowStyled>
      </RowStyled>
      <RowStyled>
        <TitleRowStyle variant="h5">
          {i18n.formatMessage({ id: 'transaction_id' })}
        </TitleRowStyle>
        <ValueRowStyled>
          <Item>{transaction_id}</Item>
        </ValueRowStyled>
      </RowStyled>
      <RowStyled>
        <TitleRowStyle variant="h5">
          {i18n.formatMessage({ id: 'creation_date' })}
        </TitleRowStyle>
        <ValueRowStyled>{moment(creation_date).format('lll')}</ValueRowStyled>
      </RowStyled>
    </ItemWrapperStyled>
  );
};

export default ItemView;
