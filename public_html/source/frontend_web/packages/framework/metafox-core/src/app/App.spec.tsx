import { render, waitFor } from '@testing-library/react';
import React from 'react';
import App from './App';

describe('App', () => {
  it('<App />', async () => {
    const { queryByTestId } = render(<App test />);

    await waitFor(
      () => {
        expect(queryByTestId('myComponent')).toBeNull();
      },
      { timeout: 100 }
    );
  });
});
