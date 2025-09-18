import { Link, useGetItem, useGlobal } from '@metafox/framework';
import {
  ItemAction,
  ItemMedia,
  ItemText,
  ItemView,
  Statistic,
  UserAvatar
} from '@metafox/ui';
import { UserItemProps } from '@metafox/user/types';
import { Button, styled, Typography } from '@mui/material';
import * as React from 'react';
import LoadingSkeleton from './LoadingSkeleton';

const TypographyStyled = styled(Typography, { name: 'TypographyStyled' })(
  ({ theme }) => ({
    color: theme.palette.text.primary,
    fontSize: theme.mixins.pxToRem(16),
    marginBottom: '0.1em',
    transition: 'all 300ms ease',
    whiteSpace: 'nowrap',
    overflow: 'hidden',
    textOverflow: 'ellipsis',
    fontWeight: theme.typography.fontWeightBold
  })
);

const ButtonStyled = styled('div', { name: 'ButtonStyled' })(({ theme }) => ({
  fontSize: '0.8125rem',
  color: theme.palette.text.secondary,
  display: 'inline-block'
}));

const ButtonInviteStyled = styled(Button, { name: 'ButtonInviteStyled' })(
  ({ theme }) => ({
    fontWeight: theme.typography.fontWeightBold
  })
);

export default function InviteItem({
  item,
  identity,
  itemProps,
  wrapAs,
  wrapProps,
  actions
}: UserItemProps) {
  const { i18n } = useGlobal();
  const user = useGetItem(item?.owner);
  const [isSubmit, setIsSubmit] = React.useState(false);

  if (!user) return null;

  const { statistic, id, full_name, link: to } = user;

  const onAction = () => {
    setIsSubmit(true);
    actions.cancelInvitation(() => setIsSubmit(false));
  };

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
    >
      <ItemMedia>
        <UserAvatar user={user} size={48} />
      </ItemMedia>
      <ItemText>
        <TypographyStyled variant="subtitle1" component="div">
          <Link to={to} children={full_name} hoverCard={`/user/${id}`} />
        </TypographyStyled>
        <ButtonStyled role="button">
          <Statistic values={statistic} display="total_mutual" />
        </ButtonStyled>
      </ItemText>
      {item?.extra?.can_remove_invite && (
        <ItemAction>
          <ButtonInviteStyled
            size="small"
            variant="outlined"
            disabled={isSubmit}
            onClick={onAction}
          >
            {i18n.formatMessage({ id: 'cancel_invite' })}
          </ButtonInviteStyled>
        </ItemAction>
      )}
    </ItemView>
  );
}

InviteItem.LoadingSkeleton = LoadingSkeleton;
