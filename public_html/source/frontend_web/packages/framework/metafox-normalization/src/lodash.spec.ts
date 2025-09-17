import { isArray, isObject } from 'lodash';

describe('Normalization', () => {
  it('_.isObject', () => {
    expect(isObject(null)).toBeFalsy();
    expect(isObject(undefined)).toBeFalsy();
    expect(isObject('simple string')).toBeFalsy();
    expect(isObject('lodash')).toBeFalsy();
    expect(isObject({})).toBeTruthy();
    expect(isObject(1)).toBeFalsy();
    expect(isObject(false)).toBeFalsy();
    expect(isObject(true)).toBeFalsy();
  });
  it('_.isArray', () => {
    expect(isArray(null)).toBeFalsy();
    expect(isArray(undefined)).toBeFalsy();
    expect(isArray('simple string')).toBeFalsy();
    expect(isArray('lodash')).toBeFalsy();
    expect(isArray({})).toBeFalsy();
    expect(isArray([134])).toBeTruthy();
    expect(isArray(1)).toBeFalsy();
    expect(isArray(false)).toBeFalsy();
    expect(isArray(true)).toBeFalsy();
  });
});
