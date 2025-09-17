import { BlockViewProps, useGetItem, useGlobal } from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { UserAvatar } from '@metafox/ui';
import { UserItemShape } from '@metafox/user';
import { filterShowWhen } from '@metafox/utils';
import { Box, styled, useMediaQuery, useTheme } from '@mui/material';
import { isEmpty } from 'lodash';
import * as React from 'react';
import composerConfig from '../../composerConfig';
import useStatusComposer from '../../hooks/useStatusComposer';
import Control from './Control';
import HtmlViewer from '@metafox/html-viewer';

export interface Props extends BlockViewProps {
  variant: 'default' | 'expanded';
  item: UserItemShape;
}

const strategy = 'block';

const AvatarWrapper = styled('div', { name: 'AvatarWrapper' })(({ theme }) => ({
  marginRight: theme.spacing(1.5)
}));

const ComposerWrapper = styled('div', { name: 'ComposerWrapper' })(
  ({ theme }) => ({
    display: 'flex',
    width: '100%',
    alignItems: 'center',
    minWidth: 0,
    [theme.breakpoints.down('sm')]: {
      display: 'block',
      width: '100%'
    }
  })
);

const ComposerInput = styled('div', {
  name: 'LayoutSlot',
  slot: 'ComposerInput',
  overridesResolver(props, styles) {
    return [styles.composerInput];
  }
})(({ theme }) => ({
  border: theme.mixins.border('secondary'),
  flex: 1,
  backgroundColor: theme.palette.action.hover,
  height: theme.spacing(6),
  borderRadius: 24,
  padding: theme.spacing(0, 3),
  cursor: 'pointer',
  color: theme.palette.text.secondary,
  fontSize: theme.mixins.pxToRem(15),
  fontWeight: theme.typography.fontWeightRegular,
  letterSpacing: 0,
  WebkitBoxOrient: 'vertical',
  WebkitLineClamp: '1',
  display: '-webkit-box',
  overflow: 'hidden',
  textOverflow: 'ellipsis',
  lineHeight: theme.mixins.pxToRem(48),
  '*': {
    margin: '0 !important'
  },
  [theme.breakpoints.down('sm')]: {
    height: theme.spacing(4),
    lineHeight: theme.mixins.pxToRem(32),
    padding: theme.spacing(0, 2)
  }
}));

const ComposerToolbar = styled('div', { name: 'ComposerToolbar' })(
  ({ theme }) => ({
    display: 'flex',
    marginLeft: theme.spacing(1.5),
    [theme.breakpoints.down('sm')]: {
      marginLeft: theme.spacing(0),
      marginTop: theme.spacing(1.5),
      '& > *': {
        marginRight: `${theme.spacing(1.5)} !important`
      }
    }
  })
);

const ComposerToolbarExpand = styled('div', { name: 'ComposerToolbarExpand' })(
  ({ theme }) => ({
    display: 'flex',
    borderTop: 'solid 1px',
    borderTopColor: theme.palette.border?.secondary,
    marginTop: theme.spacing(2),
    marginLeft: theme.spacing(8),
    paddingTop: theme.spacing(1)
  })
);

