import when from './when';

describe('when/0', () => {
  it('when/ck', () => {
    expect(when({ x: 1 }, ['ck', 'x', 1])).toBeTruthy();
    expect(when({ x: 1 }, [])).toBeTruthy();
  });
  it('when/eqeqeq', () => {
    expect(when({ x: 1 }, ['eqeqeq', 'x', 1])).toBeTruthy();
    expect(when({ x: 1 }, ['eqeqeq', 'x', 0])).toBeFalsy();
    expect(when({ x: 1 }, ['eqeqeq', 'x', '1'])).toBeFalsy();
    expect(when({ x: 1 }, ['eqeqeq', 'x', '0'])).toBeFalsy();
  });
  it('when/eq', () => {
    expect(when({ x: 1 }, ['eq', 'x', 1])).toBeTruthy();
    expect(when({ x: 1 }, ['eq', 'x', 0])).toBeFalsy();
    expect(when({ x: 1 }, ['eq', 'x', '1'])).toBeTruthy();
    expect(when({ x: 1 }, ['eq', 'x', '0'])).toBeFalsy();
  });
  it('when/lt', () => {
    expect(when({ x: 1 }, ['lt', 'x', 2])).toBeTruthy();
    expect(when({ x: 1 }, ['lt', 'x', 0])).toBeFalsy();
    expect(when({ x: 1 }, ['lt', 'x', '2'])).toBeTruthy();
    expect(when({ x: 1 }, ['lt', 'x', '0'])).toBeFalsy();
  });
  it('when/lte', () => {
    expect(when({ x: 1 }, ['lte', 'x', 1])).toBeTruthy();
    expect(when({ x: 1 }, ['lte', 'x', 0])).toBeFalsy();
    expect(when({ x: 1 }, ['lte', 'x', -1])).toBeFalsy();
    expect(when({ x: 1 }, ['lte', 'x', '1'])).toBeTruthy();
    expect(when({ x: 1 }, ['lte', 'x', '0'])).toBeFalsy();
    expect(when({ x: 1 }, ['lte', 'x', '-1'])).toBeFalsy();
  });
  it('when/gt', () => {
    expect(when({ x: 1 }, ['gt', 'x', 2])).toBeFalsy();
    expect(when({ x: 1 }, ['gt', 'x', 1])).toBeFalsy();
    expect(when({ x: 1 }, ['gt', 'x', 0])).toBeTruthy();
    expect(when({ x: 1 }, ['gt', 'x', -1])).toBeTruthy();
    expect(when({ x: 1 }, ['gt', 'x', '2'])).toBeFalsy();
    expect(when({ x: 1 }, ['gt', 'x', '1'])).toBeFalsy();
    expect(when({ x: 1 }, ['gt', 'x', '0'])).toBeTruthy();
    expect(when({ x: 1 }, ['gt', 'x', '-1'])).toBeTruthy();
  });
  it('when/gte', () => {
    expect(when({ x: 1 }, ['gte', 'x', 2])).toBeFalsy();
    expect(when({ x: 1 }, ['gte', 'x', 1])).toBeTruthy();
    expect(when({ x: 1 }, ['gte', 'x', 0])).toBeTruthy();
    expect(when({ x: 1 }, ['gte', 'x', -1])).toBeTruthy();
    expect(when({ x: 1 }, ['gte', 'x', '2'])).toBeFalsy();
    expect(when({ x: 1 }, ['gte', 'x', '1'])).toBeTruthy();
    expect(when({ x: 1 }, ['gte', 'x', '0'])).toBeTruthy();
    expect(when({ x: 1 }, ['gte', 'x', '-1'])).toBeTruthy();
  });
});

describe('when/1', () => {
  it('+and', () => {
    expect(when({ a: 1, b: 2, c: 3 }, ['and'])).toBeTruthy();

    expect(when({ a: 1, b: 2, c: 3 }, ['and', []])).toBeTruthy();

    expect(when({ a: 1, b: 2, c: 3 }, ['and', ['ck'], []])).toBeTruthy();

    expect(
      when({ a: 1, b: 2, c: 3 }, [
        'and',
        ['eq', 'a', 1],
        ['eq', 'b', '2'],
        ['eq', 'c', '3']
      ])
    ).toBeTruthy();

    expect(
      when({ a: 1, b: 2, c: 3 }, [
        'and',
        ['eq', 'a', 1],
        ['eq', 'b', 3],
        ['eq', 'c', '3']
      ])
    ).toBeFalsy();

    expect(
      when({ a: 1, b: 2, c: 3 }, [
        'and',
        ['eq', 'a', 1],
        ['eq', 'b', '2'],
        ['eq', 'c', 4]
      ])
    ).toBeFalsy();
  });
  it('+or', () => {
    expect(when({ a: 1, b: 2, c: 3 }, ['or'])).toBeFalsy();

    expect(
      when({ a: 1, b: 2, c: 3 }, [
        'or',
        ['eq', 'a', 1],
        ['eq', 'b', '2'],
        ['eq', 'c', '3']
      ])
    ).toBeTruthy();

    expect(
      when({ a: 1, b: 2, c: 3 }, [
        'or',
        ['eq', 'a', 1],
        ['eq', 'b', 3],
        ['eq', 'c', '3']
      ])
    ).toBeTruthy();

    expect(
      when({ a: 1, b: 2, c: 3 }, [
        'or',
        ['eq', 'a', 1],
        ['eq', 'b', '2'],
        ['eq', 'c', 4]
      ])
    ).toBeTruthy();

    expect(
      when({ a: 1, b: 2, c: 3 }, [
        'or',
        ['eq', 'a', '4'],
        ['eq', 'b', '5'],
        ['eq', 'c', '6']
      ])
    ).toBeFalsy();

    expect(
      when({ a: 1, b: 2, c: [1, 2, 3] }, ['lengthLess', 'c', 4])
    ).toBeTruthy();

    expect(
      when({ a: 1, b: 2, c: [1, 2, 3] }, ['lengthLess', 'c', 2])
    ).toBeFalsy();

    expect(
      when({ a: 1, b: 2, c: [1, 2, 3] }, ['lengthLess', 'c', 3])
    ).toBeFalsy();

    expect(
      when({ a: 1, b: 2, c: [1, 2, 3] }, ['lengthLessOrEqual', 'c', 3])
    ).toBeTruthy();

    expect(
      when({ a: 1, b: 2, c: [1, 2, 3] }, ['lengthLessOrEqual', 'c', 4])
    ).toBeTruthy();

    expect(
      when({ a: 1, b: 2, c: [1, 2, 3] }, ['lengthLessOrEqual', 'c', 2])
    ).toBeTruthy();

    expect(
      when({ a: 1, b: 2, c: [1, 2, 3] }, ['lengthGreater', 'c', 2])
    ).toBeTruthy();

    expect(
      when({ a: 1, b: 2, c: [1, 2, 3] }, ['lengthGreater', 'c', 3])
    ).toBeFalsy();

    expect(
      when({ a: 1, b: 2, c: [1, 2, 3] }, ['lengthGreater', 'c', 4])
    ).toBeFalsy();

    expect(
      when({ a: 1, b: 2, c: [1, 2, 3] }, ['lengthGreaterOrEquals', 'c', 6])
    ).toBeFalsy();

    expect(
      when({ a: 1, b: 2, c: [1, 2, 3] }, ['lengthGreaterOrEquals', 'c', 3])
    ).toBeTruthy();

    expect(
      when({ a: 1, b: 2, c: [1, 2, 3] }, ['lengthGreaterOrEquals', 'c', 2])
    ).toBeTruthy();
  });
});
