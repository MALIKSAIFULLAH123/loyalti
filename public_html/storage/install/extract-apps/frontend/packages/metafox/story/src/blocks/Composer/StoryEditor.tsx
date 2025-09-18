import * as React from 'react';
import Editor from './Lexical/Editor';
import {
  KEY_ENTER_COMMAND,
  COMMAND_PRIORITY_CRITICAL,
  INSERT_PARAGRAPH_COMMAND,
  KEY_ESCAPE_COMMAND
} from 'lexical';
import { useLexicalComposerContext } from '@lexical/react/LexicalComposerContext';

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
export default function StoryEditor(props) {
  const { pluginsProps, initEditorConfig = {} } = props;

  return (
    <Editor
      {...props}
      initEditorConfig={{
        ...initEditorConfig
      }}
      pluginsProps={pluginsProps}
      plugins={[
        () => (
          <Enter
            key={'enter-submit'}
            handleSubmit={props.handleSubmit}
            isMobile={props.isMobile}
          />
        ),
        () => <Esc key={'esc'} handleCancel={props.handleCancel} />
      ]}
    />
  );
}
