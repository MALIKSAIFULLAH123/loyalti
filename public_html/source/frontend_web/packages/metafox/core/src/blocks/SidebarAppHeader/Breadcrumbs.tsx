import { RouteLink, useGlobal } from '@metafox/framework';
import { Typography, Box } from '@mui/material';
import * as React from 'react';
import useStyles from './styles';
import { LineIcon } from '@metafox/ui';

type Menu = {
  title?: string;
  to?: string;
};

type Props = {
  data: Menu[];
  item: Record<string, any>;
};

export default function Breadcrumbs({ data, item = {} }: Props) {
  const classes = useStyles();
  const { compactUrl, i18n } = useGlobal();

  if (!data?.length) return null;

  return (
    <Box sx={{ display: 'flex', alignItems: 'center', flexFlow: 'wrap' }}>
      {data.map(x => (
        <>
          <RouteLink
            to={compactUrl(x.to, item || {}) || item?.link}
            className={classes.link}
          >
            <Typography variant="body2" color="primary">
              {i18n.formatMessage({ id: x.title })}
            </Typography>
          </RouteLink>
          <Box
            mx={1}
            component={'span'}
            color={'text.hint'}
            sx={{ '&:last-child': { display: 'none' } }}
          >
            <LineIcon sx={{ fontSize: '10px' }} icon="ico-angle-right" />
          </Box>
        </>
      ))}
    </Box>
  );
}
