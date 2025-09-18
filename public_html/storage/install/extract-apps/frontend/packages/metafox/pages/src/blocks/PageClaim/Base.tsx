import {
  BlockViewProps,
  useGlobal,
  useResourceAction,
  useResourceForm
} from '@metafox/framework';
import { styled, Box, Grid, Typography } from '@mui/material';
import {
  Block,
  BlockContent,
  BlockHeader,
  ScrollContainer
} from '@metafox/layout';
import React from 'react';
import { FormBuilder } from '@metafox/form';
import { whenParamRules } from '@metafox/utils';
import qs from 'querystring';
import { APP_PAGE, RESOURCE_CLAIM } from '@metafox/pages/constant';

export type Props = BlockViewProps;
const gridTitle = [
  {
    label: 'page',
    grid: 2.5
  },
  {
    label: 'created_date',
    grid: 2.5
  },
  {
    label: 'modified_date',
    grid: 2.5
  },
  {
    label: 'status',
    grid: 1.5
  },
  {
    label: 'description',
    grid: 2.5
  },
  {
    label: null,
    grid: 0.5,
    center: true
  }
];

const TitleStyled = styled(Grid, { name: 'TitleStyled' })(({ theme }) => ({
  display: 'flex',
  padding: theme.spacing(3, 2, 2)
}));

const ContentWrapper = styled(Box, {
  name: 'ContentWrapper'
})(({ theme }) => ({
  padding: theme.spacing(3, 2, 2),
  [theme.breakpoints.down('md')]: {
    padding: theme.spacing(0)
  }
}));

export default function Base({ title, ...rest }: Props) {
  const { usePageParams, navigate, jsxBackend, useIsMobile, i18n } =
    useGlobal();
  const pageParams = usePageParams();
  const isMobile = useIsMobile(true);
  const dataSource = useResourceAction(APP_PAGE, RESOURCE_CLAIM, 'viewAll');

  const formSchema = useResourceForm(APP_PAGE, RESOURCE_CLAIM, 'search');

  const ListView = jsxBackend.get('core.block.mainListing');

  const submitFilter = (values, form) => {
    const apiRules = dataSource.apiRules;

    const params = whenParamRules(values, apiRules);

    navigate(`?${qs.stringify(params)}`, { replace: true });

    form.setSubmitting(false);
  };

  if (isMobile) {
    return (
      <Block testid="pageClaimBlock" {...rest}>
        <BlockHeader title={title}></BlockHeader>
        <BlockContent {...rest}>
          <FormBuilder
            navigationConfirmWhenDirty={false}
            formSchema={formSchema}
            onSubmit={submitFilter}
          />
          {React.createElement(ListView, {
            itemView: 'page.itemView.claimRecord',
            dataSource,
            emptyPage: 'page_claim.itemView.no_content_record',
            gridContainerProps: { spacing: 0 },
            pageParams,
            clearDataOnUnMount: true,
            canLoadMore: true
          })}
        </BlockContent>
      </Block>
    );
  }

  return (
    <Block testid="inviteListBlock" {...rest}>
      <BlockHeader title={title}></BlockHeader>
      <BlockContent {...rest}>
        <ContentWrapper>
          <FormBuilder
            navigationConfirmWhenDirty={false}
            formSchema={formSchema}
            onSubmit={submitFilter}
          />
          <TitleStyled container>
            {gridTitle.map((item, index) => (
              <Grid item key={index} xs={item.grid}>
                <Typography
                  variant="h5"
                  sx={item.center && { textAlign: 'center' }}
                >
                  {item.label && i18n.formatMessage({ id: item.label })}
                </Typography>
              </Grid>
            ))}
          </TitleStyled>
          <Box sx={{ flex: 1, minHeight: 0 }}>
            <ScrollContainer autoHide={false} autoHeight>
              {React.createElement(ListView, {
                itemView: 'page.itemView.claimRecord',
                dataSource,
                emptyPage: 'page_claim.itemView.no_content_record',
                blockLayout: 'App List - Record Table',
                itemLayout: 'Record Item - Table',
                gridLayout: 'Record Item - Table',
                pageParams,
                clearDataOnUnMount: true,
                canLoadMore: true
              })}
            </ScrollContainer>
          </Box>
        </ContentWrapper>
      </BlockContent>
    </Block>
  );
}

Base.displayName = 'Page_Claim';