export default function StatusComposer({
  item,
  title,
  variant,
  blockProps,
  showWhen
}: Props) {
  const [composerState, , composerRef] = useStatusComposer();
  const {
    i18n,
    useSession,
    dispatch,
    jsxBackend,
    usePageParams,
    getAcl,
    getSetting
  } = useGlobal();
  const theme = useTheme();
  const isSmallScreen = useMediaQuery(theme.breakpoints.down('sm'));
  const acl = getAcl();
  const canCreate = getAcl('activity.feed.create');
  const setting = getSetting();
  const { user: authUser, loggedIn } = useSession();
  const pageParams = usePageParams();
  const placeholder = i18n.formatMessage(
    { id: 'what_s_your_mind' },
    { user: authUser?.full_name }
  );
  const composerStatusValue =
    useGetItem('formValues.dialogStatusComposer') || placeholder;

  const { identity: parentIdentity, item_type: parentType } = pageParams;
  const parentId = parentIdentity ? parentIdentity.split('.')[3] : '';
  const isUserProfileOther =
    parentType === 'user' && parentId && authUser?.id !== parseInt(parentId);

  React.useEffect(() => {
    // skip when user does not logged in
    if (!loggedIn) return;

    dispatch({
      type: 'setting/sharingItemPrivacy/FETCH',
      payload: { id: authUser?.id }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [loggedIn]);

  composerRef.current.requestComposerUpdate = React.useCallback(() => {
    setImmediate(() => {
      const { attachmentType, attachments } = composerRef.current.state;

      dispatch({
        type: 'statusComposer/onPress/status',
        payload: {
          data: {
            attachmentType,
            attachments: {
              [attachmentType]: attachments[attachmentType]
            }
          },
          parentIdentity,
          parentType
        }
      });
    });
  }, [composerRef, dispatch, parentIdentity, parentType]);

  let dataStatusComposer = undefined;

  if (item?.privacy_feed || item?.privacy_detail) {
    dataStatusComposer = {
      privacy_feed: item?.privacy_feed,
      privacy_detail: item?.privacy_detail
    };
  }

  const handleClick = () => {
    dispatch({
      type: 'statusComposer/onPress/status',
      payload: {
        parentIdentity,
        parentType,
        data: dataStatusComposer
      }
    });
  };

  const handleResetRef = () => {
    composerRef.current.removeAttachments();
  };

  const condition = React.useMemo(
    () => ({ strategy, acl, setting, isUserProfileOther, item, parentType }),
    [acl, setting, isUserProfileOther, item, parentType]
  );

  const attachers = filterShowWhen(composerConfig.attachers, condition);

  if (
    isEmpty(authUser) ||
    !canCreate ||
    item?.profile_settings?.profile_view_profile === false ||
    item?.profile_settings?.feed_share_on_wall === false
  )
    return null;

  const show = !!filterShowWhen([{ showWhen }], { item }).length;

  if (!show) return null;

  if (variant === 'expanded')
    return (
      <Block testid="blockStatusComposer">
        <BlockHeader title={title} />
        <BlockContent>
          <Box display="flex" flexDirection="row">
            <AvatarWrapper>
              <UserAvatar
                user={authUser}
                size={isSmallScreen ? 32 : 48}
                data-testid="userAvatar"
                noStory
                showStatus={false}
              />
            </AvatarWrapper>
            <ComposerInput
              data-testid="whatsHappening"
              color="info"
              onClick={handleClick}
            >
              <HtmlViewer html={composerStatusValue} />
            </ComposerInput>
          </Box>
          <ComposerToolbarExpand onClick={handleResetRef}>
            {attachers.map(attacher =>
              jsxBackend.render({
                component: attacher.as,
                props: {
                  key: attacher.as,
                  strategy,
                  composerRef,
                  composerState,
                  control: Control,
                  parentIdentity
                }
              })
            )}
          </ComposerToolbarExpand>
        </BlockContent>
      </Block>
    );

  const attachersMinimize = isSmallScreen ? attachers : attachers.slice(0, 3);

  return (
    <Block testid="blockStatusComposer">
      <BlockHeader title={title} />
      <BlockContent>
        <Box display="flex" flexDirection="row">
          <AvatarWrapper>
            <UserAvatar
              user={authUser}
              size={isSmallScreen ? 32 : 48}
              data-testid="userAvatar"
              noStory
              showStatus={false}
            />
          </AvatarWrapper>
          <ComposerWrapper>
            <ComposerInput data-testid="whatsHappening" onClick={handleClick}>
              <HtmlViewer html={composerStatusValue} />
            </ComposerInput>
            <ComposerToolbar
              data-testid="composerToolbar"
              onClick={handleResetRef}
            >
              {attachersMinimize.map(attacher =>
                jsxBackend.render({
                  component: attacher.as,
                  props: {
                    key: attacher.as,
                    strategy,
                    composerRef,
                    composerState,
                    control: Control,
                    parentIdentity
                  }
                })
              )}
            </ComposerToolbar>
          </ComposerWrapper>
        </Box>
      </BlockContent>
    </Block>
  );
}
