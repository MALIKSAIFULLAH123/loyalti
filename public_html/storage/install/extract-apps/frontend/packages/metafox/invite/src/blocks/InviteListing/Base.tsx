import {
  BlockViewProps,
  useGlobal,
  useResourceAction,
  useResourceForm,
  GlobalState,
  getPagingSelector
} from '@metafox/framework';
import { styled, Box } from '@mui/material';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import React from 'react';
import { FormBuilder } from '@metafox/form';
import { compactData, compactUrl, whenParamRules } from '@metafox/utils';
import qs from 'querystring';
import {
  APP_INVITE,
  PAGING_LISTING_INVITE,
  RESOURCE_INVITE
} from '../../constants';
import { SmartDataGrid } from '@metafox/ui/Loadable';
import { useSelector } from 'react-redux';
import { isEmpty, isEqual, omit } from 'lodash';
import ErrorBoundary from '@metafox/core/pages/ErrorPage/Page';

export type Props = BlockViewProps;

const ContentWrapper = styled(Box, {
  name: 'ContentWrapper'
})(({ theme }) => ({
  padding: theme.spacing(3, 2, 2),

  display: 'flex',
  flexDirection: 'column',
  [theme.breakpoints.down('md')]: {
    padding: theme.spacing(0)
  },
  '& .MuiCheckbox-root': {
    padding: 0
  }
}));

export default function Base({ title, ...rest }: Props) {
  const {
    usePageParams,
    navigate,
    jsxBackend,
    BatchSelectProvider,
    dispatch,
    useIsMobile
  } = useGlobal();
  const pageParams = usePageParams();
  const isMobile = useIsMobile();

  const [checkedList, setCheckedList] = React.useState([]);
  const [submitting, setSubmitting] = React.useState<boolean>(false);

  const [pagingIdListView, setPagingIdListView] = React.useState(
    PAGING_LISTING_INVITE
  );

  const dataSource = useResourceAction(APP_INVITE, RESOURCE_INVITE, 'viewAll');
  const dataSourceGrid = useResourceAction(
    APP_INVITE,
    RESOURCE_INVITE,
    'getGrid'
  );

  const apiParams = compactData(
    dataSource.apiParams,
    pageParams,
    dataSource.apiRules
  );

  const pagingId = `${compactUrl(dataSource.apiUrl, pageParams)}?${qs.stringify(
    omit(apiParams, ['page'])
  )}`;

  const pagingData = useSelector((state: GlobalState) =>
    getPagingSelector(state, pagingId)
  );
  const formSchema = useResourceForm(APP_INVITE, RESOURCE_INVITE, 'search');

  const ListView = jsxBackend.get('core.block.mainListing');

  const submitFilter = (values, form) => {
    const apiRules = dataSource.apiRules;

    const params = whenParamRules(values, apiRules);

    const newPagingParams = `${PAGING_LISTING_INVITE}?${qs.stringify(params)}`;

    if (!isEmpty(params) && !isEqual(pagingIdListView, newPagingParams)) {
      setCheckedList([]);
      setPagingIdListView(newPagingParams);
    }

    navigate(`?${qs.stringify(params)}`, { replace: true });

    form.setSubmitting(false);
  };

  const handleToggleItem = (idItem, isChecked) => {
    const data = isChecked
      ? [...checkedList, idItem]
      : checkedList.filter(x => x !== idItem);

    setCheckedList(data);
  };

  const handleActionName = actionName => {
    setSubmitting(true);

    dispatch({
      type: `${actionName}/selectAll`,
      payload: {
        ids: checkedList
      },
      meta: {
        onSuccess: () => {
          setCheckedList([]);
          setSubmitting(false);
        },
        onFailure: () => {
          setSubmitting(false);
        }
      }
    });
  };

  const pagingIds = React.useMemo(() => {
    return pagingData?.ids?.map(item => parseInt(item.split('.')[3]));
  }, [pagingData]);

  const handleSelectAll = () => {
    if (pagingIds.length === checkedList.length) {
      setCheckedList([]);
    } else {
      setCheckedList(pagingIds);
    }
  };

  React.useEffect(() => {
    const hasChanged = checkedList.some(x => !pagingIds.includes(x));

    if (hasChanged) {
      setCheckedList(prev => prev.filter(x => pagingIds.includes(x)));
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pagingIds?.length]);
  const context = {
    handleToggleItem,
    handleActionName,
    handleSelectAll,
    pagingIds,
    checkedList,
    pagingData,
    loading: submitting
  };

  if (isMobile) {
    return (
      <Block testid="inviteListBlock" {...rest}>
        <BlockHeader title={title}></BlockHeader>
        <BlockContent {...rest}>
          <BatchSelectProvider value={context}>
            <FormBuilder
              navigationConfirmWhenDirty={false}
              formSchema={formSchema}
              onSubmit={submitFilter}
            />
            {React.createElement(ListView, {
              itemView: 'invite.itemView.inviteRecord',
              dataSource,
              emptyPage: 'invite.itemView.no_content_record',
              blockLayout: 'Large Main Lists',
              gridContainerProps: { spacing: 0 },
              pageParams,
              canLoadMore: true,
              loadMoreType: 'pagination',
              numberOfItemsPerPage: 15
            })}
          </BatchSelectProvider>
        </BlockContent>
      </Block>
    );
  }

  return (
    <Block testid="inviteListBlock" {...rest}>
      <BlockHeader title={title}></BlockHeader>
      <BlockContent {...rest}>
        <BatchSelectProvider value={context}>
          <ContentWrapper>
            <FormBuilder
              navigationConfirmWhenDirty={false}
              formSchema={formSchema}
              onSubmit={submitFilter}
            />
            <SmartDataGrid
              dataSource={dataSourceGrid}
              gridName={'invite.listing'}
              errorComponent={ErrorBoundary}
            />
          </ContentWrapper>
        </BatchSelectProvider>
      </BlockContent>
    </Block>
  );
}

Base.displayName = 'Invite_Listing';
