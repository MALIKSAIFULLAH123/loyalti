import * as React from 'react';
import Editor from './Lexical/Editor';
import {
  KEY_ENTER_COMMAND,
  COMMAND_PRIORITY_CRITICAL,
  INSERT_PARAGRAPH_COMMAND,
  PASTE_COMMAND,
  KEY_ESCAPE_COMMAND
} from 'lexical';
import { useLexicalComposerContext } from '@lexical/react/LexicalComposerContext';
import { MentionNode } from './Mention/MentionNode';
import { isFunction } from 'lodash';

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

function KeyDown({ onKeydown, onBeforeKeyDown }) {
  const [editor] = useLexicalComposerContext();

  React.useLayoutEffect(() => {
    const _onKeyDown = evt => {
      if (isFunction(onKeydown)) {
        onKeydown(evt);
      }
    };

    return editor.registerRootListener(
      (
        rootElement: null | HTMLElement,
        prevRootElement: null | HTMLElement
      ) => {
        if (isFunction(onBeforeKeyDown)) {
          onBeforeKeyDown();
        }

        if (prevRootElement !== null) {
          prevRootElement.removeEventListener('keydown', _onKeyDown);
        }

        if (rootElement !== null) {
          rootElement.addEventListener('keydown', _onKeyDown);
        }
      }
    );
  }, [editor, onKeydown, onBeforeKeyDown]);

  return null;
}

export default function ChatEditor(props) {
  const { editorPlugins } = props;

  return (
    <Editor
      {...props}
      initEditorConfig={{ nodes: [MentionNode] }}
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
        () => (
          <KeyDown
            key={'key-down'}
            onBeforeKeyDown={props?.onBeforeKeyDown}
            onKeydown={props?.onKeyDown}
          />
        ),
        ...editorPlugins
      ]}
    />
  );
}
