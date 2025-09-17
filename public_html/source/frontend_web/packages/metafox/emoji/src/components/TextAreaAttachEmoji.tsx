/**
 * @type: ui
 * name: textArea.control.attachEmoji
 *
 */

import React from 'react';
import Base from './AttachEmojiButton';

function AttachEmojiToStatusComposer(props: any) {
  const { onChange } = props;

  const onEmojiClick = (text: string) => {
    onChange(text);
  };

  return <Base size="small" multiple onEmojiClick={onEmojiClick} {...props} />;
}

export default React.memo(AttachEmojiToStatusComposer, () => true);
