import React from 'react';
import { useGlobal } from '@metafox/framework';
import { LinkProps as RouteLinkProps } from 'react-router-dom';

export type ExternalLinkProps = RouteLinkProps & {
  children?: React.ReactNode;
  href?: string;
  onClick?: () => void;
  onCancel?: () => void;
};

const ExternalLink = (props: ExternalLinkProps) => {
  const {
    onClick,
    onCancel,
    href,
    children,
    target = '_blank',
    ...rest
  } = props;
  const { dialogBackend, i18n, getSetting } = useGlobal();
  const warnOnExternal = getSetting('core.spam.warning_on_external_links');
  const [clicked, setClicked] = React.useState(false);

  const handleClick = (
    e: React.SyntheticEvent<HTMLSpanElement, MouseEvent>
  ) => {
    if (!warnOnExternal) return;

    e.preventDefault();
    e.stopPropagation();

    if (clicked) return;

    setClicked(true);
    onClick && onClick();

    dialogBackend
      .confirm({
        message: i18n.formatMessage(
          {
            id: 'this_link_leads_to_an_untrusted_site_are_you_sure_you_want_to_proceed'
          },
          { link: href }
        )
      })
      .then(ok => {
        setClicked(false);

        if (ok) {
          if (target === '_blank') {
            const newTab = window.open();
            newTab.location.href = href;
          } else {
            window.location.href = href;
          }
        } else {
          onCancel && onCancel();
        }
      });
  };

  return (
    <a {...rest} target={target} href={href} onClick={handleClick}>
      {children}
    </a>
  );
};

export default ExternalLink;
