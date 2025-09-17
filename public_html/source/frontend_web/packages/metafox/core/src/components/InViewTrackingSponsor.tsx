/**
 * @type: service
 * name: InViewTrackingSponsor
 */

import {
  useGlobal,
  TRACKING_IN_VIEW,
  TRACKING_IN_VIEW_EXIT
} from '@metafox/framework';
import React from 'react';
import { Box } from '@mui/material';
import { useInView } from 'react-intersection-observer';
import { useBlock } from '@metafox/layout';

const SponsorshipTrackingItem = ({ identity, children }: any) => {
  const { dispatch, useGetItem } = useGlobal();
  const { isTrackingSponsor } = useBlock();
  const item = useGetItem(identity);
  const [refTrackingView, inView] = useInView({
    threshold: 0
  });

  const isTrackingViewSponsor = item?.is_sponsor && isTrackingSponsor;

  // eslint-disable-next-line react-hooks/rules-of-hooks
  React.useEffect(() => {
    if (item?._sponsorship_viewed) return;

    dispatch({
      type: inView ? TRACKING_IN_VIEW : `${TRACKING_IN_VIEW_EXIT}/${identity}`,
      payload: { identity }
    });

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [inView]);

  // eslint-disable-next-line react-hooks/rules-of-hooks
  React.useEffect(() => {
    // clean
    return () => {
      dispatch({
        type: `${TRACKING_IN_VIEW_EXIT}/${identity}`,
        payload: { identity }
      });
    };

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  if (!isTrackingViewSponsor || !item) return null;

  return <Box ref={refTrackingView}>{children}</Box>;
};

export default SponsorshipTrackingItem;
