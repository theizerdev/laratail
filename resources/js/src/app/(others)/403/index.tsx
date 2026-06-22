import AuthBgDark from '@/assets/images/auth-bg-dark.jpg';
import AuthBg from '@/assets/images/auth-bg.jpg';
import LogoDark from '@/assets/images/logo-dark.png';
import LogoLight from '@/assets/images/logo-light.png';
import PageMeta from '@/components/PageMeta';
import { LuHouse, LuShieldAlert } from 'react-icons/lu';
import { Link } from 'react-router-dom';

const PageForbidden = () => {
  return (
    <>
      <PageMeta title="Acceso Denegado" />
      <div className="relative h-screen w-full flex justify-center items-center">
        <div className="absolute inset-0">
          <div className="block dark:hidden h-full w-full">
            <img src={AuthBg} alt="background" className="object-cover size-full" />
          </div>
          <div className="hidden dark:block h-full w-full">
            <img src={AuthBgDark} alt="background dark" className="object-cover size-full" />
          </div>
        </div>

        <div className="relative z-10 bg-default-50 rounded-lg w-lg">
          <div className="text-center px-10 py-12">
            <Link to="/" className="flex justify-center">
              <div className="block dark:hidden h-6 relative w-auto">
                <img src={LogoDark} alt="logo dark" className="object-contain" width={111} />
              </div>
              <div className="hidden dark:block h-6 relative w-auto">
                <img src={LogoLight} alt="logo light" className="object-contain" width={111} />
              </div>
            </Link>

            <div className="mt-10">
              <div className="flex justify-center text-danger mx-auto">
                <LuShieldAlert className="size-32" />
              </div>
            </div>

            <div className="mt-8 text-center">
              <h4 className="mb-2 text-danger dark:text-danger-400 text-2xl font-semibold">
                ERROR 403: ACCESO DENEGADO
              </h4>
              <p className="mb-6 text-base text-default-500">
                Lo sentimos, tu usuario no cuenta con los permisos necesarios para visualizar este módulo. Por favor contacta al administrador si crees que es un error.
              </p>
              <Link to="/">
                <button
                  type="button"
                  className="bg-primary text-white hover:bg-blue-600 rounded text-[13px] py-2 px-4 inline-flex items-center transition-colors"
                >
                  <LuHouse className="size-3 me-2" />
                  Volver al Inicio
                </button>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </>
  );
};

export default PageForbidden;
