/**
 * Copyright (c) Meta Platforms, Inc. and affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 *
 */

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

type VimeoComponentProps = Readonly<{
  className: Readonly<{
    base: string;
    focus: string;
  }>;
  format: ElementFormatType | null;
  nodeKey: NodeKey;
  videoID: string;
}>;

function VimeoComponent({
  className,
  format,
  nodeKey,
  videoID,
  width,
  height,
  h
}: VimeoComponentProps) {
  return (
    <BlockWithAlignableContents
      className={className}
      format={format}
      nodeKey={nodeKey}
    >
      <iframe
        width={width}
        height={height}
        src={`https://player.vimeo.com/video/${videoID}${h ? `?h=${h}` : ''}`}
        frameBorder="0"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
        allowFullScreen
        title="Vimeo video"
      />
    </BlockWithAlignableContents>
  );
}

export type SerializedVimeoNode = Spread<
  {
    videoID: string;
  },
  SerializedDecoratorBlockNode
>;

function convertVimeoElement(domNode: HTMLElement): null | DOMConversionOutput {
  const id = domNode.getAttribute('data-lexical-vimeo');
  const width = domNode.getAttribute('width');
  const height = domNode.getAttribute('height');

  if (id) {
    const node = $createVimeoNode({ id, width, height });

    return { node };
  }

  return null;
}

export class VimeoNode extends DecoratorBlockNode {
  __id: string;
  __width: string;
  __height: string;
  __h: string;

  static getType(): string {
    return 'vimeo';
  }

  static clone(node: VimeoNode): VimeoNode {
    return new VimeoNode(
      {
        id: node.__id,
        width: node.__width,
        height: node.__height,
        h: node.__h
      },
      node.__format,
      node.__key
    );
  }

  static importJSON(serializedNode: SerializedVimeoNode): VimeoNode {
    const node = $createVimeoNode(serializedNode.videoID);
    node.setFormat(serializedNode.format);

    return node;
  }

  exportJSON(): SerializedVimeoNode {
    return {
      ...super.exportJSON(),
      type: 'vimeo',
      version: 1,
      videoID: this.__id,
      h: this.__h
    };
  }

  constructor(data, format?: ElementFormatType, key?: NodeKey) {
    super(format, key);
    this.__id = data?.id;
    this.__width = data?.width;
    this.__height = data?.height;
    this.__h = data?.h;
  }

  exportDOM(): DOMExportOutput {
    const element = document.createElement('iframe');
    element.setAttribute('data-lexical-vimeo', this.__id);
    element.setAttribute('width', this.__width);
    element.setAttribute('height', this.__height);
    element.setAttribute(
      'src',
      `https://player.vimeo.com/video/${this.__id}${
        this.__h ? `?h=${this.__h}` : ''
      }`
    );
    element.setAttribute('frameborder', '0');
    element.setAttribute(
      'allow',
      'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture'
    );
    element.setAttribute('allowfullscreen', 'true');
    element.setAttribute('title', 'Vimeo video');

    return { element };
  }

  static importDOM(): DOMConversionMap | null {
    return {
      iframe: (domNode: HTMLElement) => {
        if (!domNode.hasAttribute('data-lexical-vimeo')) {
          return null;
        }

        return {
          conversion: convertVimeoElement,
          priority: 1
        };
      }
    };
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
    return `https://player.vimeo.com/video/${this.__id}${
      this.__h ? `?h=${this.__h}` : ''
    }`;
  }

  decorate(_editor: LexicalEditor, config: EditorConfig): JSX.Element {
    const embedBlockTheme = config.theme.embedBlock || {};
    const className = {
      base: embedBlockTheme.base || '',
      focus: embedBlockTheme.focus || ''
    };

    return (
      <VimeoComponent
        className={className}
        format={this.__format}
        nodeKey={this.getKey()}
        videoID={this.__id}
        h={this.__h}
        width={this.__width}
        height={this.__height}
      />
    );
  }
}

export function $createVimeoNode(data): VimeoNode {
  return new VimeoNode(data);
}

export function $isVimeoNode(
  node: VimeoNode | LexicalNode | null | undefined
): node is VimeoNode {
  return node instanceof VimeoNode;
}
