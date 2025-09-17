import { useGlobal, useResourceAction, Link } from '@metafox/framework';
import { useFetchDetail } from '@metafox/rest-client';
import { LineIcon, FormatNumberCompact } from '@metafox/ui';
import { Box, Typography } from '@mui/material';
import React from 'react';

const MS_1_HOUR = 3600000;

const ActivityPointSummary = ({ isOwner }) => {
  const { usePageParams, i18n } = useGlobal();
  const pageParams = usePageParams();

  const dataSource = useResourceAction(
    'activitypoint',
    'activitypoint',
    'getStatistic'
  );

  const [data] = useFetchDetail({
    dataSource,
    pageParams,
    cachePrefix: 'activityPoint',
    cacheKey: `profile_${pageParams?.profile_id || pageParams?.id}`,
    ttl: MS_1_HOUR
  });

  if (!dataSource || !data) return null;

  const { available_points } = data || {};

  if (!available_points) return null;

  if (!isOwner) {
    return (
      <Box display="flex" color="primary.main" alignItems={'center'}>
        <LineIcon icon="ico-star-circle-o" sx={{ pr: 0.5 }} />
        {i18n.formatMessage(
          { id: 'activity_point_summary' },
          {
            value: () => (
              <Typography sx={{ pl: 0.5 }} variant="h6">
                <FormatNumberCompact value={available_points} />
              </Typography>
            )
          }
        )}
      </Box>
    );
  }

  return (
    <Box display="flex" color="primary.main" alignItems={'center'}>
      <LineIcon icon="ico-star-circle-o" sx={{ pr: 0.5 }} />
      <Link to={'activitypoint'}>
        <Box display="flex" color="primary.main" alignItems={'center'}>
          {i18n.formatMessage(
            { id: 'activity_point_summary' },
            {
              value: () => (
                <Typography sx={{ pl: 0.5 }} variant="h6">
                  <FormatNumberCompact value={available_points} />
                </Typography>
              )
            }
          )}
        </Box>
      </Link>
    </Box>
  );
};

export default ActivityPointSummary;
