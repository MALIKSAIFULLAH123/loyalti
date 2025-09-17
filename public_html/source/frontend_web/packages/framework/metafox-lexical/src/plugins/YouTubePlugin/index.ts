import { useLexicalComposerContext } from '@lexical/react/LexicalComposerContext';
import { $insertNodeToNearestRoot } from '@lexical/utils';
import {
  COMMAND_PRIORITY_EDITOR,
  createCommand,
  LexicalCommand
} from 'lexical';
import { useEffect } from 'react';
import { $createYouTubeNode, YouTubeNode } from '@metafox/lexical/nodes/YouTubeNode';

export type DataYouTubeCommandType = {
  url: string;
  id: string;
  width?: string;
  height?: string;
};

export const INSERT_YOUTUBE_COMMAND: LexicalCommand<DataYouTubeCommandType> =
  createCommand('INSERT_YOUTUBE_COMMAND');

export default function YouTubePlugin(): JSX.Element | null {
  const [editor] = useLexicalComposerContext();

  useEffect(() => {
    if (!editor.hasNodes([YouTubeNode])) {
      throw new Error('YouTubePlugin: YouTubeNode not registered on editor');
    }

    return editor.registerCommand<DataYouTubeCommandType>(
      INSERT_YOUTUBE_COMMAND,
      payload => {
        const youTubeNode = $createYouTubeNode(payload);
        $insertNodeToNearestRoot(youTubeNode);

        return true;
      },
      COMMAND_PRIORITY_EDITOR
    );
  }, [editor]);

  return null;
}
