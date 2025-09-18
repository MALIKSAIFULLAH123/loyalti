/**
 * @type: ui
 * name: CommentComposer
 * chunkName: comment
 */
import {
  RefOf,
  useDraftEditorConfig,
  useGetItem,
  useGlobal,
  useScript,
  useResourceAction,
  CAPTCHA_RECAPTCHA_V3_SITE_KEY
} from '@metafox/framework';
import { Image, LineIcon, UserAvatar } from '@metafox/ui';
import { getImageSrc, isPhotoType } from '@metafox/utils';
import { Box, styled, Tooltip } from '@mui/material';
import { ScrollContainer } from '@metafox/layout';
import React from 'react';
import composerConfig from '../../composerConfig';
import { CommentComposerProps, PreviewUploadPhotoHandle } from '../../types';
import PreviewUploadPhoto from '../PreviewUploadPhoto';
import CommentControl from './CommentControl';
import { get } from 'lodash';
import { useDispatch } from 'react-redux';
import { isTabletMobileMode } from '@metafox/comment/utils';
import CommentEditor from './CommentEditor';
import htmlToTextLexical from './Lexical/utils/htmlToTextComment';
import transform from './Lexical/utils/transform';
import { $createMentionNode } from '@metafox/comment/components/Mention/MentionNode';
import { $insertNodes, $createTextNode, $getRoot } from 'lexical';

const name = 'CommentComposer';

const ComposeOuter = styled('div', {
  name,
  slot: 'composeOuter',
  shouldForwardProp: prop => prop !== 'margin' && prop !== 'processing'
})<{ margin: string; processing?: boolean }>(
  ({ theme, margin, processing }) => ({
    cursor: 'default',
    display: 'flex',
    padding: theme.spacing(2, 0),
    ...(margin && { padding: theme.spacing(1, 0) }),
    ...(margin === 'none' && { padding: 0 }),
    ...(processing && { pointerEvents: 'none', opacity: 0.5 })
  })
);
const ComposeInputWrapper = styled('div', {
  name,
  slot: 'composeInputWrapper'
})(({ theme }) => ({
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
  }
}));
const Composer = styled('div', { name, slot: 'composer' })(({ theme }) => ({
  cursor: 'text',
  flex: 1,
  flexBasis: 'auto',
  minWidth: 0,
  display: 'flex'
}));
const AttachIconsWrapper = styled('div', { name, slot: 'attachIconsWrapper' })(
  ({ theme }) => ({
    display: 'inline-flex',
    alignItems: 'center',
    padding: theme.spacing(0, 0.25),
    marginLeft: 'auto'
  })
);
const IconSend = styled('div', {
  name,
  slot: 'IconSend',
  shouldForwardProp: prop => prop !== 'isMobile'
})<{ isMobile?: boolean }>(({ theme, isMobile }) => ({
  marginLeft: '8px',
  display: 'none',
  color: theme.palette.primary.main,
  width: theme.spacing(4),
  height: theme.spacing(5),
  alignItems: 'center',
  justifyContent: 'center',
  ...(isMobile && {
    '.activeSend &': {
      display: 'inline-flex'
    }
  })
}));
const ComposeWrapper = styled('div', {
  name,
  slot: 'ComposeWrapper',
  shouldForwardProp: prop => prop !== 'isMobile'
})<{ isMobile?: boolean }>(({ theme, isMobile }) => ({
  position: 'relative',
  ...(isMobile && {
    display: 'flex',
    alignItems: 'flex-end'
  })
}));
const CloseButton = styled('div', { name, slot: 'closeButton' })(
  ({ theme }) => ({
    cursor: 'pointer',
    width: theme.spacing(3),
    height: theme.spacing(3),
    backgroundColor:
      theme.palette.mode === 'light'
        ? theme.palette.grey['400']
        : theme.palette.background.default,
    borderRadius: '50%',
    fontSize: 'small',
    justifyContent: 'center',
    alignItems: 'center',
    display: 'inline-flex',
    opacity: 0.8,
    color:
      theme.palette.mode === 'light'
        ? theme.palette.text.primary
        : theme.palette.text.primary,
    '&:hover': {
      border: theme.mixins.border('secondary'),
      backgroundColor: theme.palette.background.default,
      color: theme.palette.text.primary
    }
  })
);
const CancelBtn = styled('div', { name, slot: 'cancelBtn' })(({ theme }) => ({
  fontSize: 'small',
  color: theme.palette.text.secondary,
  marginLeft: theme.spacing(5),
  marginBottom: theme.spacing(1),
  '& [role="button"]': {
    color: theme.palette.primary.main,
    '&:hover': {
      textDecoration: 'underline'
    }
  }
}));
const AvatarWrapper = styled('div', { name, slot: 'avatarWrapper' })(
  ({ theme }) => ({
    marginRight: theme.spacing(1)
  })
);

