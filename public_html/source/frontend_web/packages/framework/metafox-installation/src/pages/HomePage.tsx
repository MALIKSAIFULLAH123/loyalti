/**
 * @type: route
 * name: core.home
 * path: /install(/.+), /(.+)
 * chunkName: install
 * bundle: installation
 */
import { useGlobal } from '@metafox/framework';
import { Page } from '@metafox/layout';
import * as React from 'react';
import { findIndex } from 'lodash';
import { useInstallationState } from '@metafox/installation';

export default function HomePage(props) {
  const { createPageParams, dispatch, navigate } = useGlobal();
  const {
    steps,
    succeed,
    failed,
    forceStep,
    currentStep: _currentStep,
    verifiedStep
  } = useInstallationState();

  const pageParams = createPageParams<{ step: string }>(props, ({ step }) => {
    return {
      module_name: 'install',
      item_type: 'install',
      step: step ?? 'requirements'
    };
  });

  if (forceStep) {
    return <Page pageName={`install.${forceStep}`} pageParams={pageParams} />;
  }

  if (succeed) {
    return <Page pageName="install.installed" pageParams={pageParams} />;
  }

  if (failed) {
    return <Page pageName="install.failed" pageParams={pageParams} />;
  }

  const { step } = pageParams;

  // current step index
  let index0 = findIndex(steps, item => item.id === step);

  if (index0 < 0) {
    index0 = 0;
  }

  if (index0 > verifiedStep) {
    index0 = verifiedStep;
  }

  if (step !== steps[index0].id) {
    navigate({
      pathname: '/install',
      search: `step=${steps[index0].id}`
    });
  }

  if (index0 !== _currentStep) {
    dispatch({
      type: '@install/update',
      payload: { currentStep: index0 }
    });
  }

  return <Page pageName={`install.${step}`} pageParams={pageParams} />;
}
