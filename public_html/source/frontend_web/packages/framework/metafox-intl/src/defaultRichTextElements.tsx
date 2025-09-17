import { RouteLink } from '@metafox/framework';
import { Hint } from '@metafox/ui';
import { parseIntlString } from '@metafox/utils';
import { Box, Divider, Link, Typography } from '@mui/material';
import React from 'react';

const toTypography = chunks => {
  const { props, child } = parseIntlString(chunks[0]);

  return <Typography {...props}>{child}</Typography>;
};

const formatLink = chunks => {
  const { props, child } = parseIntlString(chunks[0]);

  // internal url
  if (props.target && props.url?.startsWith('/')) {
    return <RouteLink to={props.url}>{child}</RouteLink>;
  }

  return (
    <Link
      color="primary"
      underline="hover"
      rel="noopener noreferrer"
      target="_blank"
      href={props.url}
    >
      {child}
    </Link>
  );
};

const formatItalic = (chunks: any) => <i>{chunks}</i>;

const formatSpan = (chunks: any) => <span>{chunks}</span>;

const formatBold = (chunks: any) => <b>{chunks}</b>;

const formatDivider = () => <Divider />;

const formatBreak = () => <br />;

const formatUl = chunks => <ul>{chunks}</ul>;

const formatLi = chunks => <li>{chunks}</li>;

const formatDiv = chunks => <Box>{chunks}</Box>;

const formatHtml = chunks => chunks;

const formatHint = chunks => <Hint>{chunks}</Hint>;

const defaultRichTextElements = {
  bold: formatBold,
  b: formatBold,
  user: formatBold,
  link: formatLink,
  a: formatLink,
  p: toTypography,
  i: formatItalic,
  span: formatSpan,
  br: formatBreak,
  hr: formatDivider,
  li: formatLi,
  ul: formatUl,
  div: formatDiv,
  html: formatHtml,
  hint: formatHint
};

export default defaultRichTextElements;
