/**
 * @type: ui
 * name: ChatComposer
 * chunkName: composer.Chatplus
 */
import { ChatComposerProps, RoomType } from '@metafox/chatplus/types';
import { RefOf, useGlobal, usePrevious } from '@metafox/framework';
import React, { useEffect } from 'react';
import composerConfig from './composerConfig';
import useDraftChatConfig from './../../hooks/useDraftChatConfig';
import { formatGeneralMsg } from '@metafox/chatplus/services/formatTextMsg';
import { $getRoot, $getSelection, $createTextNode, $isTextNode } from 'lexical';
import {
  AttachIconsWrapper,
  ChatComposerControl,
  ChatComposerForm,
  ComposerWrapper
} from '../Wrapper';
import {
  $isMentionNode,
  ChatEditor,
  htmlToTextLexical
} from '../Wrapper/ChatComposer';
import { isEmpty } from 'lodash';

interface HandleComposer {}

function ChatComposer(
  {
    rid,
    room,
    user,
    msgId,
    text = '',
    focus,
    reactMode,
    onSuccess,
    margin = 'normal',
    subscription,
    previewRef,
    isAllPage
  }: ChatComposerProps,
  ref: RefOf<HandleComposer>
) {
  const { i18n, dispatch, jsxBackend, chatplus } = useGlobal();
  const preReactMode = usePrevious(reactMode);
  const firstUpdate = React.useRef(true);
  const editorRef = React.useRef<any>({});

  const [lexicalState, setLexicalState] = React.useState<string>(
    text ? formatGeneralMsg(text) : ''
  );
  const [submitting, setSubmitting] = React.useState<boolean>(false);
  const [previewFiles, setPreviewFiles] = React.useState([]);

  const condition = React.useMemo(() => {
    return {
      editing: reactMode === 'edit',
      previewFiles: previewFiles.length,
      isBotRoom: room?.isBotRoom
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [reactMode, previewFiles, room?.isBotRoom]);

  const [editorPlugins, , editorControls] = useDraftChatConfig(
    composerConfig,
    condition,
    rid,
    room,
    isAllPage
  );

  const placeholder =
    room?.t !== RoomType.Direct
      ? i18n.formatMessage({ id: 'type_a_message_or__name' })
      : i18n.formatMessage({ id: 'write_a_message' });

  useEffect(() => {
    focusEditor();

    if (firstUpdate.current) {
      firstUpdate.current = false;

      if (text) {
        dispatch({
          type: 'chatplus/room/text',
          payload: {
            rid,
            text: ''
          }
        });
      }

      return;
    }

    clearEditor(false);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [rid]);

  React.useEffect(() => {
    if (reactMode === 'edit') {
      const textEdit = text ? formatGeneralMsg(text) : '';
      setLexicalState(textEdit);
      focusEditor();
    }

    if (reactMode === 'reply') {
      focusEditor();
    }

    if (reactMode === 'no_react' && preReactMode === 'edit') {
      setLexicalState('');
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [text, reactMode, rid, msgId]);

  const clearEditor = (isBlur: boolean = true) => {    
    if (isBlur) {
      editorRef.current?.editor?.blur();
    }
    
    setLexicalState('');
    setPreviewFiles([]);

    if (previewRef.current?.clear) {
      previewRef.current.clear();
    }

    editorRef.current?.editor?.update(() => {
      const root = $getRoot();
      root.clear();
    });
  };

  const meta = React.useMemo(() => {
    return {
      onSuccess: () => {
        clearEditor();
        setSubmitting(false);
        typeof onSuccess === 'function' && onSuccess();
      }
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [clearEditor]);

  const focusEditor = React.useCallback(() => {
    // updating open and focus at the same time cause bug: plugin editor not works
    setImmediate(() => editorRef.current?.editor?.focus());
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [subscription]);

  const getCurrentText = () => {
    return htmlToTextLexical(lexicalState).trim();
  };

  const handleSubmitContent = () => {
    const text = getCurrentText();

    if (!text || submitting) return;

    setSubmitting(true);

    dispatch({
      type: 'chatplus/composer/SUBMIT',
      payload: {
        reactMode,
        rid,
        msgId,
        text
      },
      meta
    });
  };

  const handleUploadSuccess = () => {
    previewRef.current?.clear();
    setPreviewFiles([]);
    clearEditor();

    if (reactMode !== 'no_react') {
      onSuccess();
    }
  };

  const flagTyping = React.useRef(false);
  const offTypingTimer = React.useRef(null);

  const onKeyDown = (evt: any) => {
    const typingInfo = {
      typingUser: {
        name: user?.name,
        username: user?.username,
        avatarETag: user?.avatarETag
      }
    };

    if (
      !evt.keyCode ||
      13 !== evt.keyCode ||
      evt.metaKey ||
      evt.shiftKey ||
      evt.altKey ||
      evt.ctrlKey
    ) {
      if (!flagTyping.current) {
        flagTyping.current = true;
        chatplus.typingMessage(rid, user.username, true, typingInfo);
      }

      clearTimeout(offTypingTimer.current);
      offTypingTimer.current = setTimeout(() => {
        flagTyping.current = false;
        chatplus.typingMessage(rid, user.username, false, typingInfo);
      }, 5000);
    } else {
      clearTimeout(offTypingTimer.current);
      chatplus.typingMessage(rid, user.username, false, typingInfo);
      flagTyping.current = false;
    }
  };

  // handle add space after when mention success
  const onBeforeKeyDown = () => {
    if (isEmpty(editorRef?.current?.editor)) return;

    editorRef.current?.editor.update(() => {
      const selection = $getSelection();

      if (!selection) return;

      const anchor = selection?.anchor;
      const [node] = selection?.getNodes() || [];

      if (!node) return;

      const prevNode = node?.getPreviousSibling();
      const textContent = node?.getTextContent();
      const isTextNode = $isTextNode(node) && node?.isSimpleText();
      const offset = anchor?.offset;
      const last =
        textContent?.substring(0, offset)?.split(/\s+/)?.at(-1) ?? '';

      if (isTextNode && $isMentionNode(prevNode) && offset === 1 && last) {
        node.insertBefore($createTextNode(' '));
      }
    });
  };

  const handleSubmit = () => {
    if (
      previewFiles &&
      previewFiles.length &&
      Object.values(previewFiles).length
    ) {
      const text = getCurrentText();

      dispatch({
        type: 'chatplus/composer/upload',
        payload: {
          files: Object.values(previewFiles),
          rid,
          text,
          reactMode,
          msgId
        },
        meta: {
          onSuccess: handleUploadSuccess
        }
      });
    } else {
      handleSubmitContent();
    }
  };

  React.useEffect(() => {
    if (focus) {
      focusEditor();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [focus, focusEditor]);

  React.useImperativeHandle(ref, () => {
    return {
      attachFiles: (files: File[]) => {
        if (files?.length) {
          setPreviewFiles(files);
        }
      },
      removeFile: (index: any) => {
        const filesList = [...previewFiles];

        if (index > -1) {
          filesList.splice(index, 1);

          setPreviewFiles([...filesList]);
        }
      },
      getPreviewFiles: () => previewFiles
    };
  });

  const disableSubmit = React.useMemo(() => {
    const data = getCurrentText();

    return !!(previewFiles?.length || data);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [previewFiles, lexicalState]);

  const handlePastedFiles = (files: FileList[]) => {
    if (!files.length) return;

    if (previewRef && previewRef.current) {
      setPreviewFiles([...previewFiles, ...files]);
      previewRef.current?.attachFiles([...previewFiles, ...files]);
    }
  };

  const onLexicalChange = value => {
    setLexicalState(value);
  };

  return (
    <ChatComposerForm margin={margin}>
      <ComposerWrapper onClick={focusEditor}>
        {room ? (
          <ChatEditor
            handleSubmit={handleSubmit}
            onChange={onLexicalChange}
            onKeyDown={onKeyDown}
            onBeforeKeyDown={onBeforeKeyDown}
            value={lexicalState}
            editorRef={editorRef}
            placeholder={placeholder}
            handlePastedFiles={handlePastedFiles}
            sx={{
              border: 0,
              padding: '6px 12px',
              background: 'none',
              overflow: 'visible',
              minHeight: '32px'
            }}
            editorPlugins={editorPlugins}
          />
        ) : null}
      </ComposerWrapper>
      <AttachIconsWrapper>
        {editorControls.map(item =>
          jsxBackend.render({
            component: item.as,
            props: {
              key: item.as,
              previewRef,
              filesUploadRef: ref,
              control: ChatComposerControl,
              editorRef,
              rid,
              disableSubmit,
              handleSubmit,
              disablePortal: true,
              placement: 'top'
            }
          })
        )}
      </AttachIconsWrapper>
    </ChatComposerForm>
  );
}

export default React.forwardRef<HandleComposer, ChatComposerProps>(
  ChatComposer
);
