import { DEFAULT_PAGE_TITLE } from '@/helpers/constants';

type Pagedata = {
  title: string;
};
const PageMeta = ({ title }: Pagedata) => {
  return (
    <title>
      {title ? `${title} | ${DEFAULT_PAGE_TITLE}` : DEFAULT_PAGE_TITLE}
    </title>
  );
};

export default PageMeta;
