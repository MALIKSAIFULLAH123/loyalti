import ExampleTheme from './themes/ExampleTheme';
import { LexicalComposer } from '@lexical/react/LexicalComposer';
import { RichTextPlugin } from '@lexical/react/LexicalRichTextPlugin';
import { ContentEditable } from '@lexical/react/LexicalContentEditable';
import { HistoryPlugin } from '@lexical/react/LexicalHistoryPlugin';
import { AutoFocusPlugin } from '@lexical/react/LexicalAutoFocusPlugin';
import LexicalErrorBoundary from '@lexical/react/LexicalErrorBoundary';
import ToolbarPlugin from '@metafox/lexical/plugins/ToolbarPlugin';
import { LinkPlugin } from '@lexical/react/LexicalLinkPlugin';
import { ListPlugin } from '@lexical/react/LexicalListPlugin';
import { CheckListPlugin } from '@lexical/react/LexicalCheckListPlugin';
import AutoLinkPlugin from '@metafox/lexical/plugins/AutoLinkPlugin';
import React from 'react';
import './base.css';
import { styled, Box } from '@mui/material';
import { OnChangePlugin } from '@lexical/react/LexicalOnChangePlugin';
import { createDOMRange, createRectsFromDOMRange } from '@lexical/selection';
import {
  EditorState,
  $getSelection,
  $isRangeSelection,
  // $insertNodes,
  $getRoot,
  COMMAND_PRIORITY_LOW,
  CLICK_COMMAND,
  $createParagraphNode,
  LexicalEditor,
  BLUR_COMMAND
} from 'lexical';
import FloatingLinkEditorPlugin from '@metafox/lexical/plugins/FloatingLinkEditorPlugin';
import FloatingTextFormatToolbarPlugin from '@metafox/lexical/plugins/FloatingTextFormatToolbarPlugin';
import { useLexicalComposerContext } from '@lexical/react/LexicalComposerContext';
import { $generateHtmlFromNodes, $generateNodesFromDOM } from '@lexical/html';
import { useEditorFocus, isEmptyContentValue } from '@metafox/lexical';
import ViewerWrapper from './Viewer';
import { TablePlugin } from '@lexical/react/LexicalTablePlugin';
import PlaygroundNodes from '@metafox/lexical/nodes/PlaygroundNodes';
import TableCellActionMenuPlugin from '@metafox/lexical/plugins/TableActionMenuPlugin';
import {
  ImagesPlugin,
  YouTubePlugin,
  VimeoPlugin,
  AutoEmbedPlugin,
  IframePlugin,
  TableCellResizer
} from '@metafox/lexical/plugins';

const name = 'RichtextEditor';
const PlaceholderRoot = styled(Box, {
  name,
  slot: 'PlaceholderRoot'
})(({ theme }) => ({
  position: 'absolute',
  color: theme.palette.text.hint,
  overflow: 'hidden',
  textOverflow: 'ellipsis',
  top: '8px',
  left: '0',
  userSelect: 'none',
  display: 'inlineBlock',
  pointerEvents: 'none',
  ...theme.typography.body2
}));

const EditorContainer = styled(Box, {
  name,
  slot: 'EditorContainer'
})(({ theme }) => ({
  position: 'relative',
  '& .ltr': {
    textAlign: 'left'
  },
  '& .rtl': {
    textAlign: 'right'
  },
  'svg path': {
    fill: theme.palette.text.primary
  }
}));

const EditorInner = styled(Box, {
  name,
  slot: 'EditorInner'
})(({ theme }) => ({
  position: 'relative',
  '& .editor-input': {
    caretColor: theme.palette.text.primary,
    minHeight: '150px',
    resize: 'none',
    position: 'relative',
    tabSize: 1,
    outline: 0,
    padding: `${theme.spacing(1)} 0`
  }
}));

