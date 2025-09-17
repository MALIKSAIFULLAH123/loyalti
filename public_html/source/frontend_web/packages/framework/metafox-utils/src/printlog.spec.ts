import { printlog } from './index';

describe('printlog', () => {
  const consoleWarnMock = jest.spyOn(console, 'log').mockImplementation();

  it('check called', () => {
    printlog('my message');

    expect(consoleWarnMock).toBeCalledTimes(1);
    expect(consoleWarnMock).toBeCalledWith('my message');
  });

  afterAll(() => {
    consoleWarnMock.mockRestore();
  });
});
