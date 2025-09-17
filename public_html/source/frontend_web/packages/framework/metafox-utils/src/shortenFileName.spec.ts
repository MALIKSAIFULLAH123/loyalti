import shortenFileName from './shortenFileName';

describe('shortenFileName', () => {
  it('shortenFileName 1', () => {
    expect(shortenFileName('abcabcabcabcabcabcabcabc.zip', 10)).toEqual(
      'abcabcabca...zip'
    );
  });
  it('shortenFileName 2', () => {
    expect(
      shortenFileName('abcdefabcdefabcdefabcdefabcdef.jpg.zip.xls', 10)
    ).toEqual('abcdefabcd...xls');
  });
  it('shortenFileName 3', () => {
    expect(shortenFileName('abcdefabcdefabcdefabcdefabcdefabcdef', 10)).toEqual(
      null
    );
  });
  it('shortenFileName 4', () => {
    expect(shortenFileName('abc.zip', 10)).toEqual('abc.zip');
  });
});
