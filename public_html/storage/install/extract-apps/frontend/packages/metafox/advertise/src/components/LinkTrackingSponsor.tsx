/**
 * @type: service
 * name: LinkTrackingSponsor
 */

import * as React from 'react';
import { LinkProps, RouteLink, useGlobal } from '@metafox/framework';
import { Link } from '@mui/material';

type Props = LinkProps & {
  identity?: string;
  tracking?: boolean;
};

const LinkTracking = React.forwardRef((props: Props, ref: any) => {
  const { identity, tracking = true, ...rest } = props;
  const { dispatch } = useGlobal();

  if (identity && tracking) {
    rest.onClick = () => {
      if (dispatch) {
        dispatch({
          type: 'link/trackingClick',
          payload: { identity }
        });
      }
    };
  }

  return <Link {...rest} ref={ref} component={RouteLink} />;
});

export default LinkTracking;
