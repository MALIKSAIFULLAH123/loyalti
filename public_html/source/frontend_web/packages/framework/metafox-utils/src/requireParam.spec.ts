import { requireParam } from './index';

describe('requireParams', () => {
  it('truthy', () => {
    expect(
      requireParam({ username: true, password: true }, 'username, password')
    ).toBeUndefined();

    expect(
      requireParam({ username: true, password: true }, '')
    ).toBeUndefined();
  });
  it('error', () => {
    expect(() => {
      requireParam(
        { username: true, password: true },
        'username, password, grant_type'
      );
    }).toThrow('Missing parameters "grant_type"');

    expect(() => {
      requireParam(
        { username: true, password: true },
        'username, password, grant_type',
        'Missing dependencies'
      );
    }).toThrow('Missing dependencies "grant_type"');
  });
});
