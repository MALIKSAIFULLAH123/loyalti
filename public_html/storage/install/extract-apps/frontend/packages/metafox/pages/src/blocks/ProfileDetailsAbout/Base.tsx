import { BlockViewProps, useGlobal, Link } from '@metafox/framework';
import HtmlViewer from '@metafox/html-viewer';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { FormatDate, InformationList } from '@metafox/ui';
import { Skeleton, Typography, styled } from '@mui/material';
import React from 'react';

const name = 'MuiUserProfileDetailsPage';

const TextInfoStyled = styled('div', { name, slot: 'TextInfo' })(
  ({ theme }) => ({
    fontSize: theme.mixins.pxToRem(15),
    color: theme.palette.text.primary,
    marginBottom: theme.spacing(2),
    wordBreak: 'break-word',
    wordWrap: 'break-word'
  })
);

const MS_1_HOUR = 36e5;

export interface Props extends BlockViewProps {}

export default function UserProfileAboutBlock({ title }: Props) {
  const { useFetchDetail, usePageParams, i18n, useGetItem, jsxBackend } =
    useGlobal();
  const { id, identity } = usePageParams();

  const item = useGetItem(identity);

  const [data, loading] = useFetchDetail({
    dataSource: {
      apiUrl: `page-info/${id}`
    },
    cachePrefix: 'page-profile',
    cacheKey: `page-about/${id}`,
    ttl: MS_1_HOUR,
    forceReload: false
  });

  const {
    text_parsed,
    location,
    phone,
    external_link,
    extra,
    creation_date,
    category
  } = data || {};

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

  if (loading) {
    return (
      <Block>
        <BlockHeader title={title} />
        <BlockContent>
          <Skeleton height={20} width="100%" />
          <Skeleton height={20} width="100%" />
          <Skeleton height={20} width="100%" />
        </BlockContent>
      </Block>
    );
  }

  const infoItems = [
    {
      icon: 'ico-checkin-o',
      info: location,
      label: 'location'
    },
    {
      icon: 'ico-phone-o',
      info: phone,
      label: 'phone_number'
    },
    {
      icon: 'ico-layers-o',
      info: textCategory,
      value: !!textCategory,
      label: 'categories'
    },
    {
      icon: 'ico-thumbup-o',
      info: i18n.formatMessage(
        {
          id: 'people_liked_this_page'
        },
        {
          value: item?.statistic?.total_like
        }
      ),
      label: 'people_liked_page_tooltip'
    },
    {
      icon: 'ico-globe-alt-o',
      info: external_link ? (
        <Link
          to={external_link}
          color="primary"
          target="_blank"
          rel="noopener noreferrer"
        >
          {external_link}
        </Link>
      ) : null,
      label: 'external_link_tooltip'
    },
    extra?.can_view_publish_date && {
      icon: 'ico-rocket-o',
      info: (
        <FormatDate
          data-testid="creationDate"
          value={creation_date}
          format="LL"
          phrase="published_on_time"
        />
      ),
      label: 'created_date'
    }
  ];

  const sections = Object.values(data?.sections || []);

  return (
    <>
      <Block>
        <BlockHeader title={title} />
        <BlockContent>
          {text_parsed && (
            <TextInfoStyled>
              <HtmlViewer html={text_parsed} />
            </TextInfoStyled>
          )}
          <div>
            <InformationList values={infoItems} />
          </div>
        </BlockContent>
      </Block>
      {sections.length
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
