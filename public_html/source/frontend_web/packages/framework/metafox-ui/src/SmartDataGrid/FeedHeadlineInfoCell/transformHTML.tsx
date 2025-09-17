import { createElement } from 'react';
import { Typography, TypographyProps } from '@mui/material';
import { THtmlViewerNode } from '@metafox/html-viewer/types';
import { convertNodeToElement } from '@metafox/html-viewer';
import inlineStyle from '@metafox/html-viewer/inlineStyle';

const excludeTag = ['video', 'img', 'iframe', 'table'];

export const convertToHref = href => {
  return href.startsWith('http') ? href : `${process.env.MFOX_SITE_URL}${href}`;
};

const transformHTML = (node: THtmlViewerNode, index: string): JSX.Element => {
  const { style } = node.attribs || {};

  if ('tag' === node.type && excludeTag.includes(node.name)) {
    return null;
  }

  if ('tag' === node.type) {
    const children = node.children
      .filter(x => !excludeTag.includes(x.name))
      .map((node, index) => convertNodeToElement(node, index, transformHTML));

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
      const children = node.children
        .filter(x => !excludeTag.includes(x.name))
        .map((node, index) => convertNodeToElement(node, index, transformHTML));

      return createElement(
        'div',
        {
          ...node.attribs,
          key: `n${index}`,
          className: node.attribs?.class,
          style: inlineStyle(style)
        },
        children
      );
    }
  }
};

export default transformHTML;
