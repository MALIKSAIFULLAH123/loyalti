import { ConfirmParams, DialogItemContext } from '@metafox/dialog';
import { formSubmitAction, useGlobal } from '@metafox/framework';
import { toYup } from '@metafox/json2yup';
import { Formik, FormikHelpers, FormikProps } from 'formik';
import { debounce, get, isArray, isPlainObject, isString, set } from 'lodash';
import React, { useEffect } from 'react';
import Element from './Element';
import { FormSchemaProvider } from './FormSchema';
import transformSchema from './transformSchema';
import { FormBuilderProps, FormSchemaShape } from './types';

// number of millisecond to for debounce update.
// it's may be pass as a props of schema.
const WAIT_DEBOUNCE_SUBMIT: number = 200;

type FormikUpdateAwareProps = {
  schema: FormSchemaShape;
  onUpdate?: FormBuilderProps['onUpdate'];
  onChange?: FormBuilderProps['onChange'];
  formik?: FormikProps<unknown>;
  validationSchema?: Record<string, unknown>;
  dialogItem?: DialogItemContext;
  navigationConfirmWhenDirty?: ConfirmParams | boolean;
  children?: React.ReactNode;
};

const flatErrors = (errors: Record<string, string[]>): string => {
  const result = {};

  Object.keys(errors).forEach(name => {
    result[name] = isArray(errors[name])
      ? errors[name].join(', ')
      : errors[name].toString();
  });

  return Object.values(result).join(', ');
};

