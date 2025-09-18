import { MentionNode } from '@metafox/comment/components/Mention/MentionNode';
import * as React from 'react';
import Editor from './Lexical/Editor';
import { HashtagNode } from '@lexical/hashtag';
import {
  KEY_ENTER_COMMAND,
  COMMAND_PRIORITY_CRITICAL,
  INSERT_PARAGRAPH_COMMAND,
  PASTE_COMMAND,
  KEY_ESCAPE_COMMAND
} from 'lexical';
import { useLexicalComposerContext } from '@lexical/react/LexicalComposerContext';

function Pasted({ handlePastedFiles }) {
  const [editor] = useLexicalComposerContext();

  React.useLayoutEffect(() => {
    return editor.registerCommand<ClipboardEvent>(
      PASTE_COMMAND,
      event => {
        const files = event.clipboardData?.files;

        if (files?.length > 0) {
          handlePastedFiles(files);

          return true;
        }

        return false;
      },
      COMMAND_PRIORITY_CRITICAL
    );
  }, [editor, handlePastedFiles]);

  return null;
}

function Enter({ handleSubmit, isMobile }) {
  const [editor] = useLexicalComposerContext();

  React.useLayoutEffect(() => {
    return editor.registerCommand(
      KEY_ENTER_COMMAND,
      event => {
        if (isMobile || !handleSubmit) {
          return false;
        }

        if (event !== null) {
          event.preventDefault();

          if (event.shiftKey) {
            return editor.dispatchCommand(INSERT_PARAGRAPH_COMMAND, undefined);
          }
        }

        handleSubmit();

        return true;
      },
      COMMAND_PRIORITY_CRITICAL
    );
  }, [editor, handleSubmit, isMobile]);

  return null;
}

function Esc({ handleCancel }) {
  const [editor] = useLexicalComposerContext();

  React.useLayoutEffect(() => {
    return editor.registerCommand(
      KEY_ESCAPE_COMMAND,
      event => {
        if (!handleCancel) {
          return false;
        }

        if (event !== null) {
          event.preventDefault();
        }

        handleCancel();

        return true;
      },
      COMMAND_PRIORITY_CRITICAL
    );
  }, [editor, handleCancel]);

  return null;
}
export default function CommentEditor(props) {
  const { editorPlugins } = props;

  return (
    <Editor
      {...props}
      initEditorConfig={{ nodes: [MentionNode, HashtagNode] }}
      plugins={[
        () => (
          <Enter
            key={'enter-submit'}
            handleSubmit={props.handleSubmit}
            isMobile={props.isMobile}
          />
        ),
        () => <Esc key={'esc'} handleCancel={props.handleCancel} />,
        () => (
          <Pasted
            key={'pasted-event'}
            handlePastedFiles={props.handlePastedFiles}
          />
        ),
        ...editorPlugins
      ]}
    />
  );
}
