import { isEmpty, uniqueId } from 'lodash';
import React from 'react';
import { useGlobal } from '..';

export default function useSuggestions<T = Record<string, any>>({
  apiUrl,
  type = '@suggestion',
  limit,
  initialParams,
  option_default = []
}: {
  apiUrl: string;
  isCached?: boolean;
  type?: string;
  limit?: number;
  initialParams?: Record<string, any>;
  option_default?: T[];
}): [
  {
    items: T[];
    loading: boolean;
    error?: undefined;
  },
  (query: string, params: Record<string, any>) => void
] {
  const mounted = React.useRef(true);
  const none = React.useState<string>(uniqueId('s'));

  const [data, setData] = React.useState<{
    items: T[];
    loading: boolean;
    error?: undefined;
  }>({
    items: option_default || [],
    loading: true
  });

  const { dispatch } = useGlobal();

  React.useEffect(() => {
    if (isEmpty(option_default)) {
      handleChange('');
    }

    return () => {
      mounted.current = false;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const handleSuggestions = data => {
    if (mounted.current && Array.isArray(data.items)) setData(data);
  };

  const handleChange = (q: string, params = {}) => {
    dispatch({
      type,
      payload: {
        q,
        apiUrl,
        none,
        limit,
        initialParams: { ...initialParams, ...params }
      },
      meta: { onSuccess: handleSuggestions }
    });
  };

  return [data, handleChange];
}
