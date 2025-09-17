import { useGlobal, useResourceAction } from '@metafox/framework';
import { usePageParams } from '@metafox/layout';
import { List, Typography, Box, styled } from '@mui/material';
import * as React from 'react';
import { isArray } from 'lodash';
import { useFetchDetail } from '@metafox/rest-client';
import ErrorBoundary from '@metafox/core/pages/ErrorPage/Page';
import Item from './Item';
import qs from 'query-string';

// 1 day in milliseconds
const MS_1_HOUR = 36e5;

const name = 'SearchItem';

const TitleSearchContent = styled(Typography, {
  name,
  slot: 'TitleSearchContentItem',
  shouldForwardProp: prop => prop !== 'isMobile',
  overridesResolver(props, styles) {
    return [styles.TitleSearchContentItem];
  }
})<{ isMobile?: boolean }>(({ theme, isMobile }) => ({
  paddingLeft: isMobile ? theme.spacing(2) : 0,
  paddingTop: theme.spacing(2)
}));

export default function Sections({
  searchAllAction = 'viewSections',
  ...others
}) {
  const pageParams = usePageParams();
  const { useIsMobile } = useGlobal();
  const { appName, resourceName } = pageParams;
  const dataSource = useResourceAction(appName, resourceName, searchAllAction);
  const isMobile = useIsMobile();

  const [data, loading, error] = useFetchDetail({
    dataSource,
    pageParams,
    cachePrefix: 'search',
    cacheKey: qs.stringify(pageParams),
    ttl: MS_1_HOUR,
    forceReload: false
  });

  return (
    <ErrorBoundary
      error={error}
      loading={loading}
      emptyComponent={data?.length > 0 ? undefined : 'core.block.no_results'}
    >
      <Box sx={{ maxWidth: '100%' }}>
        <List disablePadding>
          {isArray(data)
            ? data.map((item, key) => (
                <Box key={key}>
                  <TitleSearchContent variant="h4" isMobile={isMobile}>
                    {item.label}
                  </TitleSearchContent>
                  <Item item={item} isLimitMode {...others} />
                </Box>
              ))
            : null}
        </List>
      </Box>
    </ErrorBoundary>
  );
}
