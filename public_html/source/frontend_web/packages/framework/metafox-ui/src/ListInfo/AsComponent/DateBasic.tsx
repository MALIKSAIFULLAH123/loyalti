/**
 * @type: ui
 * name: listInfo.as.Date
 */
import { useGlobal } from '@metafox/framework';

// todo moved this column to base size.
export default function DateBasic(props) {
  const { value, valueFormat, format = 'L' } = props;
  const { moment } = useGlobal();

  if (!value) return null;

  return moment(value, valueFormat).format(format);
}
