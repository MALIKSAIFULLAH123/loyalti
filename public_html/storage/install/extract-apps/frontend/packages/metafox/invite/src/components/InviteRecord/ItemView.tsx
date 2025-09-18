/**
 * @type: itemView
 * name: invite.itemView.inviteRecord
 * chunkName: invite
 */

import { useGetItem, useGlobal } from '@metafox/framework';
import { ItemView } from '@metafox/ui';
import { Box, Checkbox, Grid, styled, Typography } from '@mui/material';
import moment from 'moment';
import * as React from 'react';
import LoadingSkeleton from './LoadingSkeleton';
import { isEmpty } from 'lodash';

enum StatusColor {
  COMPLETED = 'Completed',
  PENDING = 'Pending'
}

const Item = styled(Box, {
  shouldForwardProp: props => props !== 'center' && props !== 'status'
})<{
  center?: boolean;
  status?: string;
}>(({ theme, center, status }) => ({
  wordBreak: 'break-word',
  ...(center && {
    textAlign: 'center'
  }),
  ...(status === StatusColor.COMPLETED && {
    color: theme.palette.success.main
  }),
  ...(status === StatusColor.PENDING && {
    color: theme.palette.warning.main
  })
}));

const ItemContentStyled = styled(Box)(({ theme }) => ({
  flex: 1,
  display: 'flex',
  alignItems: 'center',

  padding: theme.spacing(1, 2),
  background: theme.palette.background.default,
  borderRadius: theme.shape.borderRadius,
  marginLeft: theme.spacing(2)
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

const InviteRecord = ({ identity, wrapAs, wrapProps, state }: any) => {
  const {
    ItemActionMenu,
    useActionControl,
    useBatchSelectContext,
    useIsMobile,
    i18n
  } = useGlobal();
  const item = useGetItem(identity);
  const isMobile = useIsMobile();

  const [handleAction] = useActionControl<unknown, unknown>(identity, {});

  const valueContext = useBatchSelectContext();

  const { handleToggleItem, checkedList, loading } = valueContext;

  const [checked, setChecked] = React.useState(false);

  const handleCheckboxChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    handleToggleItem(item?.id, !checked);
    setChecked(prev => !prev);
  };

  React.useEffect(() => {
    if (isEmpty(item)) return;

    const checkedProp = checkedList.some(x => x === item?.id);
    setChecked(checkedProp);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [checkedList, item?.id]);

  if (!item) return null;

  const { phone_number, email, status_info, updated_at } = item || {};

  if (isMobile) {
    return (
      <ItemWrapperStyled>
        <WrapActionStyled>
          <ItemActionMenu
            identity={identity}
            menuName="itemActionMenu"
            icon={'ico-dottedmore-vertical-o'}
            handleAction={handleAction}
            size="smaller"
          />
        </WrapActionStyled>
        <RowStyled>
          <TitleRowStyle variant="h5">
            {i18n.formatMessage({ id: 'email_phone' })}
          </TitleRowStyle>
          <ValueRowStyled>
            <Item>{email || phone_number}</Item>
          </ValueRowStyled>
        </RowStyled>
        <RowStyled>
          <TitleRowStyle variant="h5">
            {i18n.formatMessage({ id: 'status' })}
          </TitleRowStyle>
          <ValueRowStyled>
            <Box color={status_info?.color}>{status_info?.label}</Box>
          </ValueRowStyled>
        </RowStyled>
        <RowStyled>
          <TitleRowStyle variant="h5">
            {i18n.formatMessage({ id: 'invited_date' })}
          </TitleRowStyle>
          <ValueRowStyled>
            {updated_at ? (
              <Item>{moment(updated_at).format('LLL')}</Item>
            ) : null}
          </ValueRowStyled>
        </RowStyled>
        <RowStyled></RowStyled>
      </ItemWrapperStyled>
    );
  }

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid="item-invite">
      <Item center>
        <Checkbox
          disabled={loading}
          checked={checked}
          onChange={handleCheckboxChange}
          color="primary"
          size="medium"
        />
      </Item>

      <ItemContentStyled>
        <Grid container alignItems="center">
          <Grid item xs={7}>
            {email || phone_number}
          </Grid>
          <Grid item xs={1.5}>
            <Box color={status_info?.color}>{status_info?.label}</Box>
          </Grid>
          <Grid item xs={3}>
            {updated_at ? moment(updated_at).format('LLL') : null}
          </Grid>
          <Grid item xs={0.5}>
            <Item center>
              <ItemActionMenu
                identity={identity}
                menuName="itemActionMenu"
                icon={'ico-dottedmore-vertical-o'}
                handleAction={handleAction}
                size="smaller"
              />
            </Item>
          </Grid>
        </Grid>
      </ItemContentStyled>
    </ItemView>
  );
};

InviteRecord.LoadingSkeleton = LoadingSkeleton;

export default InviteRecord;
