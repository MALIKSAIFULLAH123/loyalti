import {
  BlockViewProps,
  useGlobal,
  useScrollDirection
} from '@metafox/framework';
import {
  STATUS_PHOTO_STORY,
  STATUS_TEXT_STORY
} from '@metafox/story/constants';
import { AddFormContext } from '@metafox/story/context';
import { LineIcon } from '@metafox/ui';
import { Box, IconButton, Typography, styled } from '@mui/material';
import React from 'react';

export interface Props extends BlockViewProps {}

const name = 'AddForm';
const Root = styled(Box, {
  name,
  slot: 'Root',
  overridesResolver: (props, styles) => [styles.root],
  shouldForwardProp: props => props !== 'status'
})<{ status?: string }>(({ theme, status }) => ({
  display: 'flex',
  width: '100%',
  height: '100%',
  flexDirection: 'column',
  ...(status && {
    position: 'fixed',
    top: 0,
    left: 0,
    right: 0,
    zIndex: 1300
  })
}));

const ContainerView = styled(Box, {
  name,
  slot: 'containerView',
  shouldForwardProp: props => props !== 'status'
})<{ status?: string }>(({ theme, status }) => ({
  flex: 1,
  minWidth: 0,
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  width: '100%',
  height: '100%',
  ...(!status && {
    padding: theme.spacing(3)
  })
}));

const HeightFixed = styled('div', { name: 'HeightFixed' })<{
  heightApp?: number;
}>(({ heightApp }) => ({
  height: heightApp
}));

const RootButton = styled('div', { name: 'Root' })<{
  active?: boolean;
  scrollPosition?: number;
  minHeight?: number;
}>(({ theme, active, scrollPosition, minHeight }) => ({
  position: 'fixed',
  top: minHeight,
  left: 0,
  right: 0,
  zIndex: theme.zIndex.speedDial,
  transitionDuration: '.5s',
  backgroundColor: theme.palette.background.paper,
  boxShadow:
    '0px 2px 1px 0 rgba(0, 0, 0, 0.05), 0px -2px 1px 0 rgba(0, 0, 0, 0.05)',
  ...(active &&
    scrollPosition > minHeight && {
      top: 0
    })
}));

const BackButton = ({
  icon = 'ico-arrow-left',
  title = 'create_story'
}: any) => {
  const { navigate, i18n, useTheme } = useGlobal();
  const theme = useTheme();

  const handleClick = () => {
    navigate('/');
  };

  const scrollDirection = useScrollDirection();
  const [scrollPosition, setPosition] = React.useState(0);
  const minHeight = theme.appBarMobileConfig?.nav ?? 48;

  React.useEffect(() => {
    function updatePosition() {
      setPosition(window.pageYOffset);
    }
    window.addEventListener('scroll', updatePosition);
    updatePosition();

    return () => window.removeEventListener('scroll', updatePosition);
  }, []);

  const ref = React.useRef(null);

  const [heightApp, setHeightApp] = React.useState(0);

  React.useEffect(() => {
    setHeightApp(ref.current.clientHeight);
  }, []);

  return (
    <>
      <HeightFixed heightApp={heightApp} />
      <RootButton
        ref={ref}
        minHeight={minHeight}
        scrollPosition={scrollPosition}
        active={scrollDirection === 'down' ? true : false}
      >
        <IconButton
          size="small"
          role="button"
          id="back"
          data-testid="buttonBack"
          sx={{ alignSelf: 'flex-start', margin: 2, width: 'auto' }}
          onClick={handleClick}
        >
          <LineIcon icon={icon} sx={{ mr: 1 }} />
          <Typography variant="h4" color="text.primary">
            {i18n.formatMessage({
              id: title
            })}
          </Typography>
        </IconButton>
      </RootButton>
    </>
  );
};

export default function Base(props: Props) {
  const { jsxBackend, i18n, setNavigationConfirm, dialogBackend } = useGlobal();
  const MainViewForm = jsxBackend.get('story.block.mainViewForm');

  const [status, setStatus] = React.useState();
  const [filePhoto, setFilePhoto] = React.useState();
  const [uploading, setUploading] = React.useState(false);
  const [isDirty, setIsDirty] = React.useState(false);
  const [isSubmitting, setIsSubmitting] = React.useState(false);

  const confirmInfo = {
    message: i18n.formatMessage({
      id: 'if_you_leave_form_no_save_changed'
    }),
    title: i18n.formatMessage({
      id: 'are_you_sure'
    }),
    negativeButton: {
      label: i18n.formatMessage({
        id: 'cancel'
      })
    },
    positiveButton: {
      label: i18n.formatMessage({
        id: 'ok'
      })
    }
  };

  const checkSetStatus = async () => {
    let ok = true;

    if (isDirty) {
      ok = await dialogBackend.confirm(confirmInfo);
    }

    if (ok) {
      setStatus(undefined);
      setIsDirty(false);
    }
  };

  React.useEffect(() => {
    if (isSubmitting || !isDirty) return;

    if (status === STATUS_TEXT_STORY || status === STATUS_PHOTO_STORY) {
      setNavigationConfirm(isDirty, confirmInfo, () => {
        setStatus(undefined);
        setIsDirty(false);
      });
    }

    return () => {
      if (status === STATUS_TEXT_STORY || status === STATUS_PHOTO_STORY) {
        setNavigationConfirm && setNavigationConfirm(false);
      }
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [status, isDirty, isSubmitting]);

  React.useEffect(() => {
    if (status === STATUS_PHOTO_STORY && filePhoto) {
      setIsDirty(true);
    }
  }, [filePhoto, status]);

  return (
    <AddFormContext.Provider
      value={{
        status,
        setStatus,
        checkSetStatus,
        setFilePhoto,
        filePhoto,
        uploading,
        setUploading,
        setIsDirty,
        isDirty,
        isSubmitting,
        setIsSubmitting
      }}
    >
      <Root status={status}>
        {status ? null : <BackButton />}
        <ContainerView status={status}>
          <MainViewForm />
        </ContainerView>
      </Root>
    </AddFormContext.Provider>
  );
}
