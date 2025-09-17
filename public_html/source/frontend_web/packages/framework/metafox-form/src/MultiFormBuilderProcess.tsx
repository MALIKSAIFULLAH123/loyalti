import { FormBuilder } from '@metafox/form';
import { useGlobal } from '@metafox/framework';
import React from 'react';
import { useSelector } from 'react-redux';
import { get, isEmpty, isFunction } from 'lodash';

export default function MultiFormBuilder(props) {
  const { onSuccess, multiStepFormInitial, ...rest } = props || {};
  const { formName, processChildId: processChildIdInitial } =
    multiStepFormInitial || {};
  const { dispatch } = useGlobal();
  const [currentForm, setCurrentForm] = React.useState(processChildIdInitial);
  const formValues = useSelector(state => get(state, `formValues.${formName}`));
  const formSchemas = useSelector(state =>
    get(state, `formSchemas.${formName}`)
  );
  const formSchemasOrigin = get(formSchemas, `${currentForm}.formSchema`);

  React.useEffect(() => {
    return () => {
      // cleanup data when exit multistep process
      if (formName) {
        dispatch({
          type: 'formValues/onDestroy',
          payload: {
            formName
          }
        });
      }
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  if (!formSchemasOrigin) return null;

  const formSchema = Object.assign({}, formSchemasOrigin, {
    handleBackForm: x => {
      const { payload } = x || {};
      setCurrentForm(payload?.previousProcessChildId);
    }
  });

  // get data processChildId chain
  const hasForm = formValues && formSchemas && currentForm && formSchema;
  let initialValues = hasForm ? formValues[currentForm] : {};

  // preventChainData: not collect data from previous form
  if (hasForm && !formSchema?.preventChainData) {
    const value = get(formValues, currentForm) || {};
    const previousForm = get(
      formSchemas[currentForm],
      'previousProcessChildId'
    );
    const valuePrevious = get(formValues, previousForm) || {};

    initialValues = Object.assign({}, valuePrevious, value);
  }

  const handleSuccess = (values, meta) => {
    const nextProcess = meta?.continueAction?.payload?.processChildId;

    if (nextProcess) {
      setCurrentForm(nextProcess);

      return;
    }

    // Finish
    isFunction(onSuccess) && onSuccess(values, meta);
  };

  if (isEmpty(formSchema)) return null;

  return (
    <FormBuilder
      {...rest}
      key={currentForm}
      formSchema={formSchema}
      initialValues={initialValues}
      onSuccess={handleSuccess}
    />
  );
}
