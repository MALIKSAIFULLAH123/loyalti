import { GlobalProvider, Manager } from '@metafox/framework';
import { IntlProvider } from '@metafox/intl';
import { ThemeProvider } from '@metafox/layout';
import { render } from '@testing-library/react';
import * as React from 'react';
import FeaturedFlag from './FeaturedFlag';
import { FlagProps } from './Flag/Flag';

const MockInfoLoader = () => <div data-testid="infoLoader" />;

describe('FeaturedFlag', () => {
  const props: FlagProps | 'variant' | 'value' | 'color' = {
    variant: 'itemView',
    'data-testid': 'featured'
  };
  const manager = Manager.factory({});
  const usePreference = jest.fn().mockImplementation(() => ({}));
  const getConfig = jest.fn().mockImplementation(() => {});
  const mockOn = jest.fn();
  const mockOff = jest.fn();

  manager.use({
    usePreference,
    eventCenter: { on: mockOn, off: mockOff },
    constants: { isLayoutPreviewWindow: false },
    getConfig
  });

  it('render', async () => {
    const { container, getByTestId } = render(
      <GlobalProvider value={manager}>
        <IntlProvider>
          <ThemeProvider infoLoader={MockInfoLoader}>
            <FeaturedFlag value {...props} />
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
            <FeaturedFlag value color="primary" {...props} />
          </ThemeProvider>
        </IntlProvider>
      </GlobalProvider>
    );

    expect(getByTestId('featured')).toBeInTheDocument();
    expect(container).toMatchSnapshot();
  });

  it('render null', async () => {
    const { container } = render(<FeaturedFlag value={false} {...props} />);

    expect(container).toMatchSnapshot();
  });
});
