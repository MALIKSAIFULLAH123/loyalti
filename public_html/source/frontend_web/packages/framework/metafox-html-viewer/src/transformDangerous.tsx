import { RouteLink, ExternalLink } from '@metafox/framework';
import React, { createElement } from 'react';
import { convertNodeToElement } from './index';
import { THtmlViewerNode } from './types';
import { isExternalLink } from '@metafox/utils';
import { Typography, TypographyProps, Box } from '@mui/material';
import inlineStyle from './inlineStyle';

const transform = (node: THtmlViewerNode, index: string): JSX.Element => {
  const { style } = node.attribs || {};

  if ('tag' === node.type) {
    const children = node.children.map((node, index) =>
      convertNodeToElement(node, index, transform)
    );

    const tagName = node.name?.toLowerCase();

    if (['h1', 'h2', 'h3', 'h4', 'h5', 'h6'].includes(tagName)) {
      return createElement(
        Typography,
        {
          variant: tagName as TypographyProps['variant'],
          ...node.attribs,
          style: inlineStyle(style),
          key: `k${index}`
        },
        children
      );
    }
  }

  if (node.type === 'script') {
    return (
      <script {...(node?.attribs || {})} key={index} src={node?.attribs?.src}>
        {node?.children[0]?.data}
      </script>
    );
  }

  if (node.type === 'text' && !node.parent) {
    return (
      <Box sx={{ whiteSpace: 'pre-wrap' }} component={'span'} key={`k${index}`}>
        {node.data}
      </Box>
    );
  }

  if (
    'tag' === node.type &&
    'a' === node.name &&
    node.attribs &&
    node.attribs.href
  ) {
    const isExternalUrl = isExternalLink(node.attribs.href);

    if (isExternalUrl) {
      return createElement(
        ExternalLink,
        {
          to: undefined,
          ...node.attribs,
          className: `${node.attribs?.class} external-link`,
          style: inlineStyle(style),
          key: `k${index}`
        },
        node.children.map((node, index) =>
          convertNodeToElement(node, index, transform)
        )
      );
    }

    if (node.attribs.id) {
      const id = node.attribs.id;
      const href = node.attribs.href;
      const module = node.attribs.type;

      return createElement(
        RouteLink,
        {
          ...node.attribs,
          target: node.attribs.target ? node.attribs.target : undefined,
          to: href || `/user/${id}`,
          key: `n${index}`,
          className: 'profileLink',
          hoverCard: `/${module || 'user'}/${id}`,
          style: inlineStyle(style)
        },
        node.children.map((node, index) =>
          convertNodeToElement(node, index, transform)
        )
      );
    }
  }
};

export default transform;
