import { Link, LinkProps, IS_ADMINCP } from '@metafox/framework';
import * as React from 'react';
import { ItemUserShape } from '.';
import { Box } from '@mui/material';
import { FeaturedIcon } from '@metafox/ui';

type UserNameProps = {
  user: ItemUserShape;
  to?: string;
  className?: string;
  hoverCard?: any;
  InnerWrapAs: React.ElementType;
  showFeaturedBadge?: boolean;
  featuredBadgeProps?: Record<string, any>;
  rootProps?: Record<string, any>;
} & LinkProps;

const UserName = ({
  user,
  to,
  color = 'inherit',
  underline = 'hover',
  hoverCard: hoverCardProp,
  InnerWrapAs,
  rootProps = {},
  showFeaturedBadge = false,
  featuredBadgeProps = {},
  ...rest
}: UserNameProps) => {
  if (!user) return null;

  if (user?.is_deleted) {
    return <Box component="span">{user?.full_name || user?.title}</Box>;
  }

  const resource_name = user?.module_name || 'user';

  const hoverCardUrl =
    user?.id && resource_name ? `/${resource_name}/${user?.id}` : '';
  const hoverCard = hoverCardProp ?? hoverCardUrl;
  const toLink =
    to ||
    (IS_ADMINCP
      ? user?.url
      : user?.link || `/${user?.resource_name}/${user?.id}`);

  return (
    <Box
      component={'span'}
      sx={{ position: 'relative', maxWidth: '100%' }}
      {...rootProps}
    >
      <Link
        underline={underline}
        color={color}
        hoverCard={hoverCard}
        to={toLink}
        {...rest}
      >
        {InnerWrapAs ? (
          <InnerWrapAs>{user?.full_name || user?.title}</InnerWrapAs>
        ) : (
          user?.full_name || user?.title
        )}
        <FeaturedIcon
          icon="ico-check-circle"
          value={showFeaturedBadge && user?.is_featured}
          sx={{
            position: 'absolute',
            right: 0,
            top: '2px',
            transform: 'translateX(100%) translateX(4px)'
          }}
          {...featuredBadgeProps}
        />
      </Link>
    </Box>
  );
};

export default UserName;
