import { useGlobal } from '@metafox/framework';
import {
  FormBuilder,
  FormSchemaShape,
  RemoteFormBuilderProps,
  MultiFormBuilderProcess
} from '@metafox/form';
import React, { useEffect, useState } from 'react';
import ErrorPage from '@metafox/core/pages/ErrorPage/Page';
import { isEmpty } from 'lodash';

export default function RemoteFormBuilder({
  dataSource,
  dialog,
  pageParams,
  onLoaded,
  onFailure,
  hideWhenError = false,
  allowRiskParams = false,
  loadingComponent: LoadingView,
  preventReload = false,
  ...rest
}: RemoteFormBuilderProps) {
  const { useFetchDetail, dialogBackend, jsxBackend, dispatch } = useGlobal();
  const [formSchema, setFormSchema] = useState<FormSchemaShape>();
  const [key, setKey] = useState('');
  const DefaultLoading = jsxBackend.get('form.DefaultLoading');
  const DialogLoadingComponent = jsxBackend.get('form.DialogLoadingComponent');
  const [data, loading, error, , meta] = useFetchDetail({
    dataSource,
    pageParams,
    allowRiskParams,
    forceReload: true,
    preventReload,
    cacheKey: key
  });

  const [multiStepForm, setMultiStepForm] = React.useState({});

  const onReset = () => {
    setKey(Math.random().toString());
  };

  const handleProcessMultiStep = ({ data, meta }) => {
    if (!data || !meta) return;

    const { formName, processChildId, previousProcessChildId } =
      meta?.continueAction?.payload || {};

    if (!formName) return;

    setMultiStepForm({ formName, processChildId });
    dispatch({
      type: 'formSchemas/multiForm/nextStep',
      payload: {
        formName,
        processChildId,
        data: {
          formSchema: { ...data, formName: processChildId },
          previousProcessChildId
        }
      }
    });
  };

  useEffect(() => {
    if (!error) return;

    if (!dialog) {
      dialogBackend.dismiss();
    }

    typeof onFailure === 'function' && onFailure(error);

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [error]);

  useEffect(() => {
    if (data) {
      setFormSchema(data);

      if (onLoaded) {
        onLoaded({ data, meta });
      }

      handleProcessMultiStep && handleProcessMultiStep({ data, meta });
    } else {
      setFormSchema(undefined);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [data, pageParams, loading]);

  if ((error && hideWhenError) || !dataSource) return null;

  const DefaultLoadingComponent = dialog
    ? DialogLoadingComponent
    : DefaultLoading;

  return (
    <ErrorPage
      loading={loading}
      error={error}
      loadingComponent={LoadingView || DefaultLoadingComponent}
    >
      {isEmpty(multiStepForm) ? (
        <FormBuilder
          {...rest}
          onReset={onReset}
          dialog={dialog}
          formSchema={formSchema}
          pageParams={pageParams}
        />
      ) : (
        <MultiFormBuilderProcess
          {...rest}
          multiStepFormInitial={multiStepForm}
          onReset={onReset}
          dialog={dialog}
          pageParams={pageParams}
        />
      )}
    </ErrorPage>
  );
}
