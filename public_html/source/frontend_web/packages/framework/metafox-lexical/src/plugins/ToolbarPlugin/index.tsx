import {
  $isCodeNode,
  CODE_LANGUAGE_FRIENDLY_NAME_MAP,
  CODE_LANGUAGE_MAP,
  getLanguageFriendlyName
} from '@lexical/code';
import { $isLinkNode, TOGGLE_LINK_COMMAND } from '@lexical/link';
import { $isListNode, ListNode } from '@lexical/list';
import { INSERT_EMBED_COMMAND } from '@lexical/react/LexicalAutoEmbedPlugin';
import { useLexicalComposerContext } from '@lexical/react/LexicalComposerContext';
import { $isDecoratorBlockNode } from '@lexical/react/LexicalDecoratorBlockNode';
import { $isHeadingNode, $isQuoteNode } from '@lexical/rich-text';
import {
  $getSelectionStyleValueForProperty,
  $isParentElementRTL,
  $patchStyleText
} from '@lexical/selection';
import { $isTableNode } from '@lexical/table';
import {
  $findMatchingParent,
  $getNearestBlockElementAncestorOrThrow,
  $getNearestNodeOfType,
  mergeRegister
} from '@lexical/utils';
import {
  $createParagraphNode,
  $getNodeByKey,
  $getSelection,
  $isElementNode,
  $isRangeSelection,
  $isRootOrShadowRoot,
  $isTextNode,
  CAN_REDO_COMMAND,
  CAN_UNDO_COMMAND,
  COMMAND_PRIORITY_CRITICAL,
  COMMAND_PRIORITY_NORMAL,
  ElementFormatType,
  FORMAT_TEXT_COMMAND,
  KEY_MODIFIER_COMMAND,
  LexicalEditor,
  NodeKey,
  REDO_COMMAND,
  SELECTION_CHANGE_COMMAND,
  UNDO_COMMAND
} from 'lexical';
import React, { Dispatch, useCallback, useEffect, useState } from 'react';
import useModal from '@metafox/lexical/hooks/useModal';
import {
  DropDown,
  DropDownItem,
  DropdownColorPicker
} from '@metafox/lexical/ui';
import { sanitizeUrl, getSelectedNode } from '@metafox/lexical/utils';
import { EmbedConfigs } from '../AutoEmbedPlugin';
import { InsertImageDialog } from '../ImagesPlugin';
import { styled, Box } from '@mui/material';
import { InsertTableDialog } from '../TablePlugin';
import TypeBoldSvg from '@metafox/lexical/assets/images/icons/type-bold.svg';
import TypeItalicSvg from '@metafox/lexical/assets/images/icons/type-italic.svg';
import TypeUnderlineSvg from '@metafox/lexical/assets/images/icons/type-underline.svg';
import LinkSvg from '@metafox/lexical/assets/images/icons/link.svg';
import FontColorIcon from '@metafox/lexical/assets/images/icons/font-color.svg';
import BackgroundColorIcon from '@metafox/lexical/assets/images/icons/bg-color.svg';
import DropdownMoreIcon from '@metafox/lexical/assets/images/icons/dropdown-more.svg';
import StrikethroughIcon from '@metafox/lexical/assets/images/icons/type-strikethrough.svg';
import SubscriptIcon from '@metafox/lexical/assets/images/icons/type-subscript.svg';
import SuperscriptIcon from '@metafox/lexical/assets/images/icons/type-superscript.svg';
import ClearIcon from '@metafox/lexical/assets/images/icons/trash.svg';
import RedoIcon from '@metafox/lexical/assets/images/icons/arrow-clockwise.svg';
import UndoIcon from '@metafox/lexical/assets/images/icons/arrow-counterclockwise.svg';
import BlockFormatDropDown from './BlockFormatDropDown';
import ElementFormatDropdown from './ElementFormatDropdown';
import { LineIcon } from '@metafox/ui';
import { useGlobal } from '@metafox/framework';

