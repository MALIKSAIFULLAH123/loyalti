import colorHash from './colorHash';

describe('color hash', () => {
  it('metafox', () => {
    expect(colorHash.hex('metafox')).toEqual('#79bbd2');
    expect(colorHash.hex('')).toEqual('#535dac');
  });
});
