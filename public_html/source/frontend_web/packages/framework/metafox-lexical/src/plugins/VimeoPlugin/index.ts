import { useLexicalComposerContext } from '@lexical/react/LexicalComposerContext';
import { $insertNodeToNearestRoot } from '@lexical/utils';
import {
  COMMAND_PRIORITY_EDITOR,
  createCommand,
  LexicalCommand
} from 'lexical';
import { useEffect } from 'react';
import { $createVimeoNode, VimeoNode } from '@metafox/lexical/nodes/VimeoNode';

export type DataVimeoCommandType = {
  url: string;
  id: string;
  h?: string;
  width?: string;
  height?: string;
};

export const INSERT_VIMEO_COMMAND: LexicalCommand<DataVimeoCommandType> =
  createCommand('INSERT_VIMEO_COMMAND');

export default function VimeoPlugin(): JSX.Element | null {
  const [editor] = useLexicalComposerContext();

  useEffect(() => {
    if (!editor.hasNodes([VimeoNode])) {
      throw new Error('VimeoPlugin: VimeoNode not registered on editor');
    }

    return editor.registerCommand<DataVimeoCommandType>(
      INSERT_VIMEO_COMMAND,
      payload => {
        const node = $createVimeoNode(payload);
        $insertNodeToNearestRoot(node);

        return true;
      },
      COMMAND_PRIORITY_EDITOR
    );
  }, [editor]);

  return null;
}
