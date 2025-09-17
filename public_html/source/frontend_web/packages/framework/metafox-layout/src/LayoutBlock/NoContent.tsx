/**
 * @type: ui
 * name: no_content
 * title: No content with description
 * keywords: no content
 */

import { NoContentProps, useGlobal } from '@metafox/framework';
import { useBlock } from '@metafox/layout';
import { LineIcon } from '@metafox/ui';
import { Box, Typography } from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';

const NoContent = styled(Box, {
  name: 'NoContent',
  slot: 'Root'
})(({ theme }) => ({
  position: 'relative',
  display: 'flex',
  flexDirection: 'column'
}));

const NoContentMedia = styled(Box, {
  name: 'NoContent',
  slot: 'media'
})({});

const NoContentText = styled(Box, {
  name: 'NoContent',
  slot: 'text'
})({});

const NoContentIcon = styled(LineIcon, {
  name: 'NoContent',
  slot: 'icon'
})(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(72),
  color: theme.palette.text.secondary,
  marginBottom: theme.spacing(4)
}));

const NoContentTitle = styled(Typography, {
  name: 'NoContent',
  slot: 'title'
})(({ theme }) => ({}));

const NoContentSummary = styled(Typography, {
  name: 'NoContent',
  slot: 'summary'
})(({ theme }) => ({}));

export default function NoContentView({
  icon: iconProp,
  title: titleProp,
  description
}: NoContentProps) {
  const { i18n, usePageParams, layoutBackend } = useGlobal();
  const { noContentLayout } = useBlock();

  const noContentProps =
    layoutBackend.getNoContentPreset(noContentLayout) || {};

  const {
    noSummary,
    noTitle,
    noMedia,
    contentStyle,
    mediaStyle,
    textStyle,
    titleStyle,
    summaryStyle
  } = noContentProps;

  const pageParams = usePageParams();

  const icon = iconProp;

  // const menu = useSelector((state: GlobalState) =>
  //   getAppMenuSelector(state, pageParams.appName, 'sidebarMenu')
  // );

  // const icon =
  //   iconProp ||
  //   menu.items.find(item => item.tab === pageParams.tab)?.icon ||
  //   'ico-user-circle-o';

  const moduleName = pageParams.appName || pageParams.resourceName;
  const title = titleProp || `no_${moduleName}_found`;

  // no content with icon

  return (
    <NoContent {...contentStyle} data-testid="noResultFound">
      {noMedia || !icon ? null : (
        <NoContentMedia {...mediaStyle}>
          <NoContentIcon icon={icon} className="icon" />
        </NoContentMedia>
      )}
      <NoContentText {...textStyle}>
        {noTitle ? null : (
          <NoContentTitle {...titleStyle}>
            {i18n.formatMessage({ id: title })}
          </NoContentTitle>
        )}
        {noSummary ? null : (
          <NoContentSummary {...summaryStyle}>
            {i18n.formatMessage({ id: description })}
          </NoContentSummary>
        )}
      </NoContentText>
    </NoContent>
  );
}
