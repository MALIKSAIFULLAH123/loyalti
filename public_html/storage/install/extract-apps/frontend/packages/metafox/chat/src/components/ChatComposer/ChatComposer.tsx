/**
 * @type: ui
 * name: ChatSimpleComposer
 * chunkName: composer
 */
import { ChatComposerProps } from '@metafox/chat/types';
import { RefOf, useDraftEditorConfig, useGlobal } from '@metafox/framework';
import { htmlToText } from '@metafox/utils';
import React, { useEffect } from 'react';
import composerConfig from './composerConfig';
import useCheckImageSize from '@metafox/chat/hooks/useCheckImageSize';
import {
  AttachIconsWrapper,
  ChatComposerControl,
  ChatComposerForm,
  ChatEditor,
  ComposerWrapper,
  htmlToTextLexical
} from '../Wrapper';
import { $getRoot } from 'lexical';

interface HandleComposer {}

function ChatComposer(
  {
    rid,
    msgId,
    text = '',
    focus = true,
    reactMode,
    onSuccess,
    onMarkAsRead,
    margin = 'normal',
    previewRef
  }: ChatComposerProps,
  ref: RefOf<HandleComposer>
) {
  const { i18n, dispatch, jsxBackend, getAcl } = useGlobal();

  const acl = getAcl();
  const firstUpdate = React.useRef(true);
  const editorRef = React.useRef<any>({});
  const [, , checkValidateImage] = useCheckImageSize();

  const [lexicalState, setLexicalState] = React.useState<string>(
    text ? htmlToText(text) : ''
  );
  const [submitting, setSubmitting] = React.useState<boolean>(false);
  const [previewFiles, setPreviewFiles] = React.useState([]);

  const condition = React.useMemo(() => {
    return {
      editing: reactMode === 'edit',
      previewFiles: previewFiles.length,
      acl
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [reactMode, previewFiles, acl]);

  const [editorPlugins, , editorControls] = useDraftEditorConfig(
    composerConfig,
    condition
  );
  const placeholder = i18n.formatMessage({ id: 'write_a_message' });

  useEffect(() => {
    if (firstUpdate.current) {
      firstUpdate.current = false;

      if (text) {
        dispatch({
          type: 'chat/room/text',
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
      setLexicalState(text);
      focusEditor();
    }

    if (reactMode === 'reply') {
      focusEditor();
    }

    if (reactMode === 'no_react') {
      setLexicalState('');
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [text, reactMode, rid, msgId]);

  const clearEditor = (isBlur: boolean = true) => {
    if (isBlur) {
      editorRef.current?.editor?.blur();
    }

    setLexicalState('');

    if (previewRef.current?.clear) {
      previewRef.current.clear();
      setPreviewFiles([]);
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
  }, [rid]);

  const handleUploadSuccess = () => {
    previewRef.current?.clear();
    setPreviewFiles([]);
    clearEditor();
    setSubmitting(false);

    if (reactMode !== 'no_react') {
      onSuccess();
    }
  };

  const handleUploadFail = () => {
    setSubmitting(false);
  };

  const getCurrentText = () => {
    return htmlToTextLexical(lexicalState).trim();
  };

  const handleSubmitContent = () => {
    const text = getCurrentText();

    if (!text || submitting) return;

    setSubmitting(true);

    dispatch({
      type: 'chat/composer/SUBMIT',
      payload: {
        reactMode,
        rid,
        msgId,
        text
      },
      meta
    });
  };

  const handleSubmit = () => {
    if (
      previewFiles &&
      previewFiles.length &&
      Object.values(previewFiles).length
    ) {
      const text = getCurrentText();

      if (submitting) return;

      setSubmitting(true);

      dispatch({
        type: 'chat/composer/upload',
        payload: {
          files: Object.values(previewFiles),
          rid,
          text,
          reactMode,
          msgId
        },
        meta: {
          onSuccess: handleUploadSuccess,
          onFailure: handleUploadFail
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
      }
    };
  });

  const disableSubmit = React.useMemo(() => {
    const data = getCurrentText();

    return !!((!submitting && previewFiles?.length) || data);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [previewFiles, lexicalState, submitting]);

  const handlePastedFiles = (files: FileList[]) => {
    if (!acl?.chat?.chat_message?.send_attachment || !files.length) return;

    // accept image
    if (previewRef && previewRef.current) {
      const validate = checkValidateImage([...previewFiles, ...files], true);

      if (!validate) return;

      setPreviewFiles([...previewFiles, ...files]);
      previewRef.current?.attachFiles(files);
    }
  };

  const onLexicalChange = value => {
    setLexicalState(value);
  };

  const handleClickComposer = () => {
    focusEditor();
    onMarkAsRead();
  };

  return (
    <ChatComposerForm margin={margin}>
      <ComposerWrapper onClick={handleClickComposer}>
        <ChatEditor
          handleSubmit={handleSubmit}
          onChange={onLexicalChange}
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
              handleSubmit
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
