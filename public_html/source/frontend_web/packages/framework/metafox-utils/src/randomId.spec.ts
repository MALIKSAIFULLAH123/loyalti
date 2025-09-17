import { randomId } from './index';

describe('randomId', () => {
  it('with prefix', () => {
    expect(randomId('slot-').startsWith('slot-')).toBeTruthy();
    expect(randomId('slot-')).not.toEqual(randomId('slot-'));
  });

  it('without prefix', () => {
    expect(randomId()).not.toEqual(randomId());
  });
});
