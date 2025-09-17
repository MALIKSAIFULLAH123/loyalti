import { GlobalProvider, Manager } from '@metafox/framework';
import { IntlProvider } from '@metafox/intl';
import { ThemeProvider } from '@metafox/layout';
import { render } from '@testing-library/react';
import * as React from 'react';
import Flag, { FlagProps } from './Flag';

const MockInfoLoader = () => <div data-testid="infoLoader" />;

describe('Flag', () => {
  const props: FlagProps = {
    'data-testid': 'featured',
    type: 'is_featured',
    color: 'white',
    variant: 'feedView'
  };
  const usePreference = jest.fn().mockImplementation(() => ({}));
  const getConfig = jest.fn().mockImplementation(() => {});
  const mockOn = jest.fn();
  const mockOff = jest.fn();

  const manager = Manager.factory({}).use({
    usePreference,
    useLoggedIn: () => false,
    eventCenter: { on: mockOn, off: mockOff },
    constants: { isLayoutPreviewWindow: false },
    getConfig
  });

  it('render', async () => {
    const { container, getByTestId } = render(
      <GlobalProvider value={manager}>
        <IntlProvider>
          <ThemeProvider infoLoader={MockInfoLoader}>
            <Flag value {...props} />
          </ThemeProvider>
        </IntlProvider>
      </GlobalProvider>
    );

    expect(getByTestId('featured')).toBeInTheDocument();
    expect(container).toMatchSnapshot();
  });

  it('render color primary', async () => {
    const { container, getByTestId } = render(
      <GlobalProvider value={manager}>
        <IntlProvider>
          <ThemeProvider infoLoader={MockInfoLoader}>
            <Flag {...props} value color="primary" />
          </ThemeProvider>
        </IntlProvider>
      </GlobalProvider>
    );

    expect(getByTestId('featured')).toBeInTheDocument();
    expect(container).toMatchSnapshot();
  });

  it('render null', async () => {
    const { container } = render(
      <GlobalProvider value={manager}>
        <IntlProvider>
          <ThemeProvider infoLoader={MockInfoLoader}>
            <Flag {...props} value={false} color="primary" />
          </ThemeProvider>
        </IntlProvider>
      </GlobalProvider>
    );

    expect(container).toMatchSnapshot();
  });
});
