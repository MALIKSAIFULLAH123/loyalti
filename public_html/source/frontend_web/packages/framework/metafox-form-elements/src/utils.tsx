import {
  filter,
  isArray,
  isEmpty,
  isFunction,
  isInteger,
  isNaN,
  isNumber,
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
  'field' === type && !name ? null : joinNames(...args, name);

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

export function toLowerCaseNonAccentVietnamese(str) {
  str = str.toLowerCase();
  str = str.replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/g, 'a');
  str = str.replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/g, 'e');
  str = str.replace(/ì|í|ị|ỉ|ĩ/g, 'i');
  str = str.replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/g, 'o');
  str = str.replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/g, 'u');
  str = str.replace(/ỳ|ý|ỵ|ỷ|ỹ/g, 'y');
  str = str.replace(/đ/g, 'd');
  // Some system encode vietnamese combining accent as individual utf-8 characters
  str = str.replace(/\u0300|\u0301|\u0303|\u0309|\u0323/g, ''); // Huyền sắc hỏi ngã nặng
  str = str.replace(/\u02C6|\u0306|\u031B/g, ''); // Â, Ê, Ă, Ơ, Ư

  return str;
}

export function toSlugifyLowerNonAccent(str, separator = '-') {
  if (!str) return '';

  const convert = { '&': 'and' };

  str = str.replace(/[&]/g, (char: any) => convert[char]);
  str = toLowerCaseNonAccentVietnamese(str);
  str = str.normalize('NFKD').replace(/\p{Diacritic}/gu, '');
  str = str.replace(/[^a-zA-Z0-9 ]/g, '');
  str = str.trim().replaceAll(/\s+/g, separator);
  str = encodeURIComponent(str);

  return str;
}

export function toFindReplaceSlugify(
  str: any,
  findArr: any = [],
  separator: string = '-'
) {
  if (!str) return '';

  let stringRex = '[]';

  if (findArr && isArray(findArr)) {
    stringRex = `[${findArr.join('')}]`;
  }

  const rexFind = new RegExp(stringRex, 'g');

  str = str.trim().replaceAll(/\s+/g, separator);
  str = str.replaceAll(rexFind, separator);

  return str;
}

export function formatNumberSeparator({
  number,
  thousand_separator = ',',
  decimal_separator = '.',
  precision
}: any) {
  if (isNaN(number) || !isNumber(number)) {
    number = 0;
  }

  number = precision ? number.toFixed(precision) : number;

  const parts = number.toString().split('.');

  const numberPart = parts[0];
  const decimalPart = parts[1];
  const thousands = /\B(?=(\d{3})+(?!\d))/g;

  return (
    numberPart.replace(thousands, thousand_separator) +
    (decimalPart ? `${decimal_separator}${decimalPart}` : '')
  );
}
