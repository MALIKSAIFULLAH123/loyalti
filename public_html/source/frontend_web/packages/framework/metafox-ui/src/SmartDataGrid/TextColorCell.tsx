/**
 * @type: ui
 * name: dataGrid.cell.TextColorCell
 */

import { RouteLink } from '@metafox/framework';
import { styled, Tooltip } from '@mui/material';
import { get } from 'lodash';
import React from 'react';

const StyledBox = styled('span', {
  name: 'StyledBox'
})<{ truncateLines: number; textColor: string }>(
  ({ truncateLines, textColor }) => ({
    ...(truncateLines && {
      wordBreak: 'break-word',
      whiteSpace: 'normal',
      display: '-webkit-box',
      overflow: 'hidden',
      WebkitBoxOrient: 'vertical',
      WebkitLineClamp: `${truncateLines}`
    }),
    ...(textColor && {
      color: `${textColor}`
    })
  })
);

// todo moved this column to base size.
export default function BasicCell({
  row,
  colDef: {
    field,
    target,
    urlField,
    tagName: TagName,
    truncateLines,
    tooltip,
    tooltipDefault
  }
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
            <StyledBox
              textColor={content.color}
              truncateLines={truncateLines}
              sx={sxProps}
            >
              {content.label}
            </StyledBox>
          </RouteLink>
        </Tooltip>
      );
    }
  }

  return (
    <Tooltip title={tooltipText}>
      <StyledBox
        truncateLines={truncateLines}
        sx={sxProps}
        textColor={content.color}
      >
        {content.label}
      </StyledBox>
    </Tooltip>
  );
}
