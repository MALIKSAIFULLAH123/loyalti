const checkActiveMenu = (
  pageParams: Record<string, any>,
  item: Record<string, any>
) =>
  pageParams?.resourceName === item?.resource_name &&
  pageParams?.appName === item?.module_name &&
  pageParams?.id === item?.id?.toString();

export default checkActiveMenu;
