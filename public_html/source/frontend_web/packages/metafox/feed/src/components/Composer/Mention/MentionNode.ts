import {
  $applyNodeReplacement,
  type DOMConversionMap,
  type DOMConversionOutput,
  type DOMExportOutput,
  type EditorConfig,
  type LexicalNode,
  type NodeKey,
  type SerializedTextNode,
  type Spread,
  TextNode
} from 'lexical';

export type SerializedMentionNode = Spread<
  {
    mentionName: string;
  },
  SerializedTextNode
>;

function $convertMentionElement(
  domNode: HTMLElement
): DOMConversionOutput | null {
  const name = domNode.textContent;
  const link = domNode.getAttribute('href');

  if (name !== null) {
    const node = $createMentionNode({ name, link });

    return {
      node
    };
  }

  return null;
}

export class MentionNode extends TextNode {
  __data: Record<string, any>;

  static getType(): string {
    return 'mention';
  }

  static clone(node: MentionNode): MentionNode {
    return new MentionNode(node.__data, node.__text, node.__key);
  }
  static importJSON(serializedNode: SerializedMentionNode): MentionNode {
    const node = $createMentionNode(serializedNode.mentionName);
    node.setTextContent(serializedNode.text);
    node.setFormat(serializedNode.format);
    node.setDetail(serializedNode.detail);
    node.setMode(serializedNode.mode);
    node.setStyle(serializedNode.style);

    return node;
  }

  constructor(mentionObj: Record<string, any>, text?: string, key?: NodeKey) {
    const { name } = mentionObj || {};
    super(text ?? name, key);
    this.__data = mentionObj;
  }

  exportJSON(): SerializedMentionNode {
    return {
      ...super.exportJSON(),
      data: this.__data,
      type: 'mention',
      version: 1
    };
  }

  createDOM(config: EditorConfig): HTMLElement {
    const dom = super.createDOM(config);
    dom.className = 'editor-mention';

    return dom;
  }

  exportDOM(): DOMExportOutput {
    const element = document.createElement('a');
    element.textContent = this.__text;
    element.href = this.__data.link;
    element.setAttribute('data-lexical-mention', 'true');

    return { element };
  }

  static importDOM(): DOMConversionMap | null {
    return {
      a: (domNode: HTMLElement) => {
        if (!domNode.hasAttribute('data-lexical-mention')) {
          return null;
        }

        return {
          conversion: $convertMentionElement,
          priority: 1
        };
      }
    };
  }

  isTextEntity(): true {
    return true;
  }

  canInsertTextBefore(): boolean {
    return false;
  }

  canInsertTextAfter(): boolean {
    return false;
  }
}

export function $createMentionNode(mentionObj): MentionNode {
  const mentionNode = new MentionNode(mentionObj);
  mentionNode.setMode('segmented').toggleDirectionless();

  return $applyNodeReplacement(mentionNode);
}

export function $isMentionNode(
  node: LexicalNode | null | undefined
): node is MentionNode {
  return node instanceof MentionNode;
}
