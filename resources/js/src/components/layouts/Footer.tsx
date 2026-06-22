import { appAuthor, appName, authorWebsite, currentYear } from '@/helpers/constants';
import { Link } from 'react-router';

const Footer = () => {
  return (
    <footer className="mt-auto footer flex items-center py-5 border-t border-default-200 bg-white dark:bg-default-50 z-10 w-full transition-all">
      <div className="lg:px-8 px-6 w-full flex md:justify-between justify-center gap-4 text-default-600 text-sm font-medium">
        <div>
          {currentYear} © {appName} - Todos los derechos reservados
        </div>
        <div className="md:flex hidden gap-2 item-center md:justify-end">
          Diseñado por
          <Link to={authorWebsite} target="_blank" className="text-primary hover:underline">
            {appAuthor}
          </Link>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