const name = 'RichtextEditorToolbar';
const ToolbarWrapper = styled(Box, {
  name,
  slot: 'ToolbarWrapper'
})(({ theme }) => ({
  display: 'flex',
  flexFlow: 'wrap',
  marginBottom: theme.spacing(1),
  borderBottom: '1px dashed',
  borderBottomColor:
    theme.palette.mode === 'light'
      ? 'rgba(0, 0, 0, 0.1)'
      : 'rgba(255, 255, 255, 0.1)',
  '& > button': {
    marginRight: theme.spacing(0.5),
    marginBottom: theme.spacing(0.5)
  }
}));
const blockTypeToBlockName = {
  bullet: 'Bulleted List',
  check: 'Check List',
  code: 'Code Block',
  h1: 'Heading 1',
  h2: 'Heading 2',
  h3: 'Heading 3',
  h4: 'Heading 4',
  h5: 'Heading 5',
  h6: 'Heading 6',
  number: 'Numbered List',
  paragraph: 'Normal',
  quote: 'Quote'
};

const rootTypeToRootName = {
  root: 'Root',
  table: 'Table'
};

function getCodeLanguageOptions(): [string, string][] {
  const options: [string, string][] = [];

  for (const [lang, friendlyName] of Object.entries(
    CODE_LANGUAGE_FRIENDLY_NAME_MAP
  )) {
    options.push([lang, friendlyName]);
  }

  return options;
}

const CODE_LANGUAGE_OPTIONS = getCodeLanguageOptions();

const FONT_FAMILY_OPTIONS: [string, string][] = [
  ['Arial', 'Arial'],
  ['Courier New', 'Courier New'],
  ['Georgia', 'Georgia'],
  ['Times New Roman', 'Times New Roman'],
  ['Trebuchet MS', 'Trebuchet MS'],
  ['Verdana', 'Verdana']
];

const FONT_SIZE_OPTIONS: [string, string][] = [
  ['10px', '10px'],
  ['11px', '11px'],
  ['12px', '12px'],
  ['13px', '13px'],
  ['14px', '14px'],
  ['15px', '15px'],
  ['16px', '16px'],
  ['17px', '17px'],
  ['18px', '18px'],
  ['19px', '19px'],
  ['20px', '20px'],
  ['22px', '22px'],
  ['24px', '24px'],
  ['26px', '26px'],
  ['28px', '28px'],
  ['30px', '30px'],
  ['32px', '32px'],
  ['34px', '34px'],
  ['36px', '36px'],
  ['38px', '38px'],
  ['40px', '40px']
];

const DividerLine = styled(Box, {
  name,
  slot: 'ToolbarWrapper'
})(({ theme }) => ({
  display: 'inline-flex',
  width: '1px',
  background:
    theme.palette.mode === 'light'
      ? 'rgba(0, 0, 0, 0.23)'
      : 'rgba(255, 255, 255, 0.23)',
  height: '24px',
  margin: 'auto 4px'
}));

function Divider(): JSX.Element {
  return <DividerLine />;
}

function FontDropDown({
  editor,
  value,
  type,
  disabled = false
}: {
  editor: LexicalEditor;
  value: string;
  type: string;
  disabled?: boolean;
}): JSX.Element {
  const handleClick = useCallback(
    (option: string) => {
      editor.update(() => {
        const selection = $getSelection();

        if (selection !== null) {
          $patchStyleText(selection, {
            [type]: option
          });
        }
      });
    },
    [editor, type]
  );

  const buttonAriaLabel =
    type === 'font-family'
      ? 'Formatting options for font family'
      : 'Formatting options for font size';

  return (
    <DropDown
      disabled={disabled}
      buttonLabel={value}
      buttonAriaLabel={buttonAriaLabel}
      sxDropdownWrapper={{ maxHeight: '300px', overflowY: 'auto' }}
    >
      {(type === 'font-family' ? FONT_FAMILY_OPTIONS : FONT_SIZE_OPTIONS).map(
        ([option, text]) => (
          <DropDownItem
            className={`item  ${type === 'font-size' ? 'fontsize-item' : ''}`}
            onClick={() => handleClick(option)}
            key={option}
            selected={value === option}
          >
            <span className="text">{text}</span>
          </DropDownItem>
        )
      )}
    </DropDown>
  );
}