const toggleCaptchaBadge = (show: boolean) => {
  const badge = document.getElementsByClassName('grecaptcha-badge')[0];

  if (badge && badge instanceof HTMLElement) {
    badge.style.visibility = show ? 'visible' : 'hidden';
  }
};

const useCaptcha = (name: string) => {
  const { getSetting } = useGlobal();
  const settingCaptcha = getSetting('captcha') as Record<string, any>;
  const condition = get(settingCaptcha.rules, name);
  const [loading, setLoading] = React.useState(false);
  const config = useResourceAction('captcha', 'captcha', 'getVerifyForm');
  const dispatch = useDispatch();

  const isGoogleCaptchaV3 = settingCaptcha?.default === 'recaptcha_v3';
  // const isImageCaptcha = settingCaptcha?.default === 'image_captcha';
  const siteKey = isGoogleCaptchaV3 ? CAPTCHA_RECAPTCHA_V3_SITE_KEY : null;

  useScript(
    isGoogleCaptchaV3 && siteKey && condition
      ? `https://www.google.com/recaptcha/api.js?render=${siteKey}`
      : null
  );

  React.useEffect(() => {
    toggleCaptchaBadge(true);

    return () => {
      toggleCaptchaBadge(false);
    };
  }, []);

  if (!condition) {
    return {
      status: false
    };
  }

  const processCaptcha = props => {
    if (loading) return;

    dispatch({
      type: 'comment/processCaptcha',
      payload: {
        settingCaptcha,
        setLoading,
        siteKey,
        ...props
      }
    });
  };

  return {
    type: settingCaptcha?.default,
    status: true,
    processing: loading,
    setProcessing: setLoading,
    processCaptcha,
    formDatasource: config
  };
};

