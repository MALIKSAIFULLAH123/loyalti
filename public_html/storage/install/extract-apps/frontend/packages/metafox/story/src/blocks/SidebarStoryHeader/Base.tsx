import {
  RouteLink,
  SideMenuBlockProps,
  useAppUI,
  useGlobal
} from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import { Box, Typography } from '@mui/material';
import * as React from 'react';
import useStyles from './styles';
import { DotSeparator } from '@metafox/ui';

export interface Props extends SideMenuBlockProps {
  sidebarHeaderName: string;
  titleProperty?: string;
  backPage?: boolean;
}

export default function SideMenuBlock({
  sidebarHeaderName = 'homepageHeader',
  backPage: backPageProp = true
}: Props) {
  const classes = useStyles();
  const { usePageParams, i18n, useSession, dialogBackend } = useGlobal();
  const { appName, pageTitle, resourceName, backPage, backPageProps } =
    usePageParams();

  const { user } = useSession();

  const sidebarHeader = useAppUI(appName, sidebarHeaderName);

  if (!sidebarHeader) return null;

  const { title } = sidebarHeader;

  const backProps = backPageProps
    ? backPageProps
    : { title: pageTitle, to: resourceName };

  const toArchive = `/user/${user?.id}/story-archive`;

  const handleOpenMuted = () => {
    dialogBackend.present({
      component: 'story.dialog.dialogMutedListing'
    });
  };

  return (
    <Block testid="blockAppHeader">
      <BlockContent>
        <Box display={'flex'}>
          {backPageProp && backPage ? (
            <RouteLink to={backProps.to} className={classes.link}>
              <Typography variant="body2" color="primary">
                {i18n.formatMessage({ id: backProps.title })}
              </Typography>
            </RouteLink>
          ) : null}
        </Box>
        <div className={classes.header}>
          <Typography
            component="h1"
            variant="h3"
            color="textPrimary"
            className={classes.title}
          >
            {i18n.formatMessage({ id: pageTitle ?? title })}
          </Typography>
        </div>
        {user?.id ? (
          <DotSeparator>
            <RouteLink
              data-testid="archiveLinkStory"
              to={toArchive}
              target="_blank"
              className={classes.menuItem}
            >
              <Typography variant="body1" color="primary">
                {i18n.formatMessage({ id: 'archive' })}
              </Typography>
            </RouteLink>
            <RouteLink
              data-testid="mutedLinkStory"
              onClick={handleOpenMuted}
              target="_blank"
              className={classes.menuItem}
            >
              <Typography variant="body1" color="primary">
                {i18n.formatMessage({ id: 'story_muted_menu' })}
              </Typography>
            </RouteLink>
          </DotSeparator>
        ) : null}
      </BlockContent>
    </Block>
  );
}
