import { useGlobal } from '@metafox/framework';
import React from 'react';
import {
  HelpBlock,
  InstallMenu,
  Panel,
  PanelBody,
  PanelContent,
  PanelFooter,
  PanelHeader,
  useInstallationState
} from '@metafox/installation';
import { SmartFormBuilder } from '@metafox/form';
import { LoadingButton } from '@mui/lab';

const InstallForm = ({ formSchema }) => {
  const { formRefs, dispatch } = useGlobal();
  const [loading, setLoading] = React.useState<boolean>(false);
  const initialValues = useInstallationState();

  const goNext = values => {
    dispatch({ type: '@install/update', payload: values });
    dispatch({ type: '@install/next', payload: {} });
  };

  const handleFailure = React.useCallback(() => {
    setLoading(false);
  }, []);

  const onSubmitting = () => {
    setLoading(true);
  };

  const handleContinue = () => {
    formRefs.get(formSchema.name).submitForm();
  };

  return (
    <Panel>
      <PanelHeader />
      <PanelBody hasMenu>
        <InstallMenu />
        <PanelContent>
          <SmartFormBuilder
            initialValues={initialValues}
            onSuccess={goNext}
            onFailure={handleFailure}
            onSubmitting={onSubmitting}
            formSchema={formSchema as unknown}
            resetDirtyWhenSuccess
          />
          <HelpBlock />
        </PanelContent>
      </PanelBody>
      <PanelFooter>
        <LoadingButton
          variant="contained"
          color="primary"
          loading={loading}
          loadingPosition="center"
          data-testid="buttonContinue"
          onClick={handleContinue}
        >
          Continue
        </LoadingButton>
      </PanelFooter>
    </Panel>
  );
};
export default InstallForm;
