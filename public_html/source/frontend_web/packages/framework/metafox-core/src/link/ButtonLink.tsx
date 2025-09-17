import { Button, ButtonProps } from '@mui/material';
import * as React from 'react';
import BaseLink, { LinkProps } from './RouteLink';

export type ButtonLinkProps = LinkProps &
  ButtonProps & { children?: React.ReactNode };

function ButtonLink(props: ButtonLinkProps, ref) {
  return <Button component={BaseLink} {...props} ref={ref} />;
}

export default React.forwardRef(ButtonLink);
