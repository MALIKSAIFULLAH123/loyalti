/**
 * @type: ui
 * name: core.dialog.NotificationWebPush
 * bundle: web
 * chunkName: NotificationWebPush
 */

import { useGlobal, IS_ADMINCP } from '@metafox/framework';
import { styled, Button, Box, Typography } from '@mui/material';
import React from 'react';
import { useFirebaseFCM } from '@metafox/framework/firebase';

const Wrapper = styled(Box, { name: 'Wrapper', slot: 'root' })(({ theme }) => ({
  position: 'fixed',
  top: 16,
  width: 'auto',
  right: 16,
  zIndex: 1301
}));
const Inner = styled(Box, { name: 'Inner', slot: 'root' })(({ theme }) => ({
  minWidth: '100px',
  maxWidth: '25vw',
  padding: theme.spacing(2),
  backgroundColor: theme.palette.background.paper,
  color: theme.palette.text.primary,
  borderRadius: theme.shape.borderRadius,
  boxShadow: theme.shadows[20],
  [theme.breakpoints.down('md')]: {
    maxWidth: 'calc(100vw - 32px)'
  }
}));

const Content = styled(Box, { name: 'Content', slot: 'root' })(({ theme }) => ({
  paddingBottom: theme.spacing(1),
  marginBottom: theme.spacing(1),
  borderBottomStyle: 'solid',
  borderBottomWidth: 1,
  borderBottomColor: theme.palette.divider
}));

const ButtonWrapper = styled(Box, { name: 'ButtonWrapper', slot: 'root' })(
  ({ theme }) => ({
    display: 'flex',
    justifyContent: 'flex-end',
    button: {
      marginLeft: theme.spacing(1)
    }
  })
);

enum DisableMode {
  Default = '0',
  Skip = '1',
  Accept = '2'
}

export default function RequestNotificationBanner() {
  const { i18n, cookieBackend, dispatch, useLoggedIn, useIsMobile } =
    useGlobal();
  const [, handleGetToken, error] = useFirebaseFCM();
  const [show, setShow] = React.useState(true);
  const isSkiped = cookieBackend.get('fcm-notification') === DisableMode.Skip;
  const isAccepted =
    cookieBackend.get('fcm-notification') === DisableMode.Accept;
  const hasTokenRegister = !!cookieBackend.get('fcm-token');
  const loggedIn = useLoggedIn();
  const isMobile = useIsMobile(true);

  const registerTokenFCM = () => {
    if (handleGetToken) {
      handleGetToken((token: string) => {
        dispatch({ type: '@registerFCM', payload: { token } });
      });
    }
  };

  const setCookieDisableFcm = React.useCallback((x = DisableMode.Skip) => {
    cookieBackend.set('fcm-notification', x, {
      expires: 30
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const onCancel = React.useCallback(() => {
    setShow(false);
    setCookieDisableFcm();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const handleRequestBrowser = () => {
    if (!('Notification' in window)) {
      // Check if the browser supports notifications
      alert('This browser does not support desktop notification');
    } else if (Notification.permission === 'granted') {
      registerTokenFCM();
    } else if (Notification.permission !== 'denied') {
      // We need to ask the user for permission
      Notification.requestPermission().then(permission => {
        // If the user accepts, let's create a notification
        if (permission === 'granted') {
          registerTokenFCM();

          return;
        }

        setCookieDisableFcm(DisableMode.Skip);
      });
    } else {
      setCookieDisableFcm(DisableMode.Skip);
    }
  };

  const onUserConfirm = () => {
    setShow(false);
    setCookieDisableFcm(DisableMode.Accept);
    handleRequestBrowser();
  };

  React.useEffect(() => {
    // init request browser if accepted before but some reason can't get token
    if (IS_ADMINCP) return;

    if (isAccepted && !hasTokenRegister) {
      // Hotfix set Skip for just try once time if has error, remove on next version
      setCookieDisableFcm(DisableMode.Skip);

      handleRequestBrowser();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // dont show gdpr
  if (IS_ADMINCP) return null;

  if (isAccepted || isSkiped || !show || !loggedIn || error || isMobile)
    return null;

  return (
    <Wrapper>
      <Inner>
        <Content>
          <Typography variant="body1" component="span">
            {i18n.formatMessage({
              id: 'web_push_consent_desc'
            })}
          </Typography>
        </Content>
        <ButtonWrapper>
          <Button
            data-testid="cancelWebPush"
            role="button"
            tabIndex={2}
            variant="text"
            disableRipple
            size="small"
            color="primary"
            onClick={onCancel}
          >
            {i18n.formatMessage({ id: 'skip' })}
          </Button>
          <Button
            data-testid="allowWebPush"
            role="button"
            tabIndex={1}
            autoFocus
            variant="contained"
            disableRipple
            size="small"
            color="primary"
            onClick={onUserConfirm}
          >
            {i18n.formatMessage({ id: 'accept' })}
          </Button>
        </ButtonWrapper>
      </Inner>
    </Wrapper>
  );
}
