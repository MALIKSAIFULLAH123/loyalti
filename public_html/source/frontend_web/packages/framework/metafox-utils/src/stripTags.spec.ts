import stripTags from './stripTags';

describe('stripTags', () => {
  it('mui-rte stripTags', () => {
    expect(stripTags('<p>b</p>')).toEqual('b');
    expect(stripTags('<p></p>')).toEqual('');
  });
});