const FormikUpdateAware = (props: FormikUpdateAwareProps) => {
  const {
    children,
    onUpdate,
    onChange,
    schema,
    formik,
    dialogItem,
    navigationConfirmWhenDirty
  } = props;
  const { dialogBackend, formRefs, setNavigationConfirm, dispatch } =
    useGlobal();
  const {
    alertPreSubmitError,
    name,
    loadedAction,
    debounceSubmitTime = WAIT_DEBOUNCE_SUBMIT,
    submitOnValueChanged
  } = schema;

  const submitCount = React.useRef<number>(0);
  const { setUserConfirm } = dialogItem || {};

  useEffect(() => {
    if (name) {
      formRefs.tag(schema.name, formik);
    }

    if (
      alertPreSubmitError &&
      !formik.isValidating &&
      !formik.isSubmitting &&
      submitCount.current < formik.submitCount &&
      formik.errors
    ) {
      submitCount.current = formik.submitCount;
      const message = isString(alertPreSubmitError)
        ? alertPreSubmitError
        : flatErrors(formik.errors);

      if (message) {
        dialogBackend.alert({ message });
      }
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [formik.isValidating]);

  const touched = Object.keys(formik.touched).length > 0;

  useEffect(() => {
    if (!navigationConfirmWhenDirty) return;

    const shouldConfirm =
      /post|put/i.test(schema.method) &&
      formik.dirty &&
      touched &&
      !submitOnValueChanged;

    setNavigationConfirm &&
      setNavigationConfirm(shouldConfirm, navigationConfirmWhenDirty);

    if (dialogItem) {
      setUserConfirm &&
        setUserConfirm(shouldConfirm ? navigationConfirmWhenDirty : false);
    }

    return () => {
      if (shouldConfirm) {
        setNavigationConfirm && setNavigationConfirm(false);
      }
    };

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [formik.dirty, touched]);

  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const debounce_fn = React.useMemo(() => {
    return debounce((formik: FormikProps<object>) => {
      if (
        formik.isSubmitting ||
        (!formik.dirty && formik.submitCount === 0) ||
        !formik.touched
      ) {
        return;
      }

      formik.submitForm().finally(() => {
        // console.log('submit debounce_fn ?');
      });
    }, debounceSubmitTime);
  }, []);

  // loadedAction
  useEffect(() => {
    if (loadedAction) {
      dispatch({ type: loadedAction });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // WARNING:  do not add formik.touch to dependencies.
  useEffect(() => {
    if (schema.submitOnValueChanged) {
      debounce_fn(formik);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [formik.values]);

  /**
   * Callback if provided will be called when form values change
   */
  useEffect(() => {
    if (onUpdate) {
      onUpdate(formik);
    }

    if (onChange) {
      onChange({ values: formik.values, schema, form: formik });
    }
  }, [formik, formik.values, onChange, onUpdate, schema]);

  return (
    <Element config={schema} update={onUpdate} formik={formik}>
      {children}
    </Element>
  );
};

export default function FormBuilder<T extends object = object>({
  formSchema: formSchemaRaw,
  name: formName,
  noHeader,
  noTitle,
  noBreadcrumb,
  breadcrumbs,
  initialValues,
  isSidePlacement,
  onChange,
  onUpdate,
  onSubmitting,
  onSuccess,
  onFailure,
  onSubmit,
  onCancel,
  successAction,
  failureAction,
  onReset,
  changeEventName,
  dialog,
  submitAction,
  dialogItem: dialogItemProp,
  pageParams: pageParamsProps,
  resetFormWhenSuccess,
  reloadFormWhenSuccess,
  resetDirtyWhenSuccess,
  keepPaginationData,
  dialogEmbedItem,
  navigationConfirmWhenDirty: navigationConfirmWhenDirtyProps,
  disableFormOnSuccess,
  children,
  ref,
  fixedFooter,
  formContext,
  debounceSubmitTime
}: FormBuilderProps<T>) {
  const { eventCenter, dispatch, usePageParams, i18n, useDialog } = useGlobal();
  const dialogItemContext = useDialog();
  const dialogFn = dialogItemProp ?? dialogItemContext;
  const dialogItem = dialog ? dialogFn : dialogItemProp;
  const [preventSubmit, setPreventSubmit] = React.useState(false);
  const [initialValuesState, setInitialValuesState] = React.useState<
    Record<string, unknown> | undefined | object
  >(initialValues);
  const confirmInfo = {
    message: i18n.formatMessage({
      id: 'if_you_leave_form_no_save_changed'
    }),
    title: i18n.formatMessage({
      id: 'leave_page'
    }),
    negativeButton: {
      label: i18n.formatMessage({
        id: 'keep_editing'
      })
    },
    positiveButton: {
      label: i18n.formatMessage({
        id: 'leave'
      })
    }
  };
  const navigationConfirmWhenDirty =
    navigationConfirmWhenDirtyProps ??
    formSchemaRaw?.navigationConfirmWhenDirty ??
    confirmInfo;

  const pageParams = { ...usePageParams(), ...pageParamsProps };
  // check values here
  const values = {
    ...(initialValuesState || formSchemaRaw?.value)
  };

  /**
   * transform schema here
   */
  const formSchema: FormSchemaShape = React.useMemo(() => {
    if (formSchemaRaw) {
      const formSchema = transformSchema(formSchemaRaw, values, dialog);

      if (noHeader) formSchema.noHeader = true;

      if (noTitle) formSchema.noTitle = true;

      if (noBreadcrumb) formSchema.noBreadcrumb = true;

      if (fixedFooter) formSchema.fixedFooter = true;

      if (formContext) formSchema.formContext = formContext;

      if (debounceSubmitTime !== undefined)
        formSchema.debounceSubmitTime = debounceSubmitTime;

      if (breadcrumbs) {
        formSchema.breadcrumbs = breadcrumbs;
      }

      if (formName) {
        formSchema.name = formName;
      }

      if (isSidePlacement) {
        formSchema.isSidePlacement = isSidePlacement;
      }

      if (dialogEmbedItem) {
        formSchema.dialogEmbedItem = dialogEmbedItem;
      }

      if (formSchema.acceptPageParams?.length) {
        formSchema.acceptPageParams.forEach(x => {
          const v = get(pageParams, x);

          if (v !== undefined && v !== '') {
            set(values, x, v);
          }
        });
      }

      return formSchema;
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [formSchemaRaw, pageParams]);

  const validationSchema = React.useMemo(() => {
    const validation = formSchemaRaw?.validation;

    if (validation && isPlainObject(validation)) {
      try {
        return toYup(JSON.parse(JSON.stringify(formSchemaRaw.validation)));
      } catch (err) {
        //
      }
    }
  }, [formSchemaRaw]);

  // create default handler
  const handleChange = React.useCallback(
    (data: any) => {
      if (changeEventName) {
        eventCenter.dispatch(changeEventName, data);
      }

      if (onChange) onChange(data);
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [onChange]
  );

  // create default submit handler
  const handleSubmit = React.useCallback(
    (
      currentValues: Record<string, unknown>,
      form: FormikHelpers<Record<string, unknown>>
    ) => {
      if (!formSchema || preventSubmit) {
        form.setSubmitting(false);

        return;
      }

      const reset = resetFormWhenSuccess || formSchemaRaw?.resetFormWhenSuccess;
      const reload =
        reloadFormWhenSuccess || formSchemaRaw?.reloadFormWhenSuccess;
      const resetDirty =
        resetDirtyWhenSuccess || formSchemaRaw?.resetDirtyWhenSuccess;

      const handleSuccess = (...args) => {
        if (onSuccess) {
          onSuccess(...args);
        }

        if (reload) {
          // reload API form
          onReset && onReset();

          return;
        }

        if (resetDirty) {
          setInitialValuesState(currentValues);
        } else if (reset) {
          form.resetForm();
        }
      };

      dispatch(
        formSubmitAction(
          {
            initialValues: values,
            values: currentValues,
            form,
            dialog,
            submitAction: submitAction || formSchema.submitAction,
            submitUrl: formSchema.submitUrl,
            method: formSchema.method,
            action: formSchema.action,
            enctype: formSchema.enctype,
            dialogItem,
            pageParams,
            secondAction: formSchema.secondAction,
            successAction: successAction || formSchema.successAction,
            failureAction,
            preventReset: formSchema.preventReset,
            formSchema,
            disableFormOnSuccess,
            keepPaginationData:
              keepPaginationData || formSchema?.keepPaginationData
          },
          {
            onSuccess: handleSuccess,
            onFailure,
            onSubmitting
          }
        )
      );
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [dialog, formSchema, dialogItem, preventSubmit]
  );

  if (!formSchema) return null;

  formSchema.handleSubmitField = handleSubmit;
  formSchema.setPreventSubmit = setPreventSubmit;

  if (onCancel) {
    formSchema.onCancel = onCancel;
  }

  if (onReset) {
    formSchema.onReset = onReset;
  }

  return (
    <FormSchemaProvider value={formSchema}>
      <Formik
        validationSchema={validationSchema}
        initialValues={values}
        onSubmit={onSubmit ?? handleSubmit}
        enableReinitialize
        innerRef={ref}
      >
        {props => (
          <FormikUpdateAware
            schema={formSchema}
            onUpdate={onUpdate}
            onChange={handleChange}
            formik={props}
            dialogItem={dialogItem}
            navigationConfirmWhenDirty={navigationConfirmWhenDirty}
          >
            {children}
          </FormikUpdateAware>
        )}
      </Formik>
    </FormSchemaProvider>
  );
}
