import { GlobalProvider, Manager } from '@metafox/framework';
import { ThemeProvider } from '@metafox/layout';
import { render } from '@testing-library/react';
import * as React from 'react';
import { MemoryRouter } from 'react-router';
import { ImageShape, TImagePlayerOverlay } from '../types';
import Image from './Image';
import PlayerOverlay from './PlayerOverlay';

const fallbackSrc = '/images/bg-img-1x1.png';
const MockInfoLoader = () => <div data-testid="infoLoader" />;

describe('Image', () => {
  const props: ImageShape = {
    'data-testid': 'image',
    alt: 'picture',
    src: fallbackSrc
  };
  const manager = Manager.factory({});
  const usePreference = jest.fn().mockImplementation(() => ({}));
  const getConfig = jest.fn().mockImplementation(() => {});
  const mockOn = jest.fn();
  const mockOff = jest.fn();

  manager.use({
    usePreference,
    eventCenter: { on: mockOn, off: mockOff },
    useLoggedIn: () => false,
    constants: { isLayoutPreviewWindow: false },
    getConfig
  });

  it('render', async () => {
    const { container } = render(
      <GlobalProvider value={manager}>
        <ThemeProvider infoLoader={MockInfoLoader}>
          <Image {...props} />
        </ThemeProvider>
      </GlobalProvider>
    );

    expect(container).toMatchSnapshot();
  });
  it('render ImageBlock aspectRatio:auto', async () => {
    const { container } = render(
      <GlobalProvider value={manager}>
        <ThemeProvider infoLoader={MockInfoLoader}>
          <MemoryRouter>
            <Image {...props} />
          </MemoryRouter>
        </ThemeProvider>
      </GlobalProvider>
    );

    expect(container).toMatchSnapshot();
  });
  it('render ImageBlock aspectRatio:169 playerOverlay', async () => {
    const propsItem: ImageShape = {
      ...props,
      aspectRatio: '169',
      playerOverlay: true,
      playerOverlayProps: {
        icon: 'ico-angle-right'
      }
    };
    const { container } = render(
      <GlobalProvider value={manager}>
        <ThemeProvider infoLoader={MockInfoLoader}>
          <MemoryRouter>
            <Image {...propsItem} />
          </MemoryRouter>
        </ThemeProvider>
      </GlobalProvider>
    );

    expect(container).toMatchSnapshot();
  });
  it('render ImageBlock aspectRatio:169 backgroundImage playerOverlay', async () => {
    const propsItem: ImageShape = {
      ...props,
      aspectRatio: '169',
      backgroundImage: true,
      playerOverlay: true,
      playerOverlayProps: {
        icon: 'ico-angle-right'
      }
    };
    const { container } = render(
      <GlobalProvider value={manager}>
        <ThemeProvider infoLoader={MockInfoLoader}>
          <MemoryRouter>
            <Image {...propsItem} />
          </MemoryRouter>
        </ThemeProvider>
      </GlobalProvider>
    );

    expect(container).toMatchSnapshot();
  });
  it('render link ', async () => {
    const propsItem = {
      ...props,
      link: '/home'
    };
    const { container } = render(
      <GlobalProvider value={manager}>
        <ThemeProvider infoLoader={MockInfoLoader}>
          <MemoryRouter>
            <Image {...propsItem} />
          </MemoryRouter>
        </ThemeProvider>
      </GlobalProvider>
    );

    expect(container).toMatchSnapshot();
  });
  it('render link host asModal', async () => {
    const propsItem = {
      ...props,
      host: 'host',
      asModal: true,
      link: '/home'
    };
    const { container } = render(
      <GlobalProvider value={manager}>
        <ThemeProvider infoLoader={MockInfoLoader}>
          <MemoryRouter>
            <Image {...propsItem} />
          </MemoryRouter>
        </ThemeProvider>
      </GlobalProvider>
    );

    expect(container).toMatchSnapshot();
  });
});

describe('PlayerOverlay', () => {
  it('render playerOverlay', async () => {
    const propsItem: TImagePlayerOverlay = {
      icon: 'ico-angle-right',
      size: 'lg'
    };
    const { container } = render(<PlayerOverlay {...propsItem} />);

    expect(container).toMatchSnapshot();
  });
});
