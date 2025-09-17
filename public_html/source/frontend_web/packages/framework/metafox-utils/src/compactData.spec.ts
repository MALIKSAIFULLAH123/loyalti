import qs from 'query-string';
import compactData from './compactData';

describe('compactData', () => {
  it('compactData({},{})', () => {
    const a = compactData(
      {
        blog: 2,
        id: ':user_id',
        view: 'latest',
        type: ':type_id'
      },
      {
        user_id: 12
      }
    );

    expect(qs.stringify(a)).toEqual('blog=2&id=12&view=latest');
  });
  it('compactData(string,string)', () => {
    const a = compactData(
      'id=:user_id&view=latest&type=:type_id',
      'user_id=12'
    );
    expect(qs.stringify(a)).toEqual('id=12&view=latest');
  });
  it('mix empty result)', () => {
    const a = compactData('', '');
    expect(qs.stringify(a)).toEqual('');
  });

  it('mix empty result)', () => {
    const a = compactData(undefined, undefined);
    expect(qs.stringify(a)).toEqual('');
  });
  it('mix empty result)', () => {
    const a = compactData(null, null);
    expect(qs.stringify(a)).toEqual('');
  });
});
