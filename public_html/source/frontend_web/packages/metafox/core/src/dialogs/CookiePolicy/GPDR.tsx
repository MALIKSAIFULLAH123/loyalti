/**
 * @type: siteFixedDock
 * name: GDPR
 */

import { IS_ADMINCP, useGlobal } from '@metafox/framework';
import { Button } from '@mui/material';
import React from 'react';
import useStyles from './styles';
import { HtmlViewerWrapper } from '@metafox/ui';
import HtmlViewer from '@metafox/html-viewer';

export default function GDPR() {
  const classes = useStyles();
  const { cookieBackend, i18n, getSetting } = useGlobal();
  const [userConfirm, setUserConfirm] = React.useState<boolean>(false);
  const enabled = cookieBackend.get('gdpr');

  const onUserConfirm = () => {
    cookieBackend.set('gdpr', '1', { expires: 365 });
    setUserConfirm(true);
  };

  // dont show gdpr
  if (IS_ADMINCP) return null;

  if (!getSetting('core.general.gdpr_enabled')) return null;

  if (enabled || userConfirm) return null;

  return (
    <div className={classes.root}>
      <div className={classes.contentOuter}>
        <div className={classes.contentInner}>
          <h2 className={classes.title}>
            {i18n.formatMessage({ id: 'cookie_gdpr_title' })}
          </h2>
          <div className={classes.description}>
            <HtmlViewerWrapper mt={0}>
              <HtmlViewer
                html={i18n.formatMessage({ id: 'cookie_gdpr_message' })}
              />
            </HtmlViewerWrapper>
          </div>
        </div>
        <Button
          className={classes.btnConfirm}
          variant="outlined"
          size="medium"
          onClick={onUserConfirm}
        >
          {i18n.formatMessage({ id: 'accept' })}
        </Button>
      </div>
    </div>
  );
}
