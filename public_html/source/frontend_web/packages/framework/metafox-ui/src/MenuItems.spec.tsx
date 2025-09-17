import { GlobalProvider, Manager } from '@metafox/framework';
import { IntlProvider } from '@metafox/intl';
import JsxBackend from '@metafox/jsx';
import { ThemeProvider } from '@metafox/layout';
import { render } from '@testing-library/react';
import * as React from 'react';
import MenuItems from './MenuItems';

const MockInfoLoader = () => <div data-testid="infoLoader" />;

const MockMoreMenu = () => <div data-testid="moreMenu">more menu</div>;
const MockDivider = () => <div data-testid="divider">divider</div>;

describe('MenuItems', () => {
  const items = [
    {
      as: 'popover',
      testid: 'more menu',
      icon: 'ico-dottedmoreo',
      label: 'More',
      name: 'more_menu',
      ordering: 1
    },
    {
      as: 'divider',
      testid: 'divider',
      icon: '',
      label: null,
      name: 'divider',
      ordering: 2
    }
  ];

  const jsxBackend = new JsxBackend({
    'menuItem.as.popover': MockMoreMenu,
    'menuItem.as.divider': MockDivider
  });

  const manager = Manager.factory({});
  const usePreference = jest.fn().mockImplementation(() => ({}));
  const getConfig = jest.fn().mockImplementation(() => {});
  const mockOn = jest.fn();
  const mockOff = jest.fn();
  const mockRenderFn = jest.fn(({ component, props }) => {
    if (component == 'menuItem.as.popover') {
      return jsxBackend.render({ component: 'menuItem.as.popover', props });
    }

    if (component == 'menuItem.as.divider') {
      return jsxBackend.render({ component: 'menuItem.as.divider', props });
    }
  });

  manager.use({
    usePreference,
    eventCenter: { on: mockOn, off: mockOff },
    constants: { isLayoutPreviewWindow: false },
    getConfig,
    jsxBackend: { render: mockRenderFn }
  });

  it('render', async () => {
    const { container, getByTestId } = render(
      <GlobalProvider value={manager}>
        <IntlProvider>
          <ThemeProvider infoLoader={MockInfoLoader}>
            <MenuItems items={items} />
          </ThemeProvider>
        </IntlProvider>
      </GlobalProvider>
    );
    expect(getByTestId('moreMenu')).toBeInTheDocument();
    expect(getByTestId('divider')).toBeInTheDocument();
    expect(container).toMatchSnapshot();
  });
});
