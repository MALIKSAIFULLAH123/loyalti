import { GlobalProvider, Manager } from '@metafox/framework';
import { render, waitFor } from '@testing-library/react';
import * as React from 'react';
import { MemoryRouter } from 'react-router-dom';
import DialogContainer from './DialogContainer';

const mockUseLocationValue = {
  pathname: '/',
  search: '',
  hash: '',
  state: null
};

jest.mock('react-router', () => ({
  ...(jest.requireActual('react-router') as {}),
  useLocation: jest.fn().mockImplementation(() => {
    return mockUseLocationValue;
  })
}));

describe('DialogContainer', () => {
  it('mockGlobal', async () => {
    let callback: any;
    const mockSubscribeFn = jest.fn(cb => {
      callback = cb;
    });
    const mockRenderFn = jest.fn(() => <div id="test" data-testid="test" />);

    const manager = Manager.factory({}).use({
      jsxBackend: { render: mockRenderFn },
      dialogBackend: { subscribe: mockSubscribeFn, dismiss: () => {} }
    });
    const { getByTestId } = render(
      <GlobalProvider value={manager}>
        <MemoryRouter>
          <DialogContainer />
        </MemoryRouter>
      </GlobalProvider>
    );

    expect(mockSubscribeFn).toBeCalledWith(expect.anything());
    expect(mockSubscribeFn).toBeCalledTimes(1);
    expect(mockRenderFn).toBeCalledTimes(0);
    expect(callback).toBeDefined();

    const items = [1];

    await waitFor(() => {
      callback(items);
      callback(items);
      callback(items);
      expect(getByTestId('test'));
      expect(mockRenderFn).toBeCalled();
    });
  });

  afterAll(() => {
    jest.resetAllMocks();
  });
});
