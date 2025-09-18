import ErrorBoundary from '@metafox/core/pages/ErrorPage/Page';
import {
  BlockViewProps,
  Link,
  useGlobal,
  useResourceAction
} from '@metafox/framework';
import { APP_GROUP } from '@metafox/group/constant';
import HtmlViewer from '@metafox/html-viewer';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { FormatDate, InformationList, TruncateText } from '@metafox/ui';
import { filterShowWhen } from '@metafox/utils';
import { Box, Skeleton, Typography, styled } from '@mui/material';
import React from 'react';

export interface Props extends BlockViewProps {}

const MS_1_HOUR = 36e5;

const name = 'MuiUserProfileDetailsGroup';

const StyledTextInfo = styled('div', { name, slot: 'TextInfo' })(
  ({ theme }) => ({
    marginBottom: theme.spacing(1.5),
    fontSize: theme.mixins.pxToRem(15),
    color: theme.palette.text.primary,
    wordBreak: 'break-word',
    wordWrap: 'break-word'
  })
);

const PrivacyIcon = ['ico-globe-o', 'ico-lock', 'ico-user1-three'];
const PrivacyInfo = ['public_group', 'closed_group', 'secret_group'];

export default function UserProfileAboutBlock({ title, identity }: Props) {
  const { useFetchDetail, usePageParams, jsxBackend, i18n, useGetItem } =
    useGlobal();
  const item = useGetItem(identity) || [];
  const dataSource = useResourceAction(APP_GROUP, APP_GROUP, 'groupInfo');
  const pageParams = usePageParams();
  const [data, loading, error] = useFetchDetail({
    dataSource,
    pageParams,
    cachePrefix: 'group-profile',
    cacheKey: `group-about-${pageParams?.id}`,
    ttl: MS_1_HOUR,
    forceReload: false
  });

  const LoadingSkeleton = () => (
    <Box>
      <Skeleton width={'100%'} />
      <Skeleton width={'100%'} />
      <Skeleton width={'100%'} />
    </Box>
  );

  const NoContentBlock = jsxBackend.get('core.block.no_content');

  const {
    description,
    location,
    phone,
    total_member,
    external_link,
    creation_date,
    reg_method,
    category
  } = Object.assign({}, data);

  const textCategory = React.useMemo(() => {
    if (!category?.is_active) {
      return (
        <Typography variant="body1" sx={{ display: 'inline-block' }}>
          {category?.name}
        </Typography>
      );
    }

    return (
      <Link to={category?.link || category?.url} color="primary">
        {category?.name}
      </Link>
    );
  }, [category]);
  const infoItems = [
    {
      icon: 'ico-checkin-o',
      info: location,
      value: !!location,
      label: 'location'
    },
    {
      icon: 'ico-layers-o',
      info: textCategory,
      value: !!textCategory,
      label: 'categories'
    },
    {
      icon: 'ico-phone-o',
      info: phone,
      value: !!phone,
      label: 'phone_number'
    },
    {
      icon: 'ico-user-two-men-o',
      info: i18n.formatMessage(
        {
          id: 'people_joined_group'
        },
        {
          value: total_member
        }
      ),
      value: !!total_member,
      label: 'people_joined_group_tooltip'
    },
    {
      icon: PrivacyIcon[reg_method] || '',
      info: reg_method
        ? i18n.formatMessage({
            id: PrivacyInfo[reg_method]
          })
        : '',
      value: true,
      label: 'privacy'
    },
    {
      icon: 'ico-globe-alt-o',
      info: external_link,
      value: !!external_link,
      label: 'external_link_tooltip'
    },
    {
      icon: 'ico-calendar',
      info: i18n.formatMessage(
        {
          id: 'created_at'
        },
        {
          date: () => (
            <FormatDate
              data-testid="publishedDate"
              value={creation_date}
              format="LL"
            />
          )
        }
      ),
      value: true,
      showWhen: ['truthy', 'item.profile_settings.core_view_publish_date'],
      label: 'created_date'
    }
  ].filter(item => item.value);

  let infoItemsFilter = [];

  if (item && infoItems) {
    infoItemsFilter = filterShowWhen(infoItems, {
      item
    });
  }

  const sections = Object.values(data?.sections || []);

  return (
    <>
      <Block>
        <BlockHeader title={title} />
        <BlockContent>
          <ErrorBoundary
            loading={loading}
            error={error}
            loadingComponent={LoadingSkeleton}
            emptyComponent={data ? null : NoContentBlock}
          >
            <Box>
              {description && (
                <StyledTextInfo>
                  <TruncateText variant={'body1'} showFull>
                    <HtmlViewer html={description} />
                  </TruncateText>
                </StyledTextInfo>
              )}
              <div>
                <InformationList values={infoItemsFilter} />
              </div>
            </Box>
          </ErrorBoundary>
        </BlockContent>
      </Block>
      {sections?.length
        ? sections.map((section, index) => (
            <Block key={`i${index}`}>
              <BlockHeader title={section?.label} />
              <BlockContent>
                {jsxBackend.render({
                  component: section.component ?? 'layout.section.list_info',
                  props: { section }
                })}
              </BlockContent>
            </Block>
          ))
        : null}
    </>
  );
}
