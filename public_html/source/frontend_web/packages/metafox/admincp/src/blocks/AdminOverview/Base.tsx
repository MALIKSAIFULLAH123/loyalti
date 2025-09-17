import { useGlobal } from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { UIBlockViewProps } from '@metafox/ui';
import { Table, TableBody, TableCell, TableRow } from '@mui/material';
import React from 'react';

export interface Props extends UIBlockViewProps {}

export default function AdminSystemOverview({ blockProps, title }: Props) {
  const { useFetchDetail, usePageParams, BlockLoading } = useGlobal();
  const { dataSource } = usePageParams();

  const [data, loading, error] = useFetchDetail<{
    title?: string;
    sections?: { title: string; items: { label: string; value: string }[] }[];
    items?: { label: string; value: string }[];
    text?: string;
    markdown?: string;
  }>({
    dataSource,
    data: {}
  });

  if (loading || error) {
    return (
      <Block>
        <BlockContent style={{ minHeight: '60vh' }}>
          {loading || error ? (
            <BlockLoading loading={loading} error={error} />
          ) : null}
        </BlockContent>
      </Block>
    );
  }

  if (data.sections) {
    return data.sections.map((section, index) => {
      return (
        <Block key={index.toString()}>
          <BlockHeader title={section.title} />
          <BlockContent>
            <Table>
              <TableBody>
                {section.items.map((row, index) => {
                  return (
                    <TableRow key={index.toString()}>
                      <TableCell width="30%">{row.label}</TableCell>
                      <TableCell>{row.value}</TableCell>
                    </TableRow>
                  );
                })}
              </TableBody>
            </Table>
          </BlockContent>
        </Block>
      );
    });
  }

  if (data.text) {
    return (
      <Block>
        <BlockHeader title={data.title} />
        <BlockContent>
          <code dangerouslySetInnerHTML={{ __html: data.text }} />
        </BlockContent>
      </Block>
    );
  }

  if (data.items)
    return (
      <Block>
        <BlockHeader title={data.title} />
        <BlockContent>
          <Table>
            <TableBody>
              {data.items.map((row, index) => {
                return (
                  <TableRow key={index.toString()}>
                    <TableCell width="30%">{row.label}</TableCell>
                    <TableCell>{row.value}</TableCell>
                  </TableRow>
                );
              })}
            </TableBody>
          </Table>
        </BlockContent>
      </Block>
    );

  return null;
}
