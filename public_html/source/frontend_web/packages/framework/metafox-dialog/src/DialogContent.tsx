import { DialogContent } from '@mui/material';
import { styled } from '@mui/material/styles';

const CusDialogContent = styled(DialogContent, {
  name: 'CusDialogContent',
  shouldForwardProp: (prop: string) => !/isSidePlacement/i.test(prop)
})<{
  isSidePlacement?: boolean;
  variant?: 'fitScroll' | 'fit' | 'alert';
}>({});

export default CusDialogContent;
