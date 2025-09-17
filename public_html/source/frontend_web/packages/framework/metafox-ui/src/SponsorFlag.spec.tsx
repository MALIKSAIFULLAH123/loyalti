import { GlobalProvider, Manager } from '@metafox/framework';
import { IntlProvider } from '@metafox/intl';
import { ThemeProvider } from '@metafox/layout';
import { render } from '@testing-library/react';
import * as React from 'react';
import { FlagProps } from './Flag/Flag';
import SponsorFlag from './SponsorFlag';

const MockInfoLoader = () => <div data-testid="infoLoader" />;

describe('SponsorFlag', () => {
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
            <SponsorFlag value {...props} />
          </ThemeProvider>
        </IntlProvider>
      </GlobalProvider>
    );

    expect(getByTestId('sponsor')).toBeInTheDocument();
    expect(container).toMatchSnapshot();
  });

  it('render color primary', async () => {
    const { container, getByTestId } = render(
      <GlobalProvider value={manager}>
        <IntlProvider>
          <ThemeProvider infoLoader={MockInfoLoader}>
            <SponsorFlag value color="primary" {...props} />
          </ThemeProvider>
        </IntlProvider>
      </GlobalProvider>
    );

    expect(getByTestId('sponsor')).toBeInTheDocument();
    expect(container).toMatchSnapshot();
  });

  it('render null', async () => {
    const { container } = render(<SponsorFlag value={false} {...props} />);

    expect(container).toMatchSnapshot();
  });
});
