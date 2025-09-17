/**
 * @type: service
 * name: ErrorBoundary
 */
import { Manager } from '@metafox/framework/Manager';
import React from 'react';
import withManager from '../hocs/withManager';
import ErrorFallback from './ErrorFallback';

export class ErrorBoundary extends React.Component<
  { errorPage: string; manager: Manager },
  { error: Error; errorInfo: React.ErrorInfo; hasError: boolean }
> {
  constructor(props) {
    super(props);
    this.state = { error: null, errorInfo: null, hasError: false };
  }

  static getDerivedStateFromError(error) {
    return { hasError: true, error };
  }

  handleErrorCode = error => {
    const status = error?.status;

    if (!status) return;

    switch (status) {
      case 401:
      case 403:
      case 404:
    }
  };

  componentDidCatch(error, errorInfo) {
    let err;

    try {
      err = JSON.parse(error?.message)?.data?.error;
      this.handleErrorCode(error);
    } catch (e) {
      err = error;
    }

    this.setState({
      error: err,
      errorInfo
    });
  }

  render() {
    if (this.state.hasError) {
      const { error, errorInfo } = this.state;
      const { errorPage, manager } = this.props;

      if (errorPage && manager.jsxBackend.get(errorPage)) {
        const ErrorComponent = manager?.jsxBackend?.get(errorPage);

        return <ErrorComponent />;
      }

      return <ErrorFallback error={error} errorInfo={errorInfo} />;
    }

    return this.props.children;
  }
}

export default withManager(ErrorBoundary);