function CommentComposer(
  {
    open,
    text = '',
    focus,
    editing,
    identity,
    identityResource,
    onCancel,
    extra_data,
    onSuccess,
    margin = 'normal',
    parentUser,
    isReply,
    replyUser,
    actions,
    editorConfig
  }: CommentComposerProps,
  ref: RefOf<HTMLButtonElement>
) {
  const {
    i18n,
    getAcl,
    getSetting,
    useSession,
    dispatch,
    jsxBackend,
    dialogBackend,
    useIsMobile
  } = useGlobal();
  const { user: authUser } = useSession();
  const isMobile = useIsMobile(true) || isTabletMobileMode;

  const acl = getAcl();
  const commentSetting = getSetting('comment') as Object;
  const settings = getSetting() as Object;
  const captchaHook = useCaptcha('comment.create_comment');
  const { setProcessing, processCaptcha } = captchaHook || {};
  const editorRef = React.useRef({});
  const previewRef = React.useRef<PreviewUploadPhotoHandle>();
  const refForm = React.useRef<HTMLElement>();
  const isAuthReplyUser = replyUser?.id === authUser?.id;

  const [lexicalState, setLexicalState] = React.useState<string>(
    transform(text || '')
  );
  const [openSend, setOpenSend] = React.useState<Boolean>(!!text);
  const [photoId, setPhotoId] = React.useState<number>(
    'storage_file' === extra_data?.extra_type ? extra_data?.item_id : undefined
  );
  const [stickerId, setStickerId] = React.useState<number>(
    'sticker' === extra_data?.extra_type ? extra_data?.item_id : undefined
  );

  const [giphyGifId, setGiphyGifId] = React.useState<number>(
    'gif' === extra_data?.extra_type
      ? extra_data?.params?.giphy_gif_id
      : undefined
  );
  const commentWrapperRef = React.useRef();

  const sticker = useGetItem(`sticker.entities.sticker.${stickerId}`);
  const gif = useGetItem(`giphy.entities.gif.${giphyGifId}`);

  const isShowPreviewSticker =
    sticker || (editing && !!stickerId && 'sticker' === extra_data?.extra_type);

  const isShowPreviewGif =
    gif || (editing && !!giphyGifId && 'gif' === extra_data?.extra_type);

  const isShowPreviewPhoto =
    editing &&
    extra_data?.item_id === photoId &&
    'storage_file' === extra_data?.extra_type;

  const hasExtraContent =
    isShowPreviewGif || isShowPreviewSticker || isShowPreviewPhoto || photoId;

  const focusEditor = React.useCallback(() => {
    // support chrome on mobile can focus
    const inputEle = refForm.current?.querySelector('.editor-input');

    setImmediate(() =>
      inputEle && inputEle.focus
        ? inputEle.focus()
        : editorRef.current.editor?.focus()
    );
  }, []);

  const blurEditor = React.useCallback(() => {
    const inputEle = refForm.current?.querySelector('.editor-input');

    setImmediate(() =>
      inputEle && inputEle.focus
        ? inputEle.blur()
        : editorRef.current.editor?.blur()
    );
  }, []);
  let placeholderMessage = isReply
    ? 'write_reply_three_dots'
    : 'write_comment_three_dots';

  if (parentUser?.resource_name === 'group' && parentUser?.reg_method === 0) {
    placeholderMessage = isReply
      ? 'write_public_reply_three_dots'
      : 'write_public_comment_three_dots';
  }

  const placeholder = i18n.formatMessage({ id: placeholderMessage });

  const meta = React.useMemo(() => {
    return {
      onSuccess: () => {
        if (onSuccess) {
          onSuccess();
        }

        setProcessing && setProcessing(false);
        focusEditor();

        if (captchaHook?.type) {
          clearEditor();

          if (captchaHook?.type === 'image_captcha') {
            dialogBackend.dismiss();
          }
        }
      },
      onCancel: errorData => {
        setProcessing && setProcessing(false);
        focusEditor();

        if (onCancel) {
          onCancel();
        }
      }
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [onSuccess, onCancel, captchaHook?.type]);

  const clearEditor = () => {
    setLexicalState('');

    editorRef.current.editor?.update(() => {
      const root = $getRoot();
      root.clear();
    });

    if (previewRef.current?.clear) {
      previewRef.current.clear();
    }

    setStickerId(undefined);
    setPhotoId(undefined);
    setGiphyGifId(undefined);

    if (isMobile) {
      blurEditor();
    }
  };

  const condition = React.useMemo(
    () => ({
      config: editorConfig,
      editing,
      acl,
      settings,
      hasExtraContent
    }),
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [acl, editing, hasExtraContent, settings]
  );
  const [editorPlugins, editorComponents, editorControls] =
    useDraftEditorConfig(composerConfig, condition);
  const [focused, setFocused] = React.useState(false);

  const onStickerClick = React.useCallback(
    (sticker_id: number) => {
      setStickerId(sticker_id);
      setPhotoId(null);

      return;
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [dispatch, editing, identity, identityResource]
  );

  const onGifClick = React.useCallback(
    (giphy_id: any) => {
      setGiphyGifId(giphy_id);
      setPhotoId(null);

      return;
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [dispatch, editing, identity, identityResource]
  );

  const submitComment = (data = {}, clear = true) => {
    const sticker_id = stickerId;

    if (clear) {
      clearEditor();
    }

    const text = getCurrentText();

    dispatch({
      type: editing ? 'comment/composer/UPDATE' : 'comment/composer/CREATE',
      payload: {
        editing,
        identity,
        text,
        sticker_id,
        giphy_gif_id: giphyGifId,
        photo_id: photoId,
        identityResource,
        ...data
      },
      meta
    });
  };

  const handleCaptcha = onSubmitCaptcha => {
    setProcessing(true);
    blurEditor();
    processCaptcha({
      handleSubmit: onSubmitCaptcha
    });

    return;
  };

  const getCurrentText = () => {
    return htmlToTextLexical(lexicalState).trim();
  };

  const handleSubmit = () => {
    if (previewRef.current?.checkIsLoading()) return;

    const text = getCurrentText();

    if (!text && !photoId && !stickerId && !giphyGifId && !editing) return;

    if (editing && !text && !stickerId && !giphyGifId && !photoId) {
      actions.deleteComment();

      return;
    }

    if (captchaHook?.status && processCaptcha && !editing) {
      handleCaptcha(submitComment);

      return;
    }

    submitComment();
  };

  const handlePreviewPhoto = (id: number) => {
    setPhotoId(id);
  };

  React.useEffect(() => {
    if (focus) {
      focusEditor();

      if (isReply && replyUser && !isAuthReplyUser && !lexicalState) {
        editorRef.current.editor?.update(() => {
          const mentionNode = $createMentionNode({
            name: replyUser?.full_name,
            link: `@mention/${replyUser.resource_name}/${replyUser.id}`
          });

          $insertNodes([mentionNode, $createTextNode(' ')]);
        });
      }
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [focus, focusEditor, replyUser?.id]);

  React.useEffect(() => {
    const text = getCurrentText();

    setOpenSend(text && text !== '');
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [lexicalState]);

  const handlePastedFiles = (files: FileList) => {
    if (editorConfig?.disable_photo || !files.length) return 'handled';

    // accept image
    if (previewRef && previewRef.current) {
      const file = files[0];

      const isGifType = (mime: string) => mime && /image\/gif/i.test(mime);

      if (
        (isGifType(file.type) && commentSetting?.enable_sticker) ||
        (isPhotoType(file.type) && commentSetting?.enable_photo)
      ) {
        const data: FileList = [file];
        previewRef.current?.attachFiles(data);
      }
    }

    return 'handled';
  };

  if (!open) {
    return null;
  }

  const onLexicalChange = value => {
    setLexicalState(value);
  };

  return (
    <form ref={refForm} role="presentation" data-testid="commentForm">
      <ComposeOuter processing={captchaHook.processing} margin={margin}>
        <AvatarWrapper>
          <UserAvatar
            user={authUser as any}
            size={isMobile ? 40 : 32}
            data-testid="userAvatar"
            noStory
            showStatus={false}
          />
        </AvatarWrapper>
        <Box flex="1" minWidth="0">
          <ComposeWrapper
            className={openSend || photoId || stickerId ? 'activeSend' : ''}
            isMobile={isMobile}
          >
            <ComposeInputWrapper ref={commentWrapperRef}>
              <Composer
                data-testid="fieldComment"
                sx={{
                  '& p': {
                    margin: '0 !important'
                  },
                  '& .Scrollbar-view': {
                    overflowX: 'hidden',
                    marginBottom: '0 !important',
                    maxHeight: '100% !important',
                    height: '100% !important'
                  }
                }}
              >
                <ScrollContainer
                  hideX
                  autoHide
                  autoHeight
                  autoHeightMax={'300px'}
                >
                  <CommentEditor
                    handleSubmit={handleSubmit}
                    onChange={onLexicalChange}
                    value={lexicalState}
                    editorRef={editorRef}
                    placeholder={placeholder}
                    handlePastedFiles={handlePastedFiles}
                    isMobile={isMobile}
                    onFocus={() => setFocused(true)}
                    onBlur={() => setFocused(false)}
                    sx={{
                      border: 0,
                      padding: '6px 12px',
                      background: 'none',
                      overflow: 'visible',
                      minHeight: '32px'
                    }}
                    editorPlugins={editorPlugins}
                    handleCancel={onCancel}
                  />
                  {jsxBackend.render(editorComponents)}
                </ScrollContainer>
              </Composer>
              <AttachIconsWrapper>
                {editorControls.map(item =>
                  jsxBackend.render({
                    component: item.as,
                    props: {
                      key: item.as,
                      previewRef,
                      onStickerClick,
                      onGifClick,
                      control: CommentControl,
                      editorRef
                    }
                  })
                )}
              </AttachIconsWrapper>
            </ComposeInputWrapper>
            <IconSend onClick={handleSubmit} isMobile={isMobile}>
              <LineIcon icon="ico-paperplane-alt-o" />
            </IconSend>
          </ComposeWrapper>
          {isShowPreviewPhoto && (
            <Box mt={1}>
              <Box maxWidth={208} position="relative">
                <Image
                  src={getImageSrc(extra_data?.image, '500')}
                  alt={'photo'}
                />
                <Box position="absolute" top={1} right={1}>
                  <Tooltip title={i18n.formatMessage({ id: 'remove' })}>
                    <CloseButton onClick={() => setPhotoId(null)}>
                      <LineIcon icon="ico-close" />
                    </CloseButton>
                  </Tooltip>
                </Box>
              </Box>
            </Box>
          )}
          {isShowPreviewSticker && (
            <Box mt={1}>
              <Box position="relative" width={80} height={80}>
                <Image
                  src={getImageSrc(sticker?.image || extra_data?.image, '500')}
                  alt={'sticker'}
                  aspectRatio={'fixed'}
                  imageFit={'contain'}
                />
                <Box position="absolute" top={0} right={-1.5}>
                  <Tooltip title={i18n.formatMessage({ id: 'remove' })}>
                    <CloseButton
                      onClick={() => setStickerId(editing ? 0 : undefined)}
                    >
                      <LineIcon icon="ico-close" />
                    </CloseButton>
                  </Tooltip>
                </Box>
              </Box>
            </Box>
          )}
          {isShowPreviewGif && (
            <Box mt={1}>
              <Box position="relative" width={80} height={80}>
                <Image
                  src={get(
                    gif?.images || extra_data?.params?.images,
                    'fixed_width.url'
                  )}
                  alt={'giphy'}
                  aspectRatio={'fixed'}
                  imageFit={'contain'}
                />
                <Box position="absolute" top={0} right={-1.5}>
                  <Tooltip title={i18n.formatMessage({ id: 'remove' })}>
                    <CloseButton
                      onClick={() => setGiphyGifId(editing ? 0 : undefined)}
                    >
                      <LineIcon icon="ico-close" />
                    </CloseButton>
                  </Tooltip>
                </Box>
              </Box>
            </Box>
          )}
          <PreviewUploadPhoto ref={previewRef} onChange={handlePreviewPhoto} />
        </Box>
      </ComposeOuter>

      {editing && (
        <CancelBtn>
          <span className={!focused && 'hidden'}>
            {i18n.formatMessage(
              { id: 'press_esc_to_cancel' },
              {
                button: text => (
                  <span
                    onMouseDown={e => {
                      e.stopPropagation();
                      e.preventDefault();
                      onCancel();
                    }}
                    role="button"
                    children={text}
                  />
                )
              }
            )}
          </span>
          <span
            className={focused && 'hidden'}
            onClick={onCancel}
            role="button"
          >
            {i18n.formatMessage({ id: 'cancel' })}
          </span>
        </CancelBtn>
      )}
    </form>
  );
}

export default React.forwardRef<HTMLButtonElement, CommentComposerProps>(
  CommentComposer
);