const updateContent = (editor, value) => {
  const root = $getRoot();
  root.clear();

  if (!value) {
    const paragraph = $createParagraphNode();
    root.append(paragraph);

    return;
  }

  const parser = new DOMParser();
  const dom = parser.parseFromString(
    (value || '').replace(/\n/g, '<br>'),
    'text/html'
  );
  const nodes = $generateNodesFromDOM(editor, dom);

  nodes.forEach(n => {
    try {
      root.append(n);
    } catch (error) {
      const paragraph = $createParagraphNode();

      if (n.getType() === 'linebreak') {
        // hot fix issue double linebreak
        root.append(paragraph);
      } else {
        root.append(paragraph.append(n));
      }
    }
  });
};

function Placeholder({ text }) {
  if (!text) return null;

  return <PlaceholderRoot>{text}</PlaceholderRoot>;
}

export default function Editor(props) {
  const initEditorConfig = {
    // The editor theme
    theme: ExampleTheme,
    // Handling of errors during update
    onError(error) {
      throw error;
    },
    // Any custom nodes go here
    nodes: [...PlaygroundNodes],
    editorState: editor => {
      updateContent(editor, props?.value || '');
    },
    ...(props?.initEditorConfig || {})
  };

  return (
    <LexicalComposer initialConfig={initEditorConfig}>
      <ViewerWrapper mt={0}>
        <EditorElement {...props} />
      </ViewerWrapper>
    </LexicalComposer>
  );
}

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
type EditorElementProps = {
  placeholder?: string;
  onChange: () => void;
  onBlur: () => void;
  onFocus: () => void;
  onClick: () => void;
  autoFocus?: boolean;
  value: string;
  setValue: () => void;
  disabled?: boolean;
};

