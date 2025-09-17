import { useBootstrapState, useGlobal } from '@metafox/framework';

export default function BootstrapWrapper({ children }) {
  const { error, loaded } = useBootstrapState();

  const { jsxBackend } = useGlobal();

  if (error) {
    if (error?.error instanceof Error) {
      throw error?.error;
    }

    const err = new Error('Reload browser to try again!');
    err.name = 'server_unavailable'; // site not ready
    throw err;
  }

  if (!loaded)
    return jsxBackend.render({
      component: 'Loading',
      props: { variant: 'init' }
    });

  return children;
}
