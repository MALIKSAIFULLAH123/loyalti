import { Link, useGlobal } from '@metafox/framework';
import { DotSeparator } from '@metafox/ui';
import { SxProps, Typography } from '@mui/material';
import React from 'react';

interface Item {
  name: string;
  id: number;
  is_active?: number;
}

interface CategoryListProps<T extends Item = Item> {
  data: T[];
  className?: string;
  sx?: SxProps;
  isTruncateOneLine?: boolean;
  displayLimit?: number;
}

export function CategoryList({
  data,
  sx,
  isTruncateOneLine = false,
  displayLimit,
  ...props
}: CategoryListProps) {
  const { i18n } = useGlobal();
  const [categoryList, setCategoryList] = React.useState(
    data?.length ? data : []
  );

  React.useEffect(() => {
    if (displayLimit && data) {
      const dataTmp: any[] = data.slice(0, displayLimit);

      if (data.length > displayLimit) {
        dataTmp.push({
          id: 0,
          name: i18n.formatMessage(
            { id: 'total_more_categories' },
            {
              total: data.length - displayLimit
            }
          )
        });
      }

      setCategoryList(dataTmp);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [displayLimit, data]);

  if (!data?.length) return null;

  const handleShowMore = () => {
    setCategoryList(data);
  };

  return (
    <DotSeparator
      data-testid="categoryListBlock"
      sx={{
        fontSize: '13px',
        display: 'flex !important',
        alignItems: 'center',
        flexWrap: 'wrap',
        lineHeight: 1.2,
        ...sx
      }}
      isTruncateOneLine={isTruncateOneLine}
      {...props}
    >
      {categoryList.map((item, index) => {
        if (item.id === 0) {
          return (
            <Link
              data-testid={`categoryItem ${item?.name}`}
              key={'more'}
              onClick={handleShowMore}
              color={'primary'}
              role="button"
            >
              {item.name}
            </Link>
          );
        }

        if (!item.is_active) {
          return (
            <Typography
              data-testid={`categoryItem ${item?.name}`}
              key={index.toString()}
              variant="body2"
              sx={{ display: 'inline-block' }}
            >
              {item.name}
            </Typography>
          );
        }

        return (
          <Link
            data-testid={`categoryItem ${item?.name}`}
            key={index.toString()}
            to={item?.url}
            color={'primary'}
          >
            {item.name}
          </Link>
        );
      })}
    </DotSeparator>
  );
}
