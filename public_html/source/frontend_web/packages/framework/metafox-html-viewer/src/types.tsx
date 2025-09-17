import { ElementType } from 'react';

export interface THtmlViewerNode {
  type: string;
  name: string;
  children?: [THtmlViewerNode];
  next?: THtmlViewerNode;
  prev?: THtmlViewerNode;
  parent?: THtmlViewerNode;
  data?: string;
  attribs: Record<string, any>;
}

export type THtmlViewerTransform = (
  node: THtmlViewerNode,
  index: string
) => React.ReactNode;

export interface HtmlComponentProps {
  html: string;
  decodeEntities?: boolean;
  transform?: THtmlViewerTransform;
  preprocessNodes?: any;
  component?: ElementType;
  disableNl2br?: boolean; // default is "false"
  simpleTransform?: boolean;
}
