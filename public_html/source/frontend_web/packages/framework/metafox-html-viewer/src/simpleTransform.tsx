import { RouteLink, ExternalLink } from '@metafox/framework';
import { createElement } from 'react';
import { convertNodeToElement } from './index';
import { THtmlViewerNode } from './types';
import { isExternalLink } from '@metafox/utils';
import { Typography, TypographyProps } from '@mui/material';
import inlineStyle from './inlineStyle';

const excludeTag = ['video', 'img', 'iframe', 'table'];

const simpleTransform = (node: THtmlViewerNode, index: string): JSX.Element => {
  const { style } = node.attribs || {};

  if ('tag' === node.type && excludeTag.includes(node.name)) {
    return null;
  }

  if ('tag' === node.type) {
    const children = node.children
      .filter(x => !excludeTag.includes(x.name))
      .map((node, index) => convertNodeToElement(node, index, simpleTransform));

    const tagName = node.name?.toLowerCase();

    if (['h1', 'h2', 'h3', 'h4', 'h5', 'h6'].includes(tagName)) {
      return createElement(
        Typography,
        {
          variant: tagName as TypographyProps['variant'],
          ...node.attribs,
          style: inlineStyle(style)
        },
        children
      );
    }

    if ('a' === node.name && node.attribs && node.attribs.href) {
      const isExternalUrl = isExternalLink(node.attribs.href);
      const children = node.children
        .filter(x => !excludeTag.includes(x.name))
        .map((node, index) =>
          convertNodeToElement(node, index, simpleTransform)
        );

      if (isExternalUrl) {
        return createElement(
          ExternalLink,
          {
            to: undefined,
            ...node.attribs,
            className: `${node.attribs?.class} external-link`,
            style: inlineStyle(style)
          },
          children
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
          children
        );
      }
    }
  }
};

export default simpleTransform;
