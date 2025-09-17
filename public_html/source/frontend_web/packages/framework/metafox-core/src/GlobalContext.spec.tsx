import { Manager } from '@metafox/framework';
import { render, waitFor } from '@testing-library/react';
import * as React from 'react';
import { GlobalProvider } from './GlobalContext';
import useGlobal from './hooks/useGlobal';

describe('GlobalContext', () => {
  function MockComponent() {
    const manager = useGlobal();

    if (!manager) {
      return null;
    }

    return <div data-testid="mock" />;
  }
  it('+mount', async () => {
    const manager = Manager.factory({});
    const { getByTestId } = render(
      <GlobalProvider value={manager}>
        <MockComponent />
      </GlobalProvider>
    );

    await waitFor(() => {
      expect(getByTestId('mock')).toBeInTheDocument();
    });
  });
});
