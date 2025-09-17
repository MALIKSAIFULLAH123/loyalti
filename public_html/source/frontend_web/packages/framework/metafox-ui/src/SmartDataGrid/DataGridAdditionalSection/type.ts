import { SxProps } from '@mui/system';
import { RemoteDataSource } from '@metafox/framework/types';

export type ConfigProps = {
  title?: string;
  description?: string;
  titleProps?: Record<string, any>;
  descriptionProps?: Record<string, any>;
  dataSource?: RemoteDataSource;
};
export type ItemProps = {
  config: ConfigProps;
  sx?: SxProps;
};
