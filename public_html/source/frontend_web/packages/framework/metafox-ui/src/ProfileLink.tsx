import { RouteLink } from '@metafox/framework';
import { Box } from '@mui/material';
import * as React from 'react';

export default function ProfileLink({ user, className }) {
  if (!user) return null;

  if (user?.is_deleted) {
    return (
      <Box component="span" className={className}>
        {user?.full_name || user?.title}
      </Box>
    );
  }

  const { resource_name, id, user_name } = user || {};
  const to = user_name ? `/${user_name}` : `/${resource_name}/${id}`;

  return (
    <RouteLink
      to={to}
      className={className}
      hoverCard={`/${resource_name}/${id}`}
    >
      {user?.full_name || user?.title}
    </RouteLink>
  );
}
