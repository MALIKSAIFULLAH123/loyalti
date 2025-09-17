import comparePathName from './comparePathName';

describe('comparePathName', () => {
  it('empty', () => {
    expect(comparePathName(null, '/marketplace/invoice/')).toEqual(false);
    expect(comparePathName('/marketplace/invoice/', undefined)).toEqual(false);
    expect(comparePathName(null, null)).toEqual(false);
  });
  it('comparePathName normal', () => {
    expect(
      comparePathName('/marketplace/invoice', '/marketplace/invoice/')
    ).toEqual(true);
    expect(
      comparePathName('/marketplace/invoice/', '/marketplace/invoice')
    ).toEqual(true);
    expect(
      comparePathName('/marketplace/invoice//', '/marketplace/invoice')
    ).toEqual(false);
  });
});
