import { GlobalProvider, Manager } from '@metafox/framework';
import { IntlProvider } from '@metafox/intl';
import { ThemeProvider } from '@metafox/layout';
import { render } from '@testing-library/react';
import * as React from 'react';
import { MemoryRouter } from 'react-router';
import { FeedEmbedCardMediaProps } from '../types';
import FeedEmbedCardMedia from './FeedEmbedCardMedia';

const MockInfoLoader = () => <div data-testid="infoLoader" />;

describe('FeedEmbedCardMedia', () => {
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
    const { container } = render(
      <GlobalProvider value={manager}>
        <IntlProvider>
          <ThemeProvider infoLoader={MockInfoLoader}>
            <FeedEmbedCardMedia />
          </ThemeProvider>
        </IntlProvider>
      </GlobalProvider>
    );

    expect(container).toMatchSnapshot();
  });
  it('render have link, host props', async () => {
    const props: FeedEmbedCardMediaProps = {
      host: 'host',
      link: '/home'
    };
    const { container } = render(
      <GlobalProvider value={manager}>
        <IntlProvider>
          <ThemeProvider infoLoader={MockInfoLoader}>
            <MemoryRouter>
              <FeedEmbedCardMedia {...props} />
            </MemoryRouter>
          </ThemeProvider>
        </IntlProvider>
      </GlobalProvider>
    );

    expect(container).toMatchSnapshot();
  });
});
