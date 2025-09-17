import * as React from 'react';
import RouteLink, { LinkProps } from './RouteLink';
import { Link } from '@mui/material';
import { useGlobal, LINK_TRACKING_CLICK } from '@metafox/framework';
import { useBlock } from '@metafox/layout';

const MuiLink = React.forwardRef((props: LinkProps, ref: any) => {
  const {
    identityTracking: identityTrackingProp,
    identityTrackingClick,
    ...rest
  } = props;
  const { dispatch } = useGlobal();
  const { isTrackingSponsor, itemProps } = useBlock();
  const identityTracking = identityTrackingProp || identityTrackingClick;
  // Support old version itemProps?.isTrackingSponsor, identityTrackingClick. Remove on next version
  const isTracking = isTrackingSponsor || itemProps?.isTrackingSponsor;

  const isTrackingClickSponsor = identityTracking && isTracking;

  if (isTrackingClickSponsor) {
    rest.onClick = () => {
      if (dispatch) {
        dispatch({
          type: LINK_TRACKING_CLICK,
          payload: { identity: identityTracking }
        });
      }
    };
  }

  return <Link {...rest} ref={ref} component={RouteLink} />;
});

export default MuiLink;