const EditorElement = (props: EditorElementProps) => {
  const {
    placeholder,
    onChange,
    onBlur,
    onFocus,
    onClick,
    autoFocus = false,
    value,
    disabled
  } = props;
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const [canSubmit, setCanSubmit] = React.useState(false);
  const boxRef = React.useRef<HTMLDivElement>(null);
  const focusState = useEditorFocus();
  const mounted = React.useRef(false);
  const [editor] = useLexicalComposerContext();

  const selectionState = React.useMemo(
    () => ({
      container: document.createElement('div'),
      elements: []
    }),
    []
  );

  React.useEffect(() => {
    if (focusState) {
      onFocus();
    } else {
      onBlur();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [focusState]);

  React.useEffect(() => {
    editor.setEditable(!disabled);

    if (disabled) {
      editor.dispatchCommand(BLUR_COMMAND, '');
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [disabled]);
  const [floatingAnchorElem, setFloatingAnchorElem] =
    React.useState<HTMLDivElement | null>(null);
  const [isSmallWidthViewport, setIsSmallWidthViewport] =
    React.useState<boolean>(false);
  const [isLinkEditMode, setIsLinkEditMode] = React.useState<boolean>(false);
  const selectionRef = React.useRef<RangeSelection | null>(null);

  React.useEffect(() => {
    setImmediate(() => {
      mounted.current = true;
    });
  }, []);
  React.useEffect(
    () =>
      editor.registerCommand(
        CLICK_COMMAND,
        () => {
          onClick();

          return false;
        },
        COMMAND_PRIORITY_LOW
      ),
    []
  );

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

  const onRef = (_floatingAnchorElem: HTMLDivElement) => {
    if (_floatingAnchorElem !== null) {
      setFloatingAnchorElem(_floatingAnchorElem);
    }
  };

  React.useEffect(() => {
    const updateViewPortWidth = () => {
      const isNextSmallWidthViewport = window.matchMedia(
        '(max-width: 1025px)'
      ).matches;

      if (isNextSmallWidthViewport !== isSmallWidthViewport) {
        setIsSmallWidthViewport(isNextSmallWidthViewport);
      }
    };
    updateViewPortWidth();
    window.addEventListener('resize', updateViewPortWidth);

    return () => {
      window.removeEventListener('resize', updateViewPortWidth);
    };
  }, [isSmallWidthViewport]);
  const updateLocation = React.useCallback(() => {
    editor.getEditorState().read(() => {
      const selection = $getSelection();

      if ($isRangeSelection(selection)) {
        selectionRef.current = selection.clone();
        const anchor = selection.anchor;
        const focus = selection.focus;
        const range = createDOMRange(
          editor,
          anchor.getNode(),
          anchor.offset,
          focus.getNode(),
          focus.offset
        );
        const boxElem = boxRef.current;

        if (range !== null && boxElem !== null) {
          const { left, bottom, width } = range.getBoundingClientRect();
          const selectionRects = createRectsFromDOMRange(editor, range);
          let correctedLeft =
            selectionRects.length === 1 ? left + width / 2 - 125 : left - 125;

          if (correctedLeft < 10) {
            correctedLeft = 10;
          }

          boxElem.style.left = `${correctedLeft}px`;
          boxElem.style.top = `${
            bottom +
            20 +
            (window.pageYOffset || document.documentElement.scrollTop)
          }px`;
          const selectionRectsLength = selectionRects.length;
          const { container } = selectionState;
          const elements: Array<HTMLSpanElement> = selectionState.elements;
          const elementsLength = elements.length;

          for (let i = 0; i < selectionRectsLength; i++) {
            const selectionRect = selectionRects[i];
            let elem: HTMLSpanElement = elements[i];

            if (elem === undefined) {
              elem = document.createElement('span');
              elements[i] = elem;
              container.appendChild(elem);
            }

            const color = '255, 212, 0';
            const style = `position:absolute;top:${
              selectionRect.top +
              (window.pageYOffset || document.documentElement.scrollTop)
            }px;left:${selectionRect.left}px;height:${
              selectionRect.height
            }px;width:${
              selectionRect.width
            }px;background-color:rgba(${color}, 0.3);pointer-events:none;z-index:5;`;
            elem.style.cssText = style;
          }
          for (let i = elementsLength - 1; i >= selectionRectsLength; i--) {
            const elem = elements[i];
            container.removeChild(elem);
            elements.pop();
          }
        }
      }
    });
  }, [editor, selectionState]);

  React.useLayoutEffect(() => {
    updateLocation();
    const container = selectionState.container;
    const body = document.body;

    if (body !== null) {
      body.appendChild(container);

      return () => {
        body.removeChild(container);
      };
    }
  }, [selectionState.container, updateLocation]);

  React.useEffect(() => {
    window.addEventListener('resize', updateLocation);

    return () => {
      window.removeEventListener('resize', updateLocation);
    };
  }, [updateLocation]);

  const handleChange = useOnChange(onChange, setCanSubmit);

  return (
    <EditorContainer className="editor-shell">
      <ToolbarPlugin setIsLinkEditMode={setIsLinkEditMode} />
      <EditorInner>
        <RichTextPlugin
          contentEditable={
            <div className="editor-scroller">
              <div className="editor" ref={onRef}>
                <ContentEditable className="editor-input" />
              </div>
            </div>
          }
          placeholder={<Placeholder text={placeholder} />}
          ErrorBoundary={LexicalErrorBoundary}
        />
        <HistoryPlugin />
        <OnChangePlugin onChange={handleChange} />
        {autoFocus ? <AutoFocusPlugin /> : null}
        <ListPlugin />
        <LinkPlugin />
        <CheckListPlugin />
        <AutoEmbedPlugin />
        <ImagesPlugin />
        <AutoLinkPlugin />
        <TablePlugin />
        <TableCellResizer />
        <YouTubePlugin />
        <VimeoPlugin />
        <IframePlugin />
        {floatingAnchorElem && !isSmallWidthViewport && (
          <>
            <FloatingLinkEditorPlugin
              anchorElem={floatingAnchorElem}
              isLinkEditMode={isLinkEditMode}
              setIsLinkEditMode={setIsLinkEditMode}
            />
            <TableCellActionMenuPlugin
              anchorElem={floatingAnchorElem}
              cellMerge
            />
            <FloatingTextFormatToolbarPlugin anchorElem={floatingAnchorElem} />
          </>
        )}
      </EditorInner>
    </EditorContainer>
  );
};
