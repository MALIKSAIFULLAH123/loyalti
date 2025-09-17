/**
 * @type: block
 * name: app.block.UpgradeWizard
 * bundle: admincp
 */

import { useUpgradeState } from '@metafox/app/hooks';
import { createBlock, useGlobal } from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { Steppers } from '@metafox/ui/steps';
import { getErrString } from '@metafox/utils';
import { Alert, CircularProgress } from '@mui/material';
import React from 'react';

function UpgradeWizard() {
  const { dispatch } = useGlobal();
  const { steps, loaded, error } = useUpgradeState();

  React.useEffect(() => {
    dispatch({ type: '@app/upgrade/start' });
    window.onbeforeunload = () => {
      return 'Do you want to skip this upgrade process';
    };

    return () => {
      window.onbeforeunload = undefined;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  if (!loaded) {
    return (
      <Block>
        <BlockHeader title={'Upgrade Wizard'} />
        <BlockContent>
          <CircularProgress variant="indeterminate" color="primary" />
        </BlockContent>
      </Block>
    );
  }

  if (error) {
    return (
      <Block>
        <BlockHeader title={'Upgrade Wizard'} />
        <BlockContent>
          <Alert severity="error" children={getErrString(error)} />
        </BlockContent>
      </Block>
    );
  }

  return (
    <Block>
      <BlockHeader title={'Upgrade Wizard'} />
      <BlockContent>
        <Steppers steps={steps} />
      </BlockContent>
    </Block>
  );
}

export default createBlock({
  extendBlock: UpgradeWizard,
  overrides: { noHeader: false }
});
