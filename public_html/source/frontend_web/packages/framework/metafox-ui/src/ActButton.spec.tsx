import { GlobalProvider, Manager } from '@metafox/framework';
import { ThemeProvider } from '@metafox/layout';
import * as React from 'react';
import renderer from 'react-test-renderer';
import ActButton from './ActButton';

const MockInfoLoader = () => <div data-testid="infoLoader" />;

describe('ActButton', () => {
  it('render', async () => {
    const props = {
      'data-testid': 'demo'
    };
    const manager = Manager.factory({});
    const usePreference = jest.fn().mockImplementation(() => ({}));
    const mockOn = jest.fn();
    const mockOff = jest.fn();

    manager.use({
      usePreference,
      useLoggedIn: () => false,
      eventCenter: { on: mockOn, off: mockOff },
      constants: { isLayoutPreviewWindow: false }
    });

    const loading = renderer.create(
      <GlobalProvider value={manager}>
        <ThemeProvider infoLoader={MockInfoLoader}>
          <ActButton {...props} />
        </ThemeProvider>
      </GlobalProvider>
    );
    expect(loading.toJSON()).toMatchSnapshot();
  });
  it('render icon', async () => {
    const props = {
      'data-testid': 'demo',
      icon: 'ico-comment-o'
    };
    const manager = Manager.factory({});
    const usePreference = jest.fn().mockImplementation(() => ({}));
    const mockOn = jest.fn();
    const mockOff = jest.fn();

    manager.use({
      usePreference,
      eventCenter: { on: mockOn, off: mockOff },
      constants: { isLayoutPreviewWindow: false }
    });

    const loading = renderer.create(
      <GlobalProvider value={manager}>
        <ThemeProvider infoLoader={MockInfoLoader}>
          <ActButton {...props} />
        </ThemeProvider>
      </GlobalProvider>
    );
    expect(loading.toJSON()).toMatchSnapshot();
  });
  it('render icon src', async () => {
    const props = {
      'data-testid': 'demo',
      icon: 'ico-comment-o',
      src: 'https://preview-foxsocial.phpfox.us/v5-backend/files/blog/planningyourpr_295522496.jpeg'
    };
    const manager = Manager.factory({});
    const usePreference = jest.fn().mockImplementation(() => ({}));
    const mockOn = jest.fn();
    const mockOff = jest.fn();

    manager.use({
      usePreference,
      eventCenter: { on: mockOn, off: mockOff },
      constants: { isLayoutPreviewWindow: false }
    });

    const loading = renderer.create(
      <GlobalProvider value={manager}>
        <ThemeProvider infoLoader={MockInfoLoader}>
          <ActButton {...props} />
        </ThemeProvider>
      </GlobalProvider>
    );
    expect(loading.toJSON()).toMatchSnapshot();
  });

  it('render mini label', async () => {
    const props = {
      'data-testid': 'demo',
      minimize: true,
      label: 'Comment/Edit'
    };

    const usePreference = jest.fn().mockImplementation(() => ({}));
    const mockOn = jest.fn();
    const mockOff = jest.fn();

    const manager = Manager.factory({}).use({
      usePreference,
      useLoggedIn: () => false,
      eventCenter: { on: mockOn, off: mockOff },
      constants: { isLayoutPreviewWindow: false }
    });
    const loading = renderer.create(
      <GlobalProvider value={manager}>
        <ThemeProvider infoLoader={MockInfoLoader}>
          <ActButton {...props} />
        </ThemeProvider>
      </GlobalProvider>
    );
    expect(loading.toJSON()).toMatchSnapshot();
  });
  it('render icon label', async () => {
    const props = {
      'data-testid': 'demo',
      icon: 'ico-comment-o',
      label: 'Comment/Edit'
    };
    const manager = Manager.factory({});
    const usePreference = jest.fn().mockImplementation(() => ({}));
    const mockOn = jest.fn();
    const mockOff = jest.fn();

    manager.use({
      usePreference,
      useLoggedIn: () => false,
      eventCenter: { on: mockOn, off: mockOff },
      constants: { isLayoutPreviewWindow: false }
    });

    const loading = renderer.create(
      <GlobalProvider value={manager}>
        <ThemeProvider infoLoader={MockInfoLoader}>
          <ActButton {...props} />
        </ThemeProvider>
      </GlobalProvider>
    );
    expect(loading.toJSON()).toMatchSnapshot();
  });
});
