import {
  RouteLink,
  SideMenuBlockProps,
  useAppUI,
  useGlobal
} from '@metafox/framework';
import { Typography } from '@mui/material';
import * as React from 'react';
import { Block, BlockContent } from '@metafox/layout';

export interface Props extends SideMenuBlockProps {
  sidebarHeaderName: string;
  titleProperty?: string;
}

export default function SideMenuBlock({
  sidebarHeaderName = 'homepageHeader'
}: Props) {
  const { usePageParams, i18n } = useGlobal();
  const { appName, pageTitle } = usePageParams();

  const sidebarHeader = useAppUI(appName, sidebarHeaderName);

  if (!sidebarHeader) return null;

  const { title, to: toProp } = sidebarHeader;

  return (
    <Block testid="blockAppHeader">
      <BlockContent>
        <RouteLink to={toProp || '/saved'}>
          <Typography variant="body2" color="primary">
            {i18n.formatMessage({
              id: pageTitle ?? title
            })}
          </Typography>
        </RouteLink>
      </BlockContent>
    </Block>
  );
}
