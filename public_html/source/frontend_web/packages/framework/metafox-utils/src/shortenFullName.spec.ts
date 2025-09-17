import { shortenFullName } from './index';

describe('shortenFullName', () => {
  it('empty', () => {
    expect(shortenFullName('')).toEqual('NA');
    expect(shortenFullName(null)).toEqual('NA');
    expect(shortenFullName(undefined)).toEqual('NA');
  });

  it('2 words', () => {
    expect(shortenFullName('Name Off')).toEqual('NO');
    expect(shortenFullName('Name On')).toEqual('NO');
    expect(shortenFullName('Jacky London')).toEqual('JL');
  });
});
