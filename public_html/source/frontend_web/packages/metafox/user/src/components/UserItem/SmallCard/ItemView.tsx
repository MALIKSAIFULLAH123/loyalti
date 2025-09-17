import { Link } from '@metafox/framework';
import {
  ItemMedia,
  ItemText,
  ItemView,
  Statistic,
  UserAvatar
} from '@metafox/ui';
import { UserItemProps } from '@metafox/user/types';
import { Typography, styled } from '@mui/material';
import * as React from 'react';
import useStyles from './styles';

const TypographyStyled = styled(Typography, {
  name: 'UserItemSmallCard',
  slot: 'typographyStyled',
  overridesResolver(props, styles) {
    return [styles.typographyStyled];
  }
})(({ theme }) => ({
  color: theme.palette.text.primary,
  fontSize: theme.mixins.pxToRem(18),
  marginBottom: '0.1em',
  transition: 'all 300ms ease',
  whiteSpace: 'nowrap',
  overflow: 'hidden',
  textOverflow: 'ellipsis',
  fontWeight: theme.typography.fontWeightBold
}));

export default function UserItem({
  item,
  identity,
  itemProps,
  wrapAs,
  wrapProps
}: UserItemProps) {
  const classes = useStyles();

  if (!item) return null;

  const { statistic, id, full_name, link: to, profile_settings } = item;

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
    >
      <ItemMedia>
        <UserAvatar user={item as any} size={48} hoverCard={`/user/${id}`} />
      </ItemMedia>
      <ItemText>
        <TypographyStyled variant="subtitle1" component="div">
          <Link to={to} children={full_name} hoverCard={`/user/${id}`} />
        </TypographyStyled>
        {profile_settings?.profile_view_profile && (
          <div role="button" className={classes.stats}>
            <Statistic values={statistic} display="total_mutual" />
          </div>
        )}
      </ItemText>
    </ItemView>
  );
}

UserItem.displayName = 'UserItemSmallCard';
