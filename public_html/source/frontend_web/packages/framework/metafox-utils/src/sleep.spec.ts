import sleep from './sleep';

describe('sleep', () => {
  it('sleep(0)', async () => {
    const dt = await sleep(100).then(() => 'Callback returned');
    expect(dt).toEqual('Callback returned');
  });
});
