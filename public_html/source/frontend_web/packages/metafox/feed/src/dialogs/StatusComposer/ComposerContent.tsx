import { DialogContent, useDialog } from '@metafox/dialog';
import useComposerContext from '@metafox/feed/hooks/useComposerContext';
import {
  LinkShape,
  useDraftEditorConfig,
  useGlobal,
  useSession,
  useResourceAction
} from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import useAddPhotoToStatusComposerHandler from '@metafox/photo/hooks/useAddPhotoToStatusComposerHandler';
import { TruncateText, UserAvatar } from '@metafox/ui';
import PrivacyView from '@metafox/ui/PrivacyView';
import {
  useMediaQuery,
  Box,
  CircularProgress,
  Typography
} from '@mui/material';
import { styled, useTheme } from '@mui/material/styles';
import clsx from 'clsx';
import { concat, get, isEmpty, isObject, orderBy, uniq, isArray } from 'lodash';
import React, { useCallback, useEffect } from 'react';
import composerConfig from '../../composerConfig';
import PrivacyControl from './PrivacyControl';
import AsPageAction from './AsPageAction';
import { REGEX_LENGTH_TEXT } from '@metafox/feed/constant';
import FeedEditor from '@metafox/feed/components/Composer/FeedEditor';
import EditorControlButton from './EditorControlButton';
import MapPreview from './MapPreview';

const APP_PAGE = 'page';

const name = 'ComposerConmtent';

const AvatarWrapper = styled('div', { name: 'AvatarWrapper' })(({ theme }) => ({
  marginRight: theme.spacing(1.5)
}));

const ComposerWrapper = styled('div', {
  name,
  slot: 'ComposerWrapper',
  shouldForwardProp: props => props !== 'textColor'
})<{ textColor?: string }>(({ theme, textColor }) => ({
  display: 'flex',
  alignItems: 'flex-start',
  minWidth: 'calc(100% - 110px)'
}));

const Root = styled(DialogContent, { name: 'Root' })(({ theme }) => ({
  maxHeight: 'unset !important',
  display: 'flex',
  flexDirection: 'column',
  padding: '0 !important',
  position: 'relative',
  zIndex: 1300,
  [theme.breakpoints.down('sm')]: {
    width: 'unset',
    overflow: 'visible'
  }
}));

const MaxLengthComposer = styled(Box, {
  name,
  shouldForwardProp: props => props !== 'hasStatusBackground'
})<{ hasStatusBackground?: boolean }>(({ theme, hasStatusBackground }) => ({
  padding: theme.spacing(1),
  paddingBottom: 0,
  display: 'flex',
  justifyContent: 'flex-end',
  alignItems: 'center',
  color: theme.palette.text.secondary,
  ...(hasStatusBackground && {
    position: 'absolute',
    bottom: 0,
    right: 0,
    height: 32,
    paddingBottom: theme.spacing(1)
  })
}));

const TextLengthStyled = styled(Typography, {
  name,
  slot: 'TextLengthStyled',
  shouldForwardProp: props => props !== 'maxLength'
})<{ maxLength?: boolean }>(({ theme, maxLength }) => ({
  fontSize: theme.mixins.pxToRem(16),
  ...(maxLength && {
    color: theme.palette.error.main
  })
}));

const CHARACTER_LIMIT_BACKGROUND_STATUS = 150;
const LINES_LIMIT_BACKGROUND_STATUS = 3;

interface Props {
  hidePrivacy: boolean;
  parentIdentity?: string;
  parentType?: string;
  disabledPrivacy?: boolean;
  setLoadingPreviewLink?: any;
  loadingPreviewLink?: boolean;
}

interface MaxLengthProps {
  lengthText: number;
  hasStatusBackground?: boolean;
}

const MAX_HEIGHT_COMPOSER_EDITOR = '450px';

