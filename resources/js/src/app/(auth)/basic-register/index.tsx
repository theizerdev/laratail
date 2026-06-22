import { useState } from 'react';
import logoDark from '@/assets/images/logo-dark.png';
import logoLight from '@/assets/images/logo-light.png';
import IconifyIcon from '@/components/client-wrapper/IconifyIcon';
import PageMeta from '@/components/PageMeta';
import { Link, useNavigate } from 'react-router-dom';
import axios from '@/lib/axios';
import { useAuth } from '@/context/AuthContext';

const Index = () => {
  const navigate = useNavigate();
  const { login } = useAuth();
  
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: ''
  });
  
  const [errors, setErrors] = useState<any>({});
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    setErrors({});

    try {
      // CSRF initialization just in case
      await axios.get('/sanctum/csrf-cookie');
      
      const response = await axios.post('/api/register', formData);
      
      if (response.status === 201) {
        // Automatically login the user
        login(response.data);
        navigate('/'); // Redirect to dashboard or ecommerce
      }
    } catch (error: any) {
      if (error.response?.data?.errors) {
        setErrors(error.response.data.errors);
      } else {
        setErrors({ general: ['Ocurrió un error inesperado al registrar el usuario.'] });
      }
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <>
      <PageMeta title="Register" />
      <div className="relative min-h-screen w-full flex justify-center items-center py-16 md:py-10">
        <div className="card md:w-lg w-screen z-10 shadow-xl">
          <div className="text-center px-10 py-12">
            <Link to="/" className="flex justify-center">
              <img src={logoDark} alt="logo dark" className="h-6 flex dark:hidden" width={111} />
              <img src={logoLight} alt="logo light" className="h-6 hidden dark:flex" width={111} />
            </Link>

            <div className="mt-8 text-center">
              <h4 className="mb-2.5 text-xl font-semibold text-primary">
                Crea tu cuenta gratuita
              </h4>
              <p className="text-base text-default-500">Únete a Laraccess ahora mismo</p>
            </div>

            {errors.general && (
              <div className="mt-4 p-3 bg-danger/10 text-danger text-sm rounded border border-danger/20">
                {errors.general[0]}
              </div>
            )}

            <form onSubmit={handleSubmit} className="text-left w-full mt-8">
              
              <div className="mb-4">
                <label htmlFor="name" className="block font-medium text-default-900 text-sm mb-2">
                  Nombre Completo
                </label>
                <input
                  type="text"
                  id="name"
                  name="name"
                  value={formData.name}
                  onChange={handleChange}
                  className={`form-input w-full ${errors.name ? 'border-danger' : ''}`}
                  placeholder="Ej. Juan Pérez"
                  required
                />
                {errors.name && <p className="text-xs text-danger mt-1">{errors.name[0]}</p>}
              </div>

              <div className="mb-4">
                <label htmlFor="email" className="block font-medium text-default-900 text-sm mb-2">
                  Correo Electrónico
                </label>
                <input
                  type="email"
                  id="email"
                  name="email"
                  value={formData.email}
                  onChange={handleChange}
                  className={`form-input w-full ${errors.email ? 'border-danger' : ''}`}
                  placeholder="ejemplo@correo.com"
                  required
                />
                {errors.email && <p className="text-xs text-danger mt-1">{errors.email[0]}</p>}
              </div>

              <div className="mb-4">
                <label htmlFor="Password" className="block font-medium text-default-900 text-sm mb-2">
                  Contraseña
                </label>
                <input
                  type="password"
                  id="Password"
                  name="password"
                  value={formData.password}
                  onChange={handleChange}
                  className={`form-input w-full ${errors.password ? 'border-danger' : ''}`}
                  placeholder="Mínimo 8 caracteres"
                  required
                />
                {errors.password && <p className="text-xs text-danger mt-1">{errors.password[0]}</p>}
              </div>

              <p className="italic text-sm font-medium text-default-500 mt-2">
                Al registrarte aceptas nuestros{' '}
                <Link to="#" className="text-primary underline hover:text-primary-dark">
                  Términos de Uso
                </Link>
              </p>

              <div className="mt-8 text-center">
                <button 
                  type="submit" 
                  disabled={isSubmitting}
                  className="btn bg-primary text-white w-full hover:bg-primary-dark transition-colors disabled:opacity-50"
                >
                  {isSubmitting ? 'Registrando...' : 'Registrarse'}
                </button>
              </div>

              <div className="mt-6 text-center text-sm font-medium text-default-600">
                ¿Ya tienes una cuenta?{' '}
                <Link to="/basic-login" className="text-primary underline hover:text-primary-dark">
                  Inicia Sesión aquí
                </Link>
              </div>

            </form>
          </div>
        </div>

        <div className="absolute inset-0 overflow-hidden">
          <svg
            aria-hidden="true"
            className="absolute inset-0 size-full fill-black/2 stroke-black/5 dark:fill-white/2.5 dark:stroke-white/2.5"
          >
            <defs>
              <pattern id="authPattern" width="56" height="56" patternUnits="userSpaceOnUse" x="50%" y="16">
                <path d="M.5 56V.5H72" fill="none"></path>
              </pattern>
            </defs>
            <rect width="100%" height="100%" strokeWidth="0" fill="url(#authPattern)"></rect>
          </svg>
        </div>
      </div>
    </>
  );
};

export default Index;
