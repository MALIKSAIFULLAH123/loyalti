import React from 'react';
import ReactHtmlParser from 'react-html-parser';
import default_transform from './transform';
import simple_transform from './simpleTransform';
import { HtmlComponentProps } from './types';

function nl2br(text: string): string {
  // issue migrate from v4. </br> make bad layout
  return `${text}`.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<p></p>');
}

const HtmlComponent = ({
  html,
  decodeEntities = true,
  transform: transformProp,
  component: Wrap = React.Fragment,
  disableNl2br,
  preprocessNodes,
  simpleTransform = false
}: HtmlComponentProps) => {
  const transform =
    transformProp || (simpleTransform ? simple_transform : default_transform);

  return (
    <Wrap>
      {html
        ? ReactHtmlParser(disableNl2br ? html : nl2br(html), {
            decodeEntities,
            transform,
            preprocessNodes
          })
        : null}
    </Wrap>
  );
};

export default HtmlComponent;
