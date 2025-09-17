import type {
  DOMConversionMap,
  DOMConversionOutput,
  DOMExportOutput,
  EditorConfig,
  ElementFormatType,
  LexicalEditor,
  LexicalNode,
  NodeKey,
  Spread
} from 'lexical';
import { BlockWithAlignableContents } from '@lexical/react/LexicalBlockWithAlignableContents';
import {
  DecoratorBlockNode,
  SerializedDecoratorBlockNode
} from '@lexical/react/LexicalDecoratorBlockNode';
import * as React from 'react';

type IframeComponentProps = Readonly<{
  className: Readonly<{
    base: string;
    focus: string;
  }>;
  format: ElementFormatType | null;
  nodeKey: NodeKey;
  url: string;
  width: string;
  height: string;
}>;

function IframeComponent(props: IframeComponentProps) {
  const { className, format, nodeKey, url, width, height } = props;

  return (
    <BlockWithAlignableContents
      className={className}
      format={format}
      nodeKey={nodeKey}
    >
      <iframe
        src={url}
        width={width}
        height={height}
        frameBorder="0"
        scrolling="no"
        allowFullScreen
        title={'iframe embed'}
      />
    </BlockWithAlignableContents>
  );
}

export type SerializedIframeNode = Spread<
  {
    url: string;
  },
  SerializedDecoratorBlockNode
>;

function convertIframeElement(
  domNode: HTMLElement
): null | DOMConversionOutput {
  const url = domNode.getAttribute('data-lexical-iframe');
  const width = domNode.getAttribute('width');
  const height = domNode.getAttribute('height');
  const parent = domNode.parentElement;
  let format = '';

  if (parent && parent.tagName === 'DIV') {
    const textAlign = parent.style.textAlign;

    if (textAlign) format = textAlign;
  }

  if (url) {
    const node = $createIframeNode({ url, width, height, format });

    return { node };
  }

  return null;
}
type NodeType = {
  src: string;
  width: string;
  height: string;
};
export class IframeNode extends DecoratorBlockNode {
  __url: string;
  __width: string;
  __height: string;

  static getType(): string {
    return 'iframe';
  }

  static clone(node: IframeNode): IframeNode {
    return new IframeNode(
      { url: node.__url, width: node.__width, height: node.__height },
      node.__format,
      node.__key
    );
  }

  static importJSON(serializedNode: SerializedIframeNode): IframeNode {
    const node = $createIframeNode(serializedNode.url);
    node.setFormat(serializedNode.format);

    return node;
  }

  exportJSON(): SerializedIframeNode {
    return {
      ...super.exportJSON(),
      type: 'iframe',
      version: 1,
      url: this.__url
    };
  }

  constructor(data: NodeType, format?: ElementFormatType, key?: NodeKey) {
    super(format, key);
    this.__url = data?.url;
    this.__width = data?.width;
    this.__height = data?.height;
    this.__format = data?.format ?? format;
  }

  exportDOM(): DOMExportOutput {
    const element = document.createElement('div');
    element.style.textAlign = this.__format;

    const iframe = document.createElement('iframe');
    iframe.setAttribute('data-lexical-iframe', this.__url);
    iframe.setAttribute('width', this.__width);
    iframe.setAttribute('height', this.__height);
    iframe.setAttribute('src', this.__url);
    iframe.setAttribute('frameborder', '0');
    iframe.setAttribute(
      'allow',
      'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture'
    );
    iframe.setAttribute('allowfullscreen', 'true');
    iframe.setAttribute('title', '');
    element.appendChild(iframe);

    return { element };
  }

  static importDOM(): DOMConversionMap | null {
    return {
      iframe: (domNode: HTMLElement) => {
        if (!domNode.hasAttribute('data-lexical-iframe')) {
          return null;
        }

        return {
          conversion: convertIframeElement,
          priority: 1
        };
      }
    };
  }

  updateDOM(): false {
    return false;
  }

  getId(): string {
    return this.__url;
  }

  getTextContent(
    _includeInert?: boolean | undefined,
    _includeDirectionless?: false | undefined
  ): string {
    return this.__url;
  }

  decorate(_editor: LexicalEditor, config: EditorConfig): JSX.Element {
    const embedBlockTheme = config.theme.embedBlock || {};
    const className = {
      base: embedBlockTheme.base || '',
      focus: embedBlockTheme.focus || ''
    };

    return (
      <IframeComponent
        className={className}
        format={this.__format}
        nodeKey={this.getKey()}
        url={this.__url}
        width={this.__width}
        height={this.__height}
      />
    );
  }
}

export function $createIframeNode(data): IframeNode {
  return new IframeNode(data);
}

export function $isIframeNode(
  node: IframeNode | LexicalNode | null | undefined
): node is IframeNode {
  return node instanceof IframeNode;
}