const MaxLenghtBlock = ({
  lengthText,
  hasStatusBackground = false
}: MaxLengthProps) => {
  const { getSetting } = useGlobal();

  const SETTING_MAX_LENGTH_COMPOSER: number =
    getSetting('activity.feed.maximum_characters_for_post_status') || 0;

  if (SETTING_MAX_LENGTH_COMPOSER <= 0) return null;

  return (
    <MaxLengthComposer hasStatusBackground={hasStatusBackground}>
      <TextLengthStyled
        variant="body1"
        maxLength={lengthText > SETTING_MAX_LENGTH_COMPOSER}
      >
        {lengthText}/
      </TextLengthStyled>
      <TextLengthStyled variant="body1">
        {SETTING_MAX_LENGTH_COMPOSER}
      </TextLengthStyled>
    </MaxLengthComposer>
  );
};

const ComposerContent = ({
  submitting,
  hidePrivacy,
  parentIdentity,
  parentType,
  disabledPrivacy,
  setLoadingPreviewLink,
  loadingPreviewLink
}: Props) => {
  const theme = useTheme();
  const isSmallScreen = useMediaQuery(theme.breakpoints.down('sm'));
  const { jsxBackend, i18n, dispatch, dialogBackend, useGetItem, useIsMobile } =
    useGlobal();
  const parentUser = useGetItem(parentIdentity);
  const { user: authUser } = useSession();
  const { dialogProps } = useDialog();
  const isMobile = useIsMobile(true);
  const {
    data: initData,
    classes,
    composerState,
    composerRef,
    editorRef,
    editorState,
    condition,
    setEditorState,
    isEdit,
    strategy,
    editor,
    asPage,
    setAsPage,
    isEditSchedule,
    getTextSimple,
    getCurrenTextData
  } = useComposerContext();

  const [editorPlugins, editorComponents, editorControls, editorAttachers] =
    useDraftEditorConfig(composerConfig, condition);
  const [onChangeFile] = useAddPhotoToStatusComposerHandler(
    composerRef,
    undefined,
    { parentUser }
  );
  // set config mention on case has parentUser
  const configMention = useResourceAction(
    parentUser?.module_name,
    parentUser?.resource_name,
    'getForMentionInFeed'
  );

  const lengthText = [
    ...(getTextSimple()?.replace(REGEX_LENGTH_TEXT, '$3')?.trim() || '')
  ]?.length;

  const placeholder = i18n.formatMessage(
    {
      id: composerState?.parentUser
        ? 'write_something_to_parent_user'
        : 'what_s_your_mind'
    },
    { user: composerState?.parentUser?.name }
  );

  const textColorBackground = get(composerState, 'editorStyle.color');

  const hasStatusBackground =
    !isEmpty(get(composerState, 'editorStyle')) &&
    composerState.className === 'withBackgroundStatus';

  const hasBackgroundAndLink = Boolean(
    get(composerState, 'attachments.statusBackground.value.id') &&
      composerState.attachmentType === 'link' &&
      get(composerState, 'attachments.link.value.is_preview_hidden') &&
      get(composerState, 'attachments.link.value.link')
  );

  const hasAttachmentPhotos = get(composerState, 'attachments.photo.value');

  const hasAttachmentPoll = get(composerState, 'attachments.poll.value');

  const hasShareItem = get(composerState, 'attachments.shareItem.value');

  useEffect(() => {
    const hasLink = composerState.attachments['link']?.value;

    if (hasAttachmentPhotos && hasLink) {
      composerRef.current.setAttachments('photo', 'link', {
        as: 'StatusComposerControlPreviewLink',
        value: {
          ...composerState.attachments['link']?.value,
          is_preview_hidden: true
        }
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [hasAttachmentPhotos]);

  useEffect(() => {
    if (
      composerState.attachmentType === 'backgroundStatus' ||
      hasStatusBackground ||
      hasBackgroundAndLink
    ) {
      const length = getTextSimple()
        ?.replace(REGEX_LENGTH_TEXT, '$3')
        ?.trim()?.length;
      const lines = getCurrenTextData()?.split(/\r\n|\r|\n/).length;

      if (length > CHARACTER_LIMIT_BACKGROUND_STATUS) {
        composerRef.current.hideBackground();
      } else if (lines > LINES_LIMIT_BACKGROUND_STATUS) {
        composerRef.current.hideBackground();
      } else {
        composerRef.current.displayBackground();
      }
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [editorState, hasStatusBackground, hasBackgroundAndLink]);

  useEffect(() => {
    composerRef.current.setPostAsPage(asPage);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [asPage]);

  useEffect(() => {
    if (
      isEmpty(initData) ||
      isEmpty(initData.attachmentType) ||
      isEdit ||
      isEditSchedule
    )
      return;

    const { attachmentType } = initData;

    const attachment = initData.attachments[attachmentType];

    if (isEmpty(attachment)) return;

    const { value, as } = initData.attachments[attachmentType];
    const valueComposer =
      composerState.attachments[attachmentType]?.value || [];
    const newAttachment = isArray(value)
      ? uniq(concat(valueComposer, value)).filter(Boolean)
      : value;

    if (
      composerState.attachmentType === 'backgroundStatus' &&
      attachmentType !== 'backgroundStatus'
    ) {
      composerRef.current.removeBackground();
    }

    composerRef.current.setForceAttachments(attachmentType, attachmentType, {
      as,
      value: newAttachment
    });

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [composerRef, initData]);

  const focusToEndText = () => {
    setTimeout(() => {
      focusEditor();
    }, 500);
  };

  useEffect(() => {
    if (isMobile) return;

    if (dialogProps.open) {
      focusEditor();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [dialogProps.open]);

  const focusEditor = React.useCallback(() => {
    // updating open and focus at the same time cause bug: plugin editor not works
    setImmediate(() => editorRef.current.editor?.focus());
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [editorRef.current]);

  const setPrivacyValue = useCallback(
    (value: unknown) => {
      composerRef.current.setPrivacy(value);
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    []
  );

  const handlePreviewLink = React.useCallback(
    (url, prevUrl) => {
      if (
        (isEdit && composerState.post_type !== 'link') ||
        hasAttachmentPhotos ||
        hasAttachmentPoll ||
        hasShareItem
      )
        return;

      const currentLinkAttachment =
        composerState.attachments['link']?.value?.link;

      if (url === currentLinkAttachment) return;

      setLoadingPreviewLink(true);
      dispatch({
        type: 'statusComposer/getLink',
        payload: url,
        meta: {
          onSuccess: (data: LinkShape) => {
            composerRef.current.setAttachments('link', 'link', {
              as: 'StatusComposerControlPreviewLink',
              value: {
                ...data,
                is_preview_hidden: hasStatusBackground ? true : false
              }
            });
            setLoadingPreviewLink(false);
          },
          onFailure: (data: string) => {
            setLoadingPreviewLink(false);

            // will remove check when platform support change post_type
            if (isEdit) return;

            composerState.attachments['link'] &&
              composerRef.current.removeAttachmentLink();
          }
        }
      });
    },
    [
      composerRef,
      composerState.attachments,
      composerState.post_type,
      dispatch,
      hasAttachmentPhotos,
      hasAttachmentPoll,
      hasShareItem,
      hasStatusBackground,
      isEdit,
      setLoadingPreviewLink
    ]
  );

  const onChange = value => {
    setEditorState(value);
  };

  const handlePastedFiles = (files: Blob[]) => {
    if (!editorAttachers?.length) return;

    const canPastFile = editorAttachers.find(
      x =>
        x?.as === 'statusComposer.control.StatusUploadPhotoButton' &&
        !x?.disabled
    );

    if (!canPastFile) {
      dialogBackend.alert({
        message: i18n.formatMessage({ id: 'cant_add_attachment' })
      });
    } else {
      onChangeFile(files);
    }

    return 'handled';
  };

  const scrollProps = isSmallScreen ? { autoHeightMax: 'none' } : {};
  const scrollRef = React.useRef<HTMLDivElement>();
  const locationPlace = composerState?.tags?.place?.value;

  React.useLayoutEffect(() => {
    // Update location map display
    if (!locationPlace) return;

    const data = composerState?.tags?.place;
    const showMapView =
      locationPlace?.show_map &&
      !composerState?.attachmentType &&
      !loadingPreviewLink;

    if (showMapView === locationPlace?.show_map) return;

    composerRef.current?.setTags('place', {
      ...data,
      value: { ...locationPlace, show_map: showMapView }
    });
  }, [
    composerState?.attachmentType,
    locationPlace,
    loadingPreviewLink,
    composerRef,
    composerState?.tags?.place
  ]);

  return (
    <Root>
      <div className={classes.infoWrapper}>
        {parentUser?.item_type === APP_PAGE &&
        !isEdit &&
        parentUser?.is_admin ? (
          <AsPageAction asPage={asPage} setAsPage={setAsPage} page={parentUser}>
            <div className={classes.buttonWrapper}>
              {!hidePrivacy ? (
                <div className={classes.privacyButton}>
                  <PrivacyControl
                    disabled={disabledPrivacy}
                    setValue={setPrivacyValue}
                    value={composerState.privacy}
                  />
                </div>
              ) : (
                <div className={classes.privacyButton}>
                  <PrivacyView
                    item={
                      composerState?.privacy_feed ||
                      composerState?.privacy_detail
                    }
                  />
                </div>
              )}
            </div>
          </AsPageAction>
        ) : (
          <>
            <AvatarWrapper>
              <UserAvatar
                user={authUser as any}
                size={48}
                noStory
                showStatus={false}
                hoverCard={false}
              />
            </AvatarWrapper>
            <Box sx={{ flex: 1, minWidth: 0 }}>
              <div className={classes.userName}>
                <TruncateText
                  lines={1}
                  variant="h5"
                  className={classes.userName}
                >
                  {asPage ? parentUser.name : authUser?.full_name}
                </TruncateText>
              </div>
              <div className={classes.buttonWrapper}>
                {!hidePrivacy ? (
                  <div className={classes.privacyButton}>
                    <PrivacyControl
                      disabled={disabledPrivacy}
                      setValue={setPrivacyValue}
                      value={composerState.privacy}
                    />
                  </div>
                ) : (
                  <div className={classes.privacyButton}>
                    <PrivacyView
                      item={
                        composerState?.privacy_feed ||
                        composerState?.privacy_detail
                      }
                    />
                  </div>
                )}
              </div>
            </Box>
          </>
        )}
      </div>
      <Box
        pb={3}
        sx={{
          '& p': {
            margin: '0 !important'
          },
          '& .Scrollbar-view': {
            overflowX: 'hidden',
            marginBottom: '0 !important',
            height: '100% !important',
            maxHeight: `${MAX_HEIGHT_COMPOSER_EDITOR} !important`
          }
        }}
      >
        <ScrollContainer
          hideX
          autoHide
          autoHeight
          autoHeightMax={MAX_HEIGHT_COMPOSER_EDITOR}
          ref={scrollRef}
          {...scrollProps}
        >
          <div
            className={clsx(classes.contentWrapper, composerState.className)}
          >
            <ComposerWrapper textColor={textColorBackground}>
              <div className={classes.composeInner}>
                <Box
                  className={clsx(classes.composer, composerState.className)}
                  sx={composerState.editorStyle}
                  onClick={focusEditor}
                  data-testid="fieldStatus"
                >
                  <FeedEditor
                    submitting={submitting}
                    onChange={onChange}
                    value={editorState}
                    editorRef={editorRef}
                    placeholder={placeholder}
                    spellCheck
                    handlePastedFiles={handlePastedFiles}
                    sx={{
                      border: 0,
                      padding: theme =>
                        hasStatusBackground
                          ? theme.spacing(2)
                          : `0 ${theme.spacing(2)}`,
                      background: 'none',
                      overflow: 'visible',
                      minHeight: '32px'
                    }}
                    sxInner={
                      hasStatusBackground
                        ? {
                            '& a': {
                              color: 'unset'
                            },
                            '& .editor-placeholder': {
                              left: 0,
                              right: 0,
                              justifyContent: 'center'
                            }
                          }
                        : {}
                    }
                    editorPlugins={editorPlugins}
                    pluginsProps={{
                      handlePreviewLink,
                      parentUser,
                      configMention
                    }}
                  />
                  {hasStatusBackground && (
                    <MaxLenghtBlock
                      lengthText={lengthText}
                      hasStatusBackground={hasStatusBackground}
                    />
                  )}
                </Box>
              </div>
            </ComposerWrapper>
            <div className={classes.attachIconsWrapper}>
              <Box display="flex" justifyContent="flex-end">
                {editorControls.map(item =>
                  jsxBackend.render({
                    component: item.as,
                    props: {
                      disabled: item.disabled,
                      key: item.as,
                      strategy,
                      classes,
                      editorRef,
                      composerRef,
                      value: editor,
                      focusToEndText,
                      control: EditorControlButton
                    }
                  })
                )}
              </Box>

              {!hasStatusBackground && (
                <MaxLenghtBlock
                  lengthText={lengthText}
                  hasStatusBackground={hasStatusBackground}
                />
              )}
            </div>
          </div>
          <div className={classes.editorComponentsWrapper}>
            {jsxBackend.render(
              editorComponents.map(x => ({
                ...x,
                props: {
                  ...(x.props || {}),
                  userId: asPage ? parentUser?.id : authUser?.id,
                  userIdentity: asPage
                    ? parentUser?._identity
                    : authUser?._identity,
                  configMention,
                  parentUser
                }
              }))
            )}
          </div>
          <div className={classes.attachmentStage}>
            {isObject(composerState.attachments) &&
              Object.values(composerState.attachments).map(
                (attachment: any) =>
                  attachment &&
                  jsxBackend.render({
                    component: attachment.as,
                    props: {
                      key: attachment.as,
                      value: attachment.value,
                      composerRef,
                      editorRef,
                      isEdit,
                      parentUser,
                      submitting
                    }
                  })
              )}
            {hasStatusBackground ? null : (
              <Box>
                {loadingPreviewLink === true ? (
                  <div className={classes.loading}>
                    <CircularProgress size={30} />
                  </div>
                ) : null}
              </Box>
            )}
          </div>
        </ScrollContainer>
      </Box>
      <div className={classes.tagsStage}>
        {orderBy(Object.values(composerState.tags), 'priority').map(
          (data: any) =>
            jsxBackend.render({
              component: data.as,
              props: {
                key: data.as,
                value: data.value,
                composerRef,
                editorRef,
                parentType,
                parentIdentity,
                userId: asPage ? parentUser?.id : authUser?.id,
                userIdentity: asPage
                  ? parentUser?._identity
                  : authUser?._identity
              }
            })
        )}
      </div>
      {locationPlace?.show_map ? (
        <Box px={2}>
          <MapPreview composerRef={composerRef} composerState={composerState} />
        </Box>
      ) : null}
      {composerState?.schedule_time?.value
        ? jsxBackend.render({
            component: composerState.schedule_time.as,
            props: {
              key: composerState.schedule_time.as,
              value: composerState.schedule_time.value,
              composerRef,
              editorRef,
              parentType,
              parentIdentity,
              userId: asPage ? parentUser?.id : authUser?.id,
              userIdentity: asPage ? parentUser?._identity : authUser?._identity
            }
          })
        : null}
    </Root>
  );
};

export default ComposerContent;
