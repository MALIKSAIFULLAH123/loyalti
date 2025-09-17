import { Klass, LexicalNode, TextNode } from 'lexical';
import { HashtagNode } from '@lexical/hashtag';
import { AutoLinkNode, LinkNode } from '@lexical/link';
import { ListItemNode, ListNode } from '@lexical/list';
import { MarkNode } from '@lexical/mark';
import { OverflowNode } from '@lexical/overflow';
import { HorizontalRuleNode } from '@lexical/react/LexicalHorizontalRuleNode';
import { HeadingNode, QuoteNode } from '@lexical/rich-text';
import { TableCellNode, TableNode, TableRowNode } from '@lexical/table';
import { ImageNode } from './ImageNode';
import { LayoutContainerNode } from './LayoutContainerNode';
import { LayoutItemNode } from './LayoutItemNode';
import { MentionNode } from './MentionNode';
import { YouTubeNode } from './YouTubeNode';
import { VimeoNode } from './VimeoNode';
import { ExtendedTextNode } from './ExtendedTextNode';
import { IframeNode } from './IframeNode';
import { TableCellNode as TableCellNodeCustom } from './LexicalTableCellNode';

const PlaygroundNodes: Array<Klass<LexicalNode>> = [
  HeadingNode,
  ListNode,
  ListItemNode,
  QuoteNode,
  TableNode,
  TableCellNodeCustom,
  {
    replace: TableCellNode,
    with: (node: TableCellNode) => {
      return new TableCellNodeCustom();
    }
  },
  TableRowNode,
  HashtagNode,
  AutoLinkNode,
  LinkNode,
  OverflowNode,
  ImageNode,
  MentionNode,
  HorizontalRuleNode,
  YouTubeNode,
  VimeoNode,
  IframeNode,
  MarkNode,
  LayoutContainerNode,
  LayoutItemNode,
  ExtendedTextNode,
  {
    replace: TextNode,
    with: (node: TextNode) => {
      return new ExtendedTextNode(node.__text, node.__key);
    }
  }
];

export default PlaygroundNodes;
