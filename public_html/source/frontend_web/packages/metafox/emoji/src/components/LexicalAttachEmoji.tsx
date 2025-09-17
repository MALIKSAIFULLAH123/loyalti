/**
 * @type: ui
 * name: lexical.control.attachEmoji
 * chunkName: comment
 */

import React from 'react';
import Base from './AttachEmojiButton';
import { $insertNodes, $createTextNode } from 'lexical';

function AttachEmojiToStatusComposer(props: any) {
  const onEmojiClick = (emoji: string) => {
    const { editor } = props.editorRef.current;

    editor.update(() => {
      $insertNodes([$createTextNode(emoji)]);
    });
  };

  return <Base size="small" multiple onEmojiClick={onEmojiClick} {...props} />;
}

export default React.memo(AttachEmojiToStatusComposer, () => true);
