describe('test jest with error', () => {
  it('test error function', () => {
    const fn = () => {
      throw new Error('Invalid error');
    };

    expect(() => fn()).toThrow('Invalid error');
  });
  it('test error promise', () => {
    const fn = () =>
      new Promise((resolve, reject) => {
        setTimeout(() => reject('Invalid Promise Error'), 1);
      });

    expect(fn).rejects.toEqual('Invalid Promise Error');
  });
});
