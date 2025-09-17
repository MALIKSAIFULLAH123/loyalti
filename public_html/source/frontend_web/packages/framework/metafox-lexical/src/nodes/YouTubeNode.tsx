/**
 * Copyright (c) Meta Platforms, Inc. and affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 *
 */

import type {
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

type YouTubeComponentProps = Readonly<{
  className: Readonly<{
    base: string;
    focus: string;
  }>;
  format: ElementFormatType | null;
  nodeKey: NodeKey;
  videoID: string;
}>;

function YouTubeComponent(props: YouTubeComponentProps) {
  const { className, format, nodeKey, videoID, width, height } = props;

  return (
    <BlockWithAlignableContents
      className={className}
      format={format}
      nodeKey={nodeKey}
    >
      <iframe
        width={width}
        height={height}
        src={`https://www.youtube-nocookie.com/embed/${videoID}`}
        frameBorder="0"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
        allowFullScreen
        title="YouTube video"
      />
    </BlockWithAlignableContents>
  );
}

export type SerializedYouTubeNode = Spread<
  {
    videoID: string;
  },
  SerializedDecoratorBlockNode
>;

function convertYoutubeElement(
  domNode: HTMLElement
): null | DOMConversionOutput {
  const id = domNode.getAttribute('data-lexical-youtube');
  const width = domNode.getAttribute('width');
  const height = domNode.getAttribute('height');
  const parent = domNode.parentElement;
  let align = '';

  if (parent && parent.tagName === 'DIV') {
    const textAlign = parent.style.textAlign;

    if (textAlign) align = textAlign;
  }

  if (id) {
    const node = $createYouTubeNode({ id, width, height, format: align });

    return { node };
  }

  return null;
}

export class YouTubeNode extends DecoratorBlockNode {
  __id: string;
  __width: string;
  __height: string;

  static getType(): string {
    return 'youtube';
  }

  static clone(node: YouTubeNode): YouTubeNode {
    return new YouTubeNode(
      { id: node.__id, width: node.__width, height: node.__height },
      node.__format,
      node.__key
    );
  }

  constructor(data, format?: ElementFormatType, key?: NodeKey) {
    super(format, key);
    this.__id = data?.id;
    this.__width = data?.width;
    this.__height = data?.height;
    this.__format = data?.format ?? format;
  }

  static importDOM() {
    return {
      iframe: node => {
        const videoId = node.getAttribute('data-lexical-youtube');

        if (videoId) {
          return {
            conversion: convertYoutubeElement,
            priority: 1
          };
        }

        return null;
      }
    };
  }

  exportDOM(): DOMExportOutput {
    const element = document.createElement('div');
    element.style.textAlign = this.__format;
    const iframe = document.createElement('iframe');
    iframe.setAttribute('data-lexical-youtube', this.__id);
    iframe.setAttribute('width', this.__width);
    iframe.setAttribute('height', this.__height);
    iframe.setAttribute(
      'src',
      `https://www.youtube-nocookie.com/embed/${this.__id}`
    );
    iframe.setAttribute('frameborder', '0');
    iframe.setAttribute(
      'allow',
      'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture'
    );
    iframe.setAttribute('allowfullscreen', 'true');
    iframe.setAttribute('title', 'YouTube video');
    element.appendChild(iframe);

    return { element };
  }

  updateDOM(): false {
    return false;
  }

  getId(): string {
    return this.__id;
  }

  getTextContent(
    _includeInert?: boolean | undefined,
    _includeDirectionless?: false | undefined
  ): string {
    return `https://www.youtube.com/watch?v=${this.__id}`;
  }

  decorate(_editor: LexicalEditor, config: EditorConfig): JSX.Element {
    const embedBlockTheme = config.theme.embedBlock || {};
    const className = {
      base: embedBlockTheme.base || '',
      focus: embedBlockTheme.focus || ''
    };

    return (
      <YouTubeComponent
        className={className}
        format={this.__format}
        nodeKey={this.getKey()}
        videoID={this.__id}
        width={this.__width}
        height={this.__height}
      />
    );
  }
}

export function $createYouTubeNode(data): YouTubeNode {
  return new YouTubeNode(data);
}

export function $isYouTubeNode(
  node: YouTubeNode | LexicalNode | null | undefined
): node is YouTubeNode {
  return node instanceof YouTubeNode;
}
