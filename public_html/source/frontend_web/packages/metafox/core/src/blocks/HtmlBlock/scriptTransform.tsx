import React from 'react';
import { Helmet } from 'react-helmet-async';

const scriptTransform = (node, index: string): JSX.Element => {
  if (node.type === 'script') {
    const data = node?.attribs?.src ? { src: node?.attribs?.src } : {};

    return (
      <Helmet>
        <script {...(node?.attribs || {})} key={index} {...data}>
          {node?.children[0]?.data}
        </script>
      </Helmet>
    );
  }

  return null;
};

export default scriptTransform;
