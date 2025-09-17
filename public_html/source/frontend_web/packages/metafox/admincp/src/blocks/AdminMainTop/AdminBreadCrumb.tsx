import { Link } from '@metafox/framework';
import { MenuItemShape } from '@metafox/ui';
import { Box, Skeleton, styled, Typography } from '@mui/material';
import React from 'react';

interface Props {
  breadcrumbs: MenuItemShape[];
  appName: string;
}

const sx: React.CSSProperties = {
  display: 'inline-block',
  whiteSpace: 'nowrap',
  letterSpacing: -0.1,
  overflow: 'hidden',
  textOverflow: 'ellipsis',
  maxWidth: 200,
  fontSize: 15
};

const fullSx = {
  display: 'inline-block',
  whiteSpace: 'nowrap',
  letterSpacing: -0.1,
  fontSize: 15
};

const BreadcrumbItem = ({ to, label, full = false }) => {
  if (!to) {
    return (
      <Typography component="span" sx={sx}>
        {label}
      </Typography>
    );
  }

  return (
    <Link to={to} underline="hover" color="primary" sx={full ? fullSx : sx}>
      {label}
    </Link>
  );
};

const Root = styled(Box, {
  name: 'AdminBreadCrumb',
  slot: 'Root'
})(({ theme }) => ({
  width: '275px',
  display: 'flex',
  height: 32,
  alignItems: 'center',
  [theme.breakpoints.up('md')]: {
    width: '400px'
  },
  [theme.breakpoints.up('lg')]: {
    width: 'auto',
    minWidth: '400px'
  },
  [theme.breakpoints.down('sm')]: {
    maxWidth: '60%'
  }
}));

function Separator({ loading = false }) {
  return (
    <Typography
      component="span"
      color={loading ? 'text.hint' : 'text.secondary'}
      sx={{ px: 0.6 }}
    >
      »
    </Typography>
  );
}

const AdminBreadCrumb = ({ breadcrumbs: items }: Props) => {
  const exists = items?.length;

  return (
    <Root data-testid="Breadcrumbs">
      {exists ? (
        items.map((item, key) => {
          return (
            <React.Fragment key={key.toString()}>
              {key > 0 ? <Separator /> : null}
              <BreadcrumbItem full={key < 2} to={item.to} label={item.label} />
            </React.Fragment>
          );
        })
      ) : (
        <>
          <Skeleton variant="text" width={74} height={20}></Skeleton>
          <Separator loading />
          <Skeleton variant="text" width={50} height={20}></Skeleton>
          <Separator loading />
          <Skeleton variant="text" width={50} height={20}></Skeleton>
        </>
      )}
    </Root>
  );
};

export default AdminBreadCrumb;
