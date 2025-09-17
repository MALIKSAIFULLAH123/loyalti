import { connect } from 'formik';
import React from 'react';
import { FormFieldProps, FormSchemaShape } from '.';
import ElementRenderer from './ElementRenderer';

type ElementRendererProps = {
  config?: any;
  update?: any;
  formik: any;
  schema?: FormSchemaShape;
};

const ElementComponent = ({ config, update, formik }: ElementRendererProps) => {
  const { configSource, dataSource } = config;
  const [hasLoadedConfig, setHasLoadedConfig] = React.useState(false);
  const [hasMounted, setHasMounted] = React.useState(false !== update);
  const [loadedConfig, setLoadedConfig] = React.useState(undefined);

  // eslint-disable-next-line
  const [hasLoadedData, setHasLoadedData] = React.useState(
    dataSource ? false : true
  );

  const loadDataAfter = (value: unknown) => setHasLoadedData(true);

  const loadConfigAfter = React.useCallback(
    (newConfig: Partial<FormFieldProps>) => {
      setHasLoadedConfig(true);
      setLoadedConfig(Object.assign({}, config, newConfig));
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    []
  );

  /**
   * On mount, load if there is a valid config source,
   * load the data from the config source and handle
   * whether future loads should be possible
   */
  React.useEffect(() => {
    if (!hasLoadedConfig && 'function' === typeof configSource) {
      configSource(formik, config)
        .then(loadConfigAfter)
        .catch(() => {
          // keep this line
        });
    }

    return () => setHasLoadedConfig(false);
  }, [config, configSource, formik, hasLoadedConfig, loadConfigAfter]);

  /**
   * If the value of update changes or if the form is currently validating (during submission),
   * set that value for hasMounted => true
   */
  React.useEffect(() => {
    setHasMounted(hasMounted => {
      if (hasMounted) {
        return hasMounted;
      }

      return false !== update || true === formik.isValidating;
    });
  }, [update, formik.isValidating]);

  /**
   * If a valid dataSource exists, call the dataSource when the element is mounted.
   * Also, call this when initialValues have changed and the component is mounted
   *
   * The latter is useful when you update the data on the server and reinitialize the
   * values of the form top-down where the value of this particular field comes from a dataSource
   */
  React.useEffect(() => {
    if ('function' === typeof dataSource && hasMounted) {
      dataSource(formik, config)
        .then(loadDataAfter)
        .catch(() => {
          // keep this line
        });
    }
  }, [hasMounted, formik.initialValues, dataSource, formik, config]);

  return (
    hasMounted && (
      <ElementRenderer config={loadedConfig || config} formik={formik} />
    )
  );
};

const Element = connect(
  React.memo(
    ElementComponent,
    ({ config, formik, update }, nextProps) =>
      update === nextProps.update &&
      config === nextProps.config &&
      formik.initialValues === nextProps.formik.initialValues &&
      formik.isValidating === nextProps.formik.isValidating &&
      formik.isSubmitting === nextProps.formik.isSubmitting
  )
);

export default Element;
