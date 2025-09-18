import { useGetItem, useGlobal, useIsMobile } from '@metafox/framework';
import { Box, Grid, styled } from '@mui/material';
import * as React from 'react';
import { ItemView, UserName } from '@metafox/ui';
import moment from 'moment';

const BoxRow = styled(Box)(({ theme }) => ({
  display: 'flex',
  justifyContent: 'space-between'
}));

const LeftRow = styled(Box)(({ theme }) => ({
  fontWeight: theme.typography.fontWeightSemiBold,
  marginBottom: theme.spacing(2),
  color: theme.palette.text.primary
}));

const RightRow = styled(Box)(({ theme }) => ({
  marginBottom: theme.spacing(2),
  textAlign: 'right'
}));

const ItemViewMobileStyled = styled(ItemView)(({ theme }) => ({
  display: 'block'
}));

const RequestMember = ({
  identity,
  wrapAs,
  wrapProps,
  state,
  handleAction
}: any) => {
  const item = useGetItem(identity);
  const isMobile = useIsMobile();
  const user = useGetItem(item?.user);

  const { i18n, ItemActionMenu } = useGlobal();

  if (!item) return null;

  const { created_at, status_text, updated_at, extra } = item;

  if (isMobile) {
    return (
      <ItemViewMobileStyled
        wrapAs={wrapAs}
        wrapProps={wrapProps}
        testid="item-transaction-mobile"
      >
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'member' })}</LeftRow>
          <RightRow>
            {' '}
            <UserName user={user} to={user?.link} color="primary" />
          </RightRow>
        </BoxRow>
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'status' })}</LeftRow>
          <RightRow>{status_text}</RightRow>
        </BoxRow>
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'creation_date' })}</LeftRow>
          <RightRow>{moment(created_at).format('L')}</RightRow>
        </BoxRow>
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'modified_date' })}</LeftRow>
          <RightRow>
            {extra.can_approve
              ? null
              : moment(updated_at).format('L')}
          </RightRow>
        </BoxRow>

        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'options' })}</LeftRow>
          <RightRow>
            <ItemActionMenu
              identity={identity}
              icon="ico-gear-o"
              state={state}
              handleAction={handleAction}
            />
          </RightRow>
        </BoxRow>
      </ItemViewMobileStyled>
    );
  }

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid="item-transaction">
      <Grid container alignItems="center">
        <Grid item xs={3}>
          <UserName user={user} to={user?.link} color="primary" />
        </Grid>
        <Grid item xs={2}>
          {status_text}
        </Grid>
        <Grid item xs={3}>
          {moment(created_at).format('L')}
        </Grid>

        <Grid item xs={3}>
          {extra.can_approve ? null : moment(updated_at).format('L')}
        </Grid>

        <Grid item xs={1}>
          <ItemActionMenu
            identity={identity}
            icon="ico-gear-o"
            state={state}
            handleAction={handleAction}
          />
        </Grid>
      </Grid>
    </ItemView>
  );
};

export default RequestMember;
