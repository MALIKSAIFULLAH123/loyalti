/**
 * @type: ui
 * name: core.sideAppHeaderBlock
 * chunkName: sidebar
 */
import {
  RouteLink,
  SideMenuBlockProps,
  useAppUI,
  useGlobal
} from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import { IconButton, Typography, styled } from '@mui/material';
import * as React from 'react';
import useStyles from './styles';
import Breadcrumbs from './Breadcrumbs';
import { LineIcon } from '@metafox/ui';

const StyledBack = styled(IconButton, {
  name: 'SidebarAppHeader',
  slot: 'styledBackButton',
  overridesResolver(props, styles) {
    return [styles.styledBackButton];
  }
})(({ theme }) => ({
  display: 'none'
}));

const APP_GROUP = 'group';
const APP_PAGE = 'page';

export interface Props extends SideMenuBlockProps {
  sidebarHeaderName: string;
  titleProperty?: string;
}

export default function SideMenuBlock({
  blockProps,
  sidebarHeaderName = 'homepageHeader',
  titleProperty
}: Props) {
  const classes = useStyles();
  const { usePageParams, useFetchDetail, compactUrl, i18n, navigate } =
    useGlobal();
  const {
    appName,
    breadcrumb,
    heading,
    pageTitle,
    id,
    resourceName,
    backPage,
    backPageProps
  } = usePageParams();

  const sidebarHeader = useAppUI(appName, sidebarHeaderName);

  const [item] = useFetchDetail({
    dataSource:
      id && (resourceName === APP_PAGE || resourceName === APP_GROUP)
        ? { apiUrl: `/${appName}/${id}` }
        : null
  });

  if (!sidebarHeader) return null;

  const { title, to: toProp, breadcrumbsData } = sidebarHeader;
  const to = toProp || item?.link;
  const customTitle = titleProperty && item && item[titleProperty];
  const link = customTitle && `/${resourceName}/${id}`;

  const backProps = backPageProps
    ? backPageProps
    : { title: pageTitle, to: resourceName };

  if (breadcrumb || breadcrumbsData) {
    return (
      <Block testid="blockAppHeader">
        <BlockContent>
          {breadcrumbsData ? (
            <Breadcrumbs data={breadcrumbsData} item={item} />
          ) : (
            <RouteLink
              to={link || compactUrl(to, item || {})}
              className={classes.link}
            >
              <Typography variant="body2" color="primary">
                {i18n.formatMessage({
                  id: pageTitle ?? customTitle ?? title
                })}
              </Typography>
            </RouteLink>
          )}
          {heading && typeof heading === 'string' ? (
            <div className={classes.header}>
              <Typography component="h1" variant="h3" color="textPrimary">
                {i18n.formatMessage({ id: heading })}
              </Typography>
            </div>
          ) : null}
        </BlockContent>
      </Block>
    );
  }

  return (
    <Block testid="blockAppHeader">
      <BlockContent>
        {backPage ? (
          <RouteLink to={backProps.to} className={classes.link}>
            <Typography variant="body2" color="primary">
              {i18n.formatMessage({ id: backProps.title })}
            </Typography>
          </RouteLink>
        ) : null}
        <div className={classes.header}>
          <StyledBack
            size="small"
            role="button"
            onClick={() => navigate(-1)}
            sx={{ mr: 1 }}
          >
            <LineIcon icon="ico-arrow-left" />
          </StyledBack>
          <Typography
            component="h1"
            variant="h3"
            color="textPrimary"
            className={classes.title}
          >
            {i18n.formatMessage({ id: pageTitle ?? title })}
          </Typography>
        </div>
      </BlockContent>
    </Block>
  );
}
