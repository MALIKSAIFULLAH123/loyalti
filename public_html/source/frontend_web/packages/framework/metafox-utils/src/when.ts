import { get, isArray, isInteger, isString } from 'lodash';

type Input = Record<string, any>;

type RuleName =
  | 'eq'
  | 'equals'
  | 'neq'
  | 'notEquals'
  | 'gt'
  | 'gte'
  | 'lt'
  | 'lte'
  | 'eqeqeq'
  | 'neqeqeq'
  | 'exists'
  | 'notExists'
  | 'lessThan'
  | 'strictEquals'
  | 'notStrictEquals'
  | 'lessOrEquals'
  | 'greater'
  | 'greaterOrEquals'
  | 'lengthGreater'
  | 'lengthGreaterOrEquals'
  | 'lengthLess'
  | 'lengthLessOrEquals'
  | 'lengthEquals'
  | 'truthy'
  | 'falsy'
  | 'int'
  | 'oneOf'
  | 'noneOf'
  | 'includes';

type ConditionName = 'and' | 'or';

type RuleFunction = (data: Input, key: string, value?: any) => boolean;

type LogicFunction = (data: boolean[]) => boolean;

type RuleDictionary = Record<RuleName, RuleFunction>;

type LogicDictionary = Partial<Record<ConditionName, LogicFunction>>;

const LogicDict: LogicDictionary = {
  and: function isAnd(data: boolean[]) {
    return !data.includes(false);
  },
  or: function isOr(data: boolean[]) {
    return data.includes(true);
  }
};

function exists(data: Input, expected: string, value?: any) {
  return expected !== undefined;
}

function notExists(data: Input, expected: string, value?: any) {
  return expected === undefined;
}

function oneOf(data: Input, expected: string, value?: any) {
  if (isArray(value)) {
    // eslint-disable-next-line eqeqeq
    return value.some(v => v == expected);
  } else if (isArray(expected)) {
    // eslint-disable-next-line eqeqeq
    return expected.some(v => v == value);
  }

  return false;
}

function noneOf(data: Input, expected: string, value?: any) {
  if (isArray(value)) {
    // eslint-disable-next-line eqeqeq
    return !value.some(v => v == expected);
  } else if (isArray(expected)) {
    // eslint-disable-next-line eqeqeq
    return !expected.some(v => v == value);
  }

  return false;
}

function lessOrEquals(data: Input, expected: string, value?: any) {
  return expected <= value;
}

function isLessThan(data: Input, expected: string, value?: any) {
  return expected < value;
}

function greaterOrEquals(data: Input, expected: string, value?: any) {
  return expected >= value;
}

function greater(data: Input, expected: string, value?: any) {
  return expected > value;
}

function strictEquals(data: Input, expected: string, value?: any) {
  return expected === value;
}

function notStrictEquals(data: Input, expected: string, value?: any) {
  return expected !== value;
}

function falsy(data: Input, expected: string, value?: any) {
  return !expected;
}

function truthy(data: Input, expected: string, value?: any) {
  return !!expected;
}

function notEquals(data: Input, expected: string, value?: any) {
  // eslint-disable-next-line
  return expected != value;
}

function equals(data: Input, expected: string, value?: any) {
  // eslint-disable-next-line
  return expected == value;
}

function lengthGreater(data: Input, expected: string, value?: any) {
  return expected?.length > value;
}

function lengthLess(data: Input, expected: string, value?: any) {
  // eslint-disable-next-line

  return expected?.length < value;
}

function lengthEquals(data: Input, expected: string, value?: any) {
  return expected?.length === value;
}

function lengthLessOrEquals(data: Input, expected: string, value?: any) {
  return expected?.length <= value;
}

function lengthGreaterOrEquals(data: Input, expected: string, value?: any) {
  return expected?.length >= value;
}

const RuleDict: RuleDictionary = {
  int: function _int(data: Input, key: string) {
    return isInteger(get(data, key)) || /^\d+$/.test(get(data, key));
  },
  includes: oneOf,
  oneOf,
  noneOf,
  eq: equals,
  equals,
  neq: notEquals,
  notEquals,
  truthy,
  falsy,
  eqeqeq: strictEquals,
  strictEquals,
  neqeqeq: notStrictEquals,
  notStrictEquals,
  gt: greater,
  greater,
  greaterOrEquals,
  gte: greaterOrEquals,
  lt: isLessThan,
  lessThan: isLessThan,
  lte: lessOrEquals,
  lessOrEquals,
  lengthGreater,
  lengthLess,
  lengthEquals,
  lengthLessOrEquals,
  lengthGreaterOrEquals,
  exists,
  notExists
};

function getActualValue(data: Input, name: any) {
  if (isString(name) && name.startsWith('$.')) {
    return get(data, name.substring(2));
  }

  return name;
}

function getExpectedValue(data: Input, name: any) {
  if (!isString(name)) return name;

  if (isString(name) && name.startsWith('$.')) {
    return get(data, name.substring(2));
  }

  return get(data, name);
}

function validate(data: Input, params: any[]) {
  if (RuleDict[params[0]]) {
    const actual = getActualValue(data, params[2]);
    const expected = getExpectedValue(data, params[1]);

    return RuleDict[params[0]](data, expected, actual);
  } else if (LogicDict[params[0]]) {
    return LogicDict[params[0]](
      params
        .filter((param, index) => 0 < index)
        .map(param => validate(data, param))
    );
  }

  return true;
}

/**
 * usages
 * []
 */
export default function when(data: Input, params: any[]) {
  return validate(data, params);
}
