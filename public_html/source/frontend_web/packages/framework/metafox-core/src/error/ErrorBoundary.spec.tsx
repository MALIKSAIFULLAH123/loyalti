import { render, screen } from '@testing-library/react';
import * as React from 'react';
import ErrorBoundary from './ErrorBoundary';

describe('ErrorBoundary', () => {
  // turn off console error in this case.
  jest.spyOn(console, 'error').mockImplementation(jest.fn());

  it('<ErrorBoundary />', async () => {
    render(
      <ErrorBoundary>
        <h2 data-testid="correct">Work correctly!</h2>
      </ErrorBoundary>
    );
    expect(screen.getByTestId('correct')).toHaveTextContent('Work correctly!');
  });

  it('<ErrorBoundary />', () => {
    const ErrorComp = () => {
      throw new Error('test');
    };

    render(
      <ErrorBoundary>
        <ErrorComp />
      </ErrorBoundary>
    );

    expect(screen.getByTestId('errorBoundary')).toBeInTheDocument();
  });
});
