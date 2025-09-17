/**
 * @type: siteDock
 * name: PWA
 */

import { IS_ADMINCP, MFOX_FAVICON_URL, useGlobal } from '@metafox/framework';
import React from 'react';
import { styled, Box, Typography, Button } from '@mui/material';
import { detect } from 'detect-browser';

const name = 'PWA';

const Logo = styled('img', {
  name,
  slot: 'logo'
})(({ theme }) => ({
  width: theme.spacing(6),
  height: theme.spacing(6),
  marginRight: theme.spacing(2)
}));

const Wrapper = styled(Box, { name, slot: 'Wrapper' })(({ theme }) => ({
  position: 'fixed',
  top: 16,
  width: 'auto',
  right: 16,
  zIndex: 1301
}));
const Inner = styled(Box, { name, slot: 'Inner' })(({ theme }) => ({
  minWidth: '300px',
  maxWidth: '400px',
  padding: theme.spacing(2),
  backgroundColor: theme.palette.background.paper,
  color: theme.palette.text.primary,
  borderRadius: theme.shape.borderRadius,
  boxShadow: theme.shadows[20],
  [theme.breakpoints.down('md')]: {
    maxWidth: 'calc(100vw - 32px)'
  }
}));

const TopContent = styled(Box, { name, slot: 'TopContent' })(({ theme }) => ({
  paddingBottom: theme.spacing(1),
  marginBottom: theme.spacing(1),
  borderBottomStyle: 'solid',
  borderBottomWidth: 1,
  borderBottomColor: theme.palette.divider
}));

const Content = styled(Box, { name, slot: 'Content' })(({ theme }) => ({
  paddingBottom: theme.spacing(1),
  marginBottom: theme.spacing(1),
  borderBottomStyle: 'solid',
  borderBottomWidth: 1,
  borderBottomColor: theme.palette.divider
}));

const ButtonWrapper = styled(Box, { name, slot: 'ButtonWrapper' })(
  ({ theme }) => ({
    display: 'flex',
    justifyContent: 'flex-end',
    button: {
      marginLeft: theme.spacing(1)
    }
  })
);

export default function GDPR() {
  const { i18n, localStore, useIsMobile, getSetting } = useGlobal();
  const { name: browserName } = detect() || {};
  const isMobile = useIsMobile(true);
  const disabledCache =
    localStore.get('pwa-hide-install') === '1' ||
    isMobile ||
    browserName !== 'chrome';
  const pwaSetting = getSetting<any>('core.pwa', {});
  const { enable, app_name, app_description, install_description } = pwaSetting;
  const disabled = disabledCache || !enable;
  const [show, setShow] = React.useState(false);

  React.useEffect(() => {
    if (window?.hasPWA && !disabled) {
      setShow(true);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [window?.hasPWA, disabled]);

  const hidePWA = () => {
    localStore.set('pwa-hide-install', '1');
    setShow(false);
  };

  const onInstall = () => {
    window?.promptPWA?.prompt && window.promptPWA.prompt();
    hidePWA();
  };

  const onCancel = () => {
    localStore.set('pwa-hide-install', '1');
    hidePWA();
  };

  // dont show
  if (IS_ADMINCP || disabled || !show) return null;

  return (
    <Wrapper>
      <Inner>
        <TopContent>
          <Box sx={{ display: 'flex' }} mb={1.5}>
            <Logo src={MFOX_FAVICON_URL} />
            <Box>
              <Typography variant="body1" component="div">
                {app_name}
              </Typography>
              <Typography variant="body1" component="div">
                {window?.location?.host}
              </Typography>
            </Box>
          </Box>
          {app_description ? (
            <Typography variant="body1" component="span">
              {app_description}
            </Typography>
          ) : null}
        </TopContent>
        {install_description ? (
          <Content>
            <Typography variant="body1" component="span">
              {install_description}
            </Typography>
          </Content>
        ) : null}
        <ButtonWrapper>
          <Button
            data-testid="closePwa"
            role="button"
            tabIndex={2}
            variant="text"
            disableRipple
            size="small"
            color="primary"
            onClick={onCancel}
          >
            {i18n.formatMessage({ id: 'close' })}
          </Button>
          <Button
            data-testid="installPwa"
            role="button"
            tabIndex={1}
            autoFocus
            variant="contained"
            disableRipple
            size="small"
            color="primary"
            onClick={onInstall}
          >
            {i18n.formatMessage({ id: 'install' })}
          </Button>
        </ButtonWrapper>
      </Inner>
    </Wrapper>
  );
}
