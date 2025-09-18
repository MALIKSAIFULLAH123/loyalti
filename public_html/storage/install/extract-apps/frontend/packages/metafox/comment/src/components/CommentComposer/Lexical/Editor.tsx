import { LexicalComposer } from '@lexical/react/LexicalComposer';
import { PlainTextPlugin } from '@lexical/react/LexicalPlainTextPlugin';
import { ContentEditable } from '@lexical/react/LexicalContentEditable';
import * as React from 'react';
import LexicalErrorBoundary from '@lexical/react/LexicalErrorBoundary';
import { OnChangePlugin } from '@lexical/react/LexicalOnChangePlugin';
import { $generateHtmlFromNodes } from '@lexical/html';
import { EditorState, LexicalEditor } from 'lexical';
import { useLexicalComposerContext } from '@lexical/react/LexicalComposerContext';
import { Box, styled } from '@mui/material';
import updateContent from './utils/updateContent';
import { useEditorFocus, isEmptyContentValue } from '@metafox/lexical';

const ExampleTheme = {
  ltr: 'ltr',
  rtl: 'rtl',
  placeholder: 'editor-placeholder',
  paragraph: 'editor-paragraph',
  hashtag: 'editor-hashtag'
};
const name = 'EditorComment';
const EditorPlaceHolder = styled(Box, { name, slot: 'PlaceHolder' })(
  ({ theme }) => ({
    position: 'absolute',
    color: theme.palette.text.hint,
    overflow: 'hidden',
    top: 0,
    left: 0,
    bottom: 0,
    display: 'flex',
    alignItems: 'center',
    userSelect: 'none',
    pointerEvents: 'none'
  })
);
const EditorContainer = styled(Box, { name, slot: 'EditorContainer' })(
  ({ theme }) => ({
    position: 'relative',
    width: '100%',
    border:
      theme.palette.mode === 'light'
        ? theme.mixins.border('secondary')
        : 'solid 1px rgba(73, 73, 73, 0.2)',
    backgroundColor: theme.palette.action.hover,
    minHeight: theme.spacing(4),
    borderRadius: theme.spacing(3),
    display: 'flex',
    flexFlow: 'wrap',
    transition: 'all 200ms ease 0s',
    overflow: 'hidden',
    [theme.breakpoints.down('md')]: {
      minHeight: theme.spacing(5)
    },
    padding: '4px 12px',
    '& .editor-input': {
      width: '100%',
      outline: 'none',
      '& p': {
        margin: 0,
        padding: 0
      },
      '& .editor-hashtag, & .editor-mention': {
        color: theme.palette.primary.main
      }
    }
  })
);

const EditorCompose = styled(Box, { name, slot: 'EditorCompose' })(
  ({ theme }) => ({
    position: 'relative',
    width: '100%',
    display: 'flex',
    alignItems: 'center'
  })
);

function useOnChange(
  onChange: (text: string) => void,
  setCanSubmit: (canSubmit: boolean) => void
) {
  return React.useCallback(
    (editorState: EditorState, _editor: LexicalEditor) => {
      editorState.read(() => {
        const htmlString = $generateHtmlFromNodes(_editor, null);
        // onChange($rootTextContent());
        // const canSubmit = !$isRootTextContentEmpty(_editor.isComposing(), true);
        const canSubmit = !isEmptyContentValue(_editor.isComposing(), true);

        if (canSubmit) {
          onChange(htmlString);
        } else {
          onChange('');
        }

        setCanSubmit(canSubmit);
      });
    },
    [setCanSubmit, onChange]
  );
}

export default function Editor(props) {
  const { editorRef } = props;

  const initEditorConfig = {
    // The editor theme
    theme: ExampleTheme,
    // Handling of errors during update
    onError(error) {
      throw error;
    },
    // Any custom nodes go here
    editorState: editor => {
      updateContent(editor, props?.value || '');
      editorRef.current.editor = editor;
    },
    ...(props?.initEditorConfig || {})
  };

  return (
    <LexicalComposer initialConfig={initEditorConfig}>
      <EditorElement {...props} />
    </LexicalComposer>
  );
}

function EditorElement(props) {
  const { onChange, value, plugins, placeholder, sx, onFocus, onBlur } = props;
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const [canSubmit, setCanSubmit] = React.useState(false);
  const focusState = useEditorFocus();
  const mounted = React.useRef(false);
  const [editor] = useLexicalComposerContext();
  const ref = React.useRef();

  React.useEffect(() => {
    setImmediate(() => {
      mounted.current = true;
    });
  }, []);

  const editorChange = value => {
    onChange(value);
  };

  const handleChange = useOnChange(editorChange, setCanSubmit);

  React.useEffect(() => {
    if (focusState) {
      onFocus();
    } else {
      onBlur();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [focusState]);

  React.useEffect(() => {
    if (!mounted.current) return;

    editor.update(() => {
      // when isFocus only accept data from editor, dont updateContent
      if (focusState) return;

      const htmlString = $generateHtmlFromNodes(editor, null);

      if (htmlString === value) return;

      updateContent(editor, value);
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [value]);

  const handleFocus = () => {
    editor.focus();
  };

  return (
    <EditorContainer ref={ref} sx={sx} onClick={handleFocus}>
      <EditorCompose>
        <PlainTextPlugin
          contentEditable={
            <ContentEditable className="editor-input" spellCheck={false} />
          }
          placeholder={<Placeholder placeholder={placeholder} />}
          ErrorBoundary={LexicalErrorBoundary}
        />
        <OnChangePlugin onChange={handleChange} />
        {plugins.map((PluginComponent, key) => (
          <PluginComponent key={`plugin${key}`} refContainer={ref} />
        ))}
      </EditorCompose>
    </EditorContainer>
  );
}

function Placeholder({ placeholder }) {
  return <EditorPlaceHolder>{placeholder}</EditorPlaceHolder>;
}
