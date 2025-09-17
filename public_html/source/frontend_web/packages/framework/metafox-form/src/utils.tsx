import {
  filter,
  isEmpty,
  isFunction,
  isInteger,
  isString,
  join,
  reduce
} from 'lodash';

export const setFieldValueWrapper = (setFieldValue, name) => value =>
  setFieldValue(name, value);

export const joinNames = (...args) =>
  join(
    filter(args, arg => (isString(arg) && arg) || isInteger(arg)),
    '.'
  );

export const getName = (type, name, ...args) =>
  ('field' === type && !name ? null : joinNames(...args, name));

/**
 * Handle Change and trigger callback if provided
 *
 * @param  {function} handler
 * @param  {object} formikProps
 * @param  {object} config
 * @param  {object} data
 * @param  {string} key
 * @return {void}
 */
export const changeHandler = (
  handler,
  formikProps,
  config,
  data,
  key = 'onChange'
) => {
  handler(data);
  isFunction(config[key]) && config[key](formikProps, config, data);
};

/**
 * Recurively prepare a complete validation schema array for yup-schema from individual
 * validation arrays passed to fields
 *
 * @param  {array} schema
 * @return {array}
 */
export const prepareValidationSchema = schema => {
  const {
    type,
    elements,
    name,
    renderer,
    validation
    // prefixNameToElement = false,
  } = schema;

  if ('field' === type && validation) {
    return {
      [name]: validation
    };
  }

  const elementSchema = reduce(
    elements,
    (result = {}, element, key) => ({
      ...result,
      ...prepareValidationSchema(element)
    }),
    {}
  );

  let result = {};

  if ('editable-grid' === renderer && !isEmpty(elementSchema)) {
    result[name] = [['array', [['object', elementSchema]]]];
  } else if (!isEmpty(elementSchema) && name) {
    result[name] = [['object', elementSchema]];
  } else {
    result = { ...result, ...elementSchema };
  }

  return result;
};
