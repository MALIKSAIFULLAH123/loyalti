import { useGlobal, MFOX_ADMINCP_URL, IS_ADMINCP } from '@metafox/framework';
import { LinkProps as MuiLinkProps } from '@mui/material';
import { isString } from 'lodash';
import * as React from 'react';
import { Link, LinkProps as RouteLinkProps } from 'react-router-dom';
import { isExternalLink } from '@metafox/utils';
import ExternalLink from './ExternalLink';

const IsUrlReg = /^(http|https)?:?\/\//s;

export type LinkProps = MuiLinkProps &
  RouteLinkProps & {
    asModal?: boolean;
    asChildPage?: boolean;
    keepScroll?: boolean;
    keepModal?: boolean;
    hoverCard?: boolean | string;
    onClick?: (evt?: React.SyntheticEvent<HTMLElement>) => void;
    aliasPath?: string;
    href?: string;
    className?: string;
    identity?: string;
    identityTracking?: string;
    // old version
    identityTrackingClick?: string;
  };

export default React.forwardRef<HTMLAnchorElement, LinkProps>(
  (
    {
      to,
      asModal,
      hoverCard,
      keepScroll,
      asChildPage,
      aliasPath,
      keepModal,
      href,
      ...rest
    }: LinkProps,
    ref
  ) => {
    const mn = useGlobal();
    const { useIsMobile } = mn || {};
    const isMobile = useIsMobile();

    let state;

    if (asModal) {
      state = { asModal, keepScroll, aliasPath, keepModal };
    } else if (asChildPage) {
      state = { asChildPage, keepScroll, aliasPath };
    } else if (aliasPath) {
      state = { keepScroll, aliasPath };
    }

    if (!isMobile && (isString(hoverCard) || (hoverCard && to))) {
      rest['data-popover'] = isString(hoverCard) ? hoverCard : to;
      rest.onMouseLeave = mn.popoverBackend?.onLeaveAnchor;
      rest.onMouseEnter = mn.popoverBackend?.onEnterAnchor;
    }

    href = href ?? (isString(to) ? to : to?.pathname);

    if (href && isExternalLink(href)) {
      return <ExternalLink {...rest} to={to} href={href} />;
    }

    // some case url same domain but startWith http
    if (
      !to ||
      IsUrlReg.test(href) ||
      (!IS_ADMINCP && href && href?.startsWith(MFOX_ADMINCP_URL))
    ) {
      // eslint-disable-next-line jsx-a11y/anchor-has-content
      return <a href={href} {...rest} />;
    }

    return <Link to={to} ref={ref} state={state} {...rest} />;
  }
);
