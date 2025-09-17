import { useGlobal } from '@metafox/framework';

function DataGridAdditionalSection(props) {
  const { jsxBackend } = useGlobal();

  if (!props) return null;

  const { config } = props;

  return jsxBackend.render({
    component: `dataGrid.additionalSection.${config.component ?? 'Basic'}`,
    props
  });
}

export default DataGridAdditionalSection;
