/**
 * @type: ui
 * name: dataGrid.cell.UserCell
 */

import { RouteLink } from '@metafox/framework';
import { styled, Tooltip } from '@mui/material';
import { get } from 'lodash';
import React from 'react';
import { LineIcon } from '@metafox/ui';

const FeaturedIcon = styled(LineIcon, { name: 'FeaturedIcon' })(
  ({ theme }) => ({
    color: theme.palette.primary.main,
    marginLeft: theme.spacing(0.5),
    fontSize: 12
  })
);

const StyledBox = styled('span', {
  name: 'StyledBox'
})<{ truncateLines: number }>(({ truncateLines }) => ({
  ...(truncateLines && {
    wordBreak: 'break-word',
    whiteSpace: 'normal',
    display: '-webkit-box',
    overflow: 'hidden',
    WebkitBoxOrient: 'vertical',
    WebkitLineClamp: `${truncateLines}`
  })
}));

export default function UserCell({
  row,
  colDef: { field, target, urlField, truncateLines, tooltip, tooltipDefault }
}) {
  const content = get(row, field, null);
  const sx = get(row, 'sx');
  const sxProps = get(sx, field);
  let url: string = '';
  const tooltipText = tooltip
    ? get<string>(row, tooltip) || tooltipDefault
    : null;

  if (urlField) {
    url = get<string>(row, urlField);

    if (url) {
      return (
        <Tooltip title={tooltipText}>
          <RouteLink target={target} to={url}>
            <StyledBox truncateLines={truncateLines} sx={sxProps}>
              {content}
              {row?.is_featured && <FeaturedIcon icon="ico-check-circle" />}
            </StyledBox>
          </RouteLink>
        </Tooltip>
      );
    }
  }

  return (
    <Tooltip title={tooltipText}>
      <StyledBox truncateLines={truncateLines} sx={sxProps}>
        {content}
      </StyledBox>
    </Tooltip>
  );
}
