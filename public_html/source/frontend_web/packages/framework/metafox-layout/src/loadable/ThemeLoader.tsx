import { DEFAULT_THEME, Manager, useSession, IS_WEB } from '@metafox/framework';
import React from 'react';
import StyleLoader from './StyleLoader';
import LayoutLoader from './LayoutLoader';

interface Props {
  themeId?: string;
  manager: Manager;
}

interface State {
  themeId: string;
  hasError?: boolean;
  error?: unknown;
  retry?: number;
  themeLoaded?: boolean;
  styleLoaded?: boolean;
  shouldReload?: boolean;
}

class ThemeLoader extends React.Component<Props, State> {
  eventToken: string;

  constructor(props: Props) {
    super(props);

    const { preferenceBackend } = props.manager;

    const themeId = props.themeId ?? preferenceBackend.getTheme();

    this.state = {
      themeId
    };
  }

  static getDerivedStateFromError(error) {
    // Update state so the next render will show the fallback UI.
    return {
      hasError: true,
      error,
      themeId: DEFAULT_THEME,
      retry: 1
    };
  }

  componentDidCatch(error, errorInfo) {
    // console.log('Got error', error.message, errorInfo, this.state);
    // You can also log the error to an error reporting service
    // does not catch error
  }

  componentDidMount() {
    const { eventCenter } = this.props.manager;

    this.eventToken = eventCenter.on('layout.theme.changed', themeId => {
      this.setState({ themeId, shouldReload: true });
    });
  }

  componentShouldUnmount() {
    const { eventCenter } = this.props.manager;

    eventCenter.off('onEventCenter', this.eventToken);
  }

  setTheme = (data: any) => {
    // console.log({ theme: data });

    const { layoutBackend } = this.props.manager;

    layoutBackend.setupTheme(data);

    this.setState({ themeLoaded: true }, () => this.checkReady());
  };

  setStyle = (data: any) => {
    // console.log({ style: data });

    const { layoutBackend } = this.props.manager;

    layoutBackend.setupVariant(data);

    this.setState({ styleLoaded: true }, () => this.checkReady());
  };

  checkReady() {
    const { dispatch, layoutBackend } = this.props.manager;
    const { styleLoaded, themeLoaded, shouldReload } = this.state;

    if (styleLoaded && themeLoaded) {
      dispatch({ type: '@bootstrap/theme/DONE' });

      if (shouldReload) {
        layoutBackend.afterThemeChanged();
      }
    }
  }

  shouldComponentUpdate(nextProps: Props, nextState: State) {
    return nextState.themeId !== this.state.themeId;
  }

  render() {
    const { themeId } = this.state;

    const [themeName, styleName] = themeId.split(':');

    return (
      <>
        <LayoutLoader theme={themeName} ref={this.setTheme} />
        <StyleLoader
          theme={themeName}
          style={styleName ?? themeName}
          ref={this.setStyle}
        />
      </>
    );
  }
}

const ThemeLoaderWait = props => {
  const { loaded } = useSession();

  if (!loaded && IS_WEB) return null;

  return <ThemeLoader {...props} />;
};

export default ThemeLoaderWait;