export default function ToolbarPlugin({
  setIsLinkEditMode
}: {
  setIsLinkEditMode: Dispatch<boolean>;
}): JSX.Element {
  const { i18n, useLoggedIn } = useGlobal();
  const loggedIn = useLoggedIn();
  const [editor] = useLexicalComposerContext();
  const [activeEditor, setActiveEditor] = useState(editor);
  const [blockType, setBlockType] =
    useState<keyof typeof blockTypeToBlockName>('paragraph');
  const [rootType, setRootType] =
    useState<keyof typeof rootTypeToRootName>('root');
  const [selectedElementKey, setSelectedElementKey] = useState<NodeKey | null>(
    null
  );
  const [fontSize, setFontSize] = useState<string>('15px');
  const [fontColor, setFontColor] = useState<string>('#000');
  const [bgColor, setBgColor] = useState<string>('#fff');
  // const [fontFamily, setFontFamily] = useState<string>('Arial');
  const [elementFormat, setElementFormat] = useState<ElementFormatType>('left');
  const [isLink, setIsLink] = useState(false);
  const [isBold, setIsBold] = useState(false);
  const [isItalic, setIsItalic] = useState(false);
  const [isUnderline, setIsUnderline] = useState(false);
  const [isStrikethrough, setIsStrikethrough] = useState(false);
  const [isSubscript, setIsSubscript] = useState(false);
  const [isSuperscript, setIsSuperscript] = useState(false);
  const [canUndo, setCanUndo] = useState(false);
  const [canRedo, setCanRedo] = useState(false);
  const [modal, showModal] = useModal();
  const [isRTL, setIsRTL] = useState(false);
  const [codeLanguage, setCodeLanguage] = useState<string>('');
  const [isEditable, setIsEditable] = useState(() => editor.isEditable());

  const $updateToolbar = useCallback(() => {
    const selection = $getSelection();

    if ($isRangeSelection(selection)) {
      const anchorNode = selection.anchor.getNode();
      let element =
        anchorNode.getKey() === 'root'
          ? anchorNode
          : $findMatchingParent(anchorNode, e => {
              const parent = e.getParent();

              return parent !== null && $isRootOrShadowRoot(parent);
            });

      if (element === null) {
        element = anchorNode.getTopLevelElementOrThrow();
      }

      const elementKey = element.getKey();
      const elementDOM = activeEditor.getElementByKey(elementKey);

      // Update text format
      setIsBold(selection.hasFormat('bold'));
      setIsItalic(selection.hasFormat('italic'));
      setIsUnderline(selection.hasFormat('underline'));
      setIsStrikethrough(selection.hasFormat('strikethrough'));
      setIsSubscript(selection.hasFormat('subscript'));
      setIsSuperscript(selection.hasFormat('superscript'));
      setIsRTL($isParentElementRTL(selection));

      // Update links
      const node = getSelectedNode(selection);
      const parent = node.getParent();

      if ($isLinkNode(parent) || $isLinkNode(node)) {
        setIsLink(true);
      } else {
        setIsLink(false);
      }

      const tableNode = $findMatchingParent(node, $isTableNode);

      if ($isTableNode(tableNode)) {
        setRootType('table');
      } else {
        setRootType('root');
      }

      if (elementDOM !== null) {
        setSelectedElementKey(elementKey);

        if ($isListNode(element)) {
          const parentList = $getNearestNodeOfType<ListNode>(
            anchorNode,
            ListNode
          );
          const type = parentList
            ? parentList.getListType()
            : element.getListType();
          setBlockType(type);
        } else {
          const type = $isHeadingNode(element)
            ? element.getTag()
            : element.getType();

          if (type in blockTypeToBlockName) {
            setBlockType(type as keyof typeof blockTypeToBlockName);
          }

          if ($isCodeNode(element)) {
            const language =
              element.getLanguage() as keyof typeof CODE_LANGUAGE_MAP;

            setCodeLanguage(
              language ? CODE_LANGUAGE_MAP[language] || language : ''
            );

            return;
          }
        }
      }

      // Handle buttons
      setFontSize(
        $getSelectionStyleValueForProperty(selection, 'font-size', '15px')
      );
      setFontColor(
        $getSelectionStyleValueForProperty(selection, 'color', '#000')
      );
      setBgColor(
        $getSelectionStyleValueForProperty(
          selection,
          'background-color',
          '#fff'
        )
      );
      // setFontFamily(
      //   $getSelectionStyleValueForProperty(selection, 'font-family', 'Arial')
      // );
      let matchingParent;

      if ($isLinkNode(parent)) {
        // If node is a link, we need to fetch the parent paragraph node to set format
        matchingParent = $findMatchingParent(
          node,
          parentNode => $isElementNode(parentNode) && !parentNode.isInline()
        );
      }

      // If matchingParent is a valid node, pass it's format type
      setElementFormat(
        $isElementNode(matchingParent)
          ? matchingParent.getFormatType()
          : $isElementNode(node)
          ? node.getFormatType()
          : parent?.getFormatType() || 'left'
      );
    }
  }, [activeEditor]);

  useEffect(() => {
    return editor.registerCommand(
      SELECTION_CHANGE_COMMAND,
      (_payload, newEditor) => {
        $updateToolbar();
        setActiveEditor(newEditor);

        return false;
      },
      COMMAND_PRIORITY_CRITICAL
    );
  }, [editor, $updateToolbar]);

  useEffect(() => {
    return mergeRegister(
      editor.registerEditableListener(editable => {
        setIsEditable(editable);
      }),
      activeEditor.registerUpdateListener(({ editorState }) => {
        editorState.read(() => {
          $updateToolbar();
        });
      }),
      activeEditor.registerCommand<boolean>(
        CAN_UNDO_COMMAND,
        payload => {
          setCanUndo(payload);

          return false;
        },
        COMMAND_PRIORITY_CRITICAL
      ),
      activeEditor.registerCommand<boolean>(
        CAN_REDO_COMMAND,
        payload => {
          setCanRedo(payload);

          return false;
        },
        COMMAND_PRIORITY_CRITICAL
      )
    );
  }, [$updateToolbar, activeEditor, editor]);

  useEffect(() => {
    return activeEditor.registerCommand(
      KEY_MODIFIER_COMMAND,
      payload => {
        const event: KeyboardEvent = payload;
        const { code, ctrlKey, metaKey } = event;

        if (code === 'KeyK' && (ctrlKey || metaKey)) {
          event.preventDefault();
          let url: string | null;

          if (!isLink) {
            setIsLinkEditMode(true);
            url = sanitizeUrl('https://');
          } else {
            setIsLinkEditMode(false);
            url = null;
          }

          return activeEditor.dispatchCommand(TOGGLE_LINK_COMMAND, url);
        }

        return false;
      },
      COMMAND_PRIORITY_NORMAL
    );
  }, [activeEditor, isLink, setIsLinkEditMode]);

  const applyStyleText = useCallback(
    (styles: Record<string, string>, skipHistoryStack?: boolean) => {
      activeEditor.update(
        () => {
          const selection = $getSelection();

          if (selection !== null) {
            $patchStyleText(selection, styles);
          }
        },
        skipHistoryStack ? { tag: 'historic' } : {}
      );
    },
    [activeEditor]
  );

  const clearFormatting = useCallback(() => {
    activeEditor.update(() => {
      const selection = $getSelection();

      if ($isRangeSelection(selection)) {
        const anchor = selection.anchor;
        const focus = selection.focus;
        const nodes = selection.getNodes();

        if (anchor.key === focus.key && anchor.offset === focus.offset) {
          return;
        }

        nodes.forEach((node, idx) => {
          // We split the first and last node by the selection
          // So that we don't format unselected text inside those nodes
          if ($isTextNode(node)) {
            // Use a separate variable to ensure TS does not lose the refinement
            let textNode = node;

            if (idx === 0 && anchor.offset !== 0) {
              textNode = textNode.splitText(anchor.offset)[1] || textNode;
            }

            if (idx === nodes.length - 1) {
              textNode = textNode.splitText(focus.offset)[0] || textNode;
            }

            if (textNode.__style !== '') {
              textNode.setStyle('');
            }

            if (textNode.__format !== 0) {
              textNode.setFormat(0);
              $getNearestBlockElementAncestorOrThrow(textNode).setFormat('');
            }

            node = textNode;
          } else if ($isHeadingNode(node) || $isQuoteNode(node)) {
            node.replace($createParagraphNode(), true);
          } else if ($isDecoratorBlockNode(node)) {
            node.setFormat('');
          }
        });
      }
    });
  }, [activeEditor]);

  const onFontColorSelect = useCallback(
    (value: string, skipHistoryStack: boolean) => {
      applyStyleText({ color: value }, skipHistoryStack);
    },
    [applyStyleText]
  );

  const onBgColorSelect = useCallback(
    (value: string, skipHistoryStack: boolean) => {
      applyStyleText({ 'background-color': value }, skipHistoryStack);
    },
    [applyStyleText]
  );

  const insertLink = useCallback(() => {
    if (!isLink) {
      setIsLinkEditMode(true);
      editor.dispatchCommand(TOGGLE_LINK_COMMAND, sanitizeUrl('https://'));
    } else {
      setIsLinkEditMode(false);
      editor.dispatchCommand(TOGGLE_LINK_COMMAND, null);
    }
  }, [editor, isLink]);

  const onCodeLanguageSelect = useCallback(
    (value: string) => {
      activeEditor.update(() => {
        if (selectedElementKey !== null) {
          const node = $getNodeByKey(selectedElementKey);

          if ($isCodeNode(node)) {
            node.setLanguage(value);
          }
        }
      });
    },
    [activeEditor, selectedElementKey]
  );

  return (
    <ToolbarWrapper>
      <button
        disabled={!canUndo || !isEditable}
        onClick={() => {
          activeEditor.dispatchCommand(UNDO_COMMAND, undefined);
        }}
        title={i18n.formatMessage({ id: 'rich_text_editor_undo' })}
        type="button"
        className="toolbar-item spaced"
        aria-label="Undo"
      >
        <UndoIcon />
      </button>
      <button
        disabled={!canRedo || !isEditable}
        onClick={() => {
          activeEditor.dispatchCommand(REDO_COMMAND, undefined);
        }}
        title={i18n.formatMessage({ id: 'rich_text_editor_redo' })}
        type="button"
        className="toolbar-item"
        aria-label="Redo"
      >
        <RedoIcon />
      </button>
      <Divider />
      {blockType in blockTypeToBlockName && activeEditor === editor && (
        <>
          <BlockFormatDropDown
            disabled={!isEditable}
            blockType={blockType}
            rootType={rootType}
            editor={editor}
          />
          <Divider />
        </>
      )}
      {blockType === 'code' ? (
        <DropDown
          disabled={!isEditable}
          buttonClassName="toolbar-item code-language"
          buttonLabel={getLanguageFriendlyName(codeLanguage)}
          buttonAriaLabel="Select language"
        >
          {CODE_LANGUAGE_OPTIONS.map(([value, name]) => {
            return (
              <DropDownItem
                className={'item'}
                onClick={() => onCodeLanguageSelect(value)}
                key={value}
                selected={value === codeLanguage}
              >
                <span className="text">{name}</span>
              </DropDownItem>
            );
          })}
        </DropDown>
      ) : (
        <>
          <ElementFormatDropdown
            disabled={!isEditable}
            value={elementFormat}
            editor={editor}
            isRTL={isRTL}
          />
          <FontDropDown
            disabled={!isEditable}
            type={'font-size'}
            value={fontSize}
            editor={editor}
          />
          <Divider />
          <button
            disabled={!isEditable}
            onClick={() => {
              activeEditor.dispatchCommand(FORMAT_TEXT_COMMAND, 'bold');
            }}
            className={`toolbar-item spaced ${isBold ? 'active' : ''}`}
            title={i18n.formatMessage({ id: 'rich_text_editor_bold' })}
            type="button"
            aria-label={i18n.formatMessage({ id: 'rich_text_editor_bold' })}
          >
            <TypeBoldSvg />
          </button>
          <button
            disabled={!isEditable}
            onClick={() => {
              activeEditor.dispatchCommand(FORMAT_TEXT_COMMAND, 'italic');
            }}
            className={`toolbar-item spaced ${isItalic ? 'active' : ''}`}
            title={i18n.formatMessage({ id: 'rich_text_editor_italic' })}
            type="button"
            aria-label={i18n.formatMessage({ id: 'rich_text_editor_italic' })}
          >
            <TypeItalicSvg />
          </button>
          <button
            disabled={!isEditable}
            onClick={() => {
              activeEditor.dispatchCommand(FORMAT_TEXT_COMMAND, 'underline');
            }}
            className={`toolbar-item spaced ${isUnderline ? 'active' : ''}`}
            title={i18n.formatMessage({ id: 'rich_text_editor_underline' })}
            type="button"
            aria-label={i18n.formatMessage({
              id: 'rich_text_editor_underline'
            })}
          >
            <TypeUnderlineSvg />
          </button>
          <button
            disabled={!isEditable}
            onClick={insertLink}
            className={`toolbar-item spaced ${isLink ? 'active' : ''}`}
            aria-label="Insert link"
            title={i18n.formatMessage({ id: 'rich_text_editor_insert_link' })}
            type="button"
          >
            <LinkSvg />
          </button>
          <DropdownColorPicker
            disabled={!isEditable}
            buttonAriaLabel={i18n.formatMessage({
              id: 'rich_text_editor_text_color'
            })}
            color={fontColor}
            onChange={onFontColorSelect}
            title={i18n.formatMessage({ id: 'rich_text_editor_text_color' })}
            buttonIcon={<FontColorIcon />}
          />
          <DropdownColorPicker
            disabled={!isEditable}
            buttonAriaLabel={i18n.formatMessage({
              id: 'rich_text_editor_background_color'
            })}
            color={bgColor}
            onChange={onBgColorSelect}
            title={i18n.formatMessage({
              id: 'rich_text_editor_background_color'
            })}
            buttonIcon={<BackgroundColorIcon />}
          />
          <DropDown
            disabled={!isEditable}
            buttonAriaLabel="Formatting options for additional text styles"
            buttonIcon={<DropdownMoreIcon />}
          >
            <DropDownItem
              onClick={() => {
                activeEditor.dispatchCommand(
                  FORMAT_TEXT_COMMAND,
                  'strikethrough'
                );
              }}
              className={'item '}
              title={i18n.formatMessage({
                id: 'rich_text_editor_strikethrough'
              })}
              aria-label={i18n.formatMessage({
                id: 'rich_text_editor_strikethrough'
              })}
              selected={isStrikethrough}
            >
              <StrikethroughIcon />
              <span className="text">
                {i18n.formatMessage({ id: 'rich_text_editor_strikethrough' })}
              </span>
            </DropDownItem>
            <DropDownItem
              onClick={() => {
                activeEditor.dispatchCommand(FORMAT_TEXT_COMMAND, 'subscript');
              }}
              className={'item '}
              title={i18n.formatMessage({ id: 'rich_text_editor_subscript' })}
              aria-label={i18n.formatMessage({
                id: 'rich_text_editor_subscript'
              })}
              selected={isSubscript}
            >
              <SubscriptIcon />
              <span className="text">
                {i18n.formatMessage({ id: 'rich_text_editor_subscript' })}
              </span>
            </DropDownItem>
            <DropDownItem
              onClick={() => {
                activeEditor.dispatchCommand(
                  FORMAT_TEXT_COMMAND,
                  'superscript'
                );
              }}
              className={'item'}
              title={i18n.formatMessage({ id: 'rich_text_editor_superscript' })}
              aria-label={i18n.formatMessage({
                id: 'rich_text_editor_superscript'
              })}
              selected={isSuperscript}
            >
              <SuperscriptIcon />
              <span className="text">
                {i18n.formatMessage({ id: 'rich_text_editor_superscript' })}
              </span>
            </DropDownItem>
            <DropDownItem
              onClick={clearFormatting}
              className="item"
              title={i18n.formatMessage({
                id: 'rich_text_editor_clear_formatting'
              })}
              aria-label={i18n.formatMessage({
                id: 'rich_text_editor_clear_formatting'
              })}
            >
              <ClearIcon />
              <span className="text">
                {i18n.formatMessage({
                  id: 'rich_text_editor_clear_formatting'
                })}
              </span>
            </DropDownItem>
          </DropDown>
          <Divider />
          <DropDown
            disabled={!isEditable}
            buttonLabel={i18n.formatMessage({ id: 'rich_text_editor_insert' })}
            buttonAriaLabel="Insert specialized editor node"
          >
            {/* current rule only accept upload when loggedIn */}
            {loggedIn ? (
              <DropDownItem
                onClick={() => {
                  showModal(
                    i18n.formatMessage({ id: 'rich_text_editor_insert_image' }),
                    onClose => (
                      <InsertImageDialog
                        activeEditor={activeEditor}
                        onClose={onClose}
                      />
                    )
                  );
                }}
                className="item"
              >
                <LineIcon sx={{ mr: 1 }} icon="ico-photo-o" />
                <span className="text">
                  {i18n.formatMessage({ id: 'rich_text_editor_image' })}
                </span>
              </DropDownItem>
            ) : null}
            <DropDownItem
              onClick={() => {
                showModal(
                  i18n.formatMessage({ id: 'rich_text_editor_insert_table' }),
                  onClose => (
                    <InsertTableDialog
                      activeEditor={activeEditor}
                      onClose={onClose}
                    />
                  )
                );
              }}
              className="item"
            >
              <LineIcon sx={{ mr: 1 }} icon="ico-th-o" />
              <span className="text">
                {i18n.formatMessage({ id: 'rich_text_editor_table' })}
              </span>
            </DropDownItem>
            {EmbedConfigs.map(embedConfig => (
              <DropDownItem
                key={embedConfig.type}
                onClick={() => {
                  activeEditor.dispatchCommand(
                    INSERT_EMBED_COMMAND,
                    embedConfig.type
                  );
                }}
                className="item"
              >
                <Box component="span" mr={1}>
                  {embedConfig.icon}
                </Box>
                <span className="text">
                  {i18n.formatMessage({
                    id: embedConfig?.contentName || 'rich_text_editor_embed'
                  })}
                </span>
              </DropDownItem>
            ))}
          </DropDown>
        </>
      )}

      {modal}
    </ToolbarWrapper>
  );
}
