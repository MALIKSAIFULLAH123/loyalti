import getFileExtension from './getFileExtension';

describe('getFileExtension', () => {
  it('getFileExtension 1', () => {
    expect(getFileExtension('abc.zip')).toEqual('zip');
  });
  it('getFileExtension 2', () => {
    expect(getFileExtension('abcdef.jpg.zip.xls')).toEqual('xls');
  });
  it('getFileExtension 3', () => {
    expect(getFileExtension('abcdef')).toEqual(null);
  });
});
