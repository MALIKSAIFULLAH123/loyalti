# @metafox/framework

### Shared Context

Shared context is a container contain common service likes `i18n`, `apiClient`, `modal`, `layout`, `router`
It allow we to access it's service in sagas & general React Component.

Using shared context with i18n

```typescript jsx
import { useGlobal } from '@metafox/framework';

function ComponentA() {
  // access i18n service in SharedContext.
  const { i18n } = useGlobal();
  return <div>{i18n.formatMessage({ id: 'say_hello' })}</div>;
}
```

In saga context

```typescript jsx
import { call, put } from 'redux-saga/effect';
import { getGlobalContext } from '@metafox/framework';

export function* checkLoggedIn() {
  // using alert service instead of window.alert
  // using i18n.t from i18n next translation.
  // using apiClient from data.

  const { apiClient, i18n, alert } = yield* getGlobalContext();
  yield apiClient.post('/path/to/api', {
    identity: 'email@domain.com',
    credential: 'password'
  });
  alert(i18n.formatMessage({ id: 'say_hello' }));
}
```
