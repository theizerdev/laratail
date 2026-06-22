import logoDark from '@/assets/images/logo-dark.png';
import logoLight from '@/assets/images/logo-light.png';
import IconifyIcon from '@/components/client-wrapper/IconifyIcon';
import PageMeta from '@/components/PageMeta';
import { Link, useNavigate } from 'react-router';
import { useState } from 'react';
import { useAuth } from '@/context/AuthContext';
import axios from '@/lib/axios';

const Index = () => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [remember, setRemember] = useState(false);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const { checkAuth } = useAuth();
  const navigate = useNavigate();

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setLoading(true);
    
    try {
      await axios.get('/sanctum/csrf-cookie');
      await axios.post('/api/login', { email, password, remember });
      await checkAuth();
      navigate('/');
    } catch (err: any) {
      if (err.response?.status === 422) {
        setError(err.response.data.message || 'Invalid credentials');
      } else {
        setError('An error occurred. Please try again.');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      <PageMeta title="Login" />
      <div className="relative min-h-screen w-full flex justify-center items-center py-16 md:py-10">
        <div className="card md:w-lg w-screen z-10">
          <div className="text-center px-10 py-12">
            <Link to="/index" className="flex justify-center">
              <img src={logoDark} alt="logo dark" className="h-6 flex dark:hidden" width={111} />
              <img src={logoLight} alt="logo light" className="h-6 hidden dark:flex" width={111} />
            </Link>

            <div className="mt-8 text-center">
              <h4 className="mb-2.5 text-xl font-semibold text-primary">Welcome Back !</h4>
              <p className="text-base text-default-500">Sign in to continue to Tailwick.</p>
            </div>

            <form onSubmit={handleLogin} className="text-left w-full mt-10">
              {error && (
                <div className="mb-4 text-sm text-red-600 bg-red-100 border border-red-200 p-2 rounded">
                  {error}
                </div>
              )}
              <div className="mb-4">
                <label htmlFor="email" className="block font-medium text-default-900 text-sm mb-2">
                  Email ID
                </label>
                <input
                  type="email"
                  id="email"
                  className="form-input"
                  placeholder="Enter email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  required
                />
              </div>

              <div className="mb-4">
                <Link
                  to="/basic-reset-password"
                  className="text-primary font-medium text-sm mb-2 float-end"
                >
                  Forgot Password ?
                </Link>
                <label
                  htmlFor="Password"
                  className="block font-medium text-default-900 text-sm mb-2"
                >
                  Password
                </label>
                <input
                  type="password"
                  id="Password"
                  className="form-input"
                  placeholder="Enter Password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  required
                />
              </div>

              <div className="flex items-center gap-2 mb-4">
                <input 
                  id="checkbox-1" 
                  type="checkbox" 
                  className="form-checkbox" 
                  checked={remember}
                  onChange={(e) => setRemember(e.target.checked)}
                />
                <label className="text-default-900 text-sm font-medium" htmlFor="checkbox-1">
                  Remember Me
                </label>
              </div>

              <div className="mt-10 text-center">
                <button type="submit" disabled={loading} className="btn bg-primary text-white w-full">
                  {loading ? 'Signing In...' : 'Sign In'}
                </button>
              </div>

              <div className="my-9 relative text-center before:absolute before:top-2.5 before:left-0 before:border-t before:border-t-default-200 before:w-full before:h-0.5 before:right-0 before:-z-0">
                <h4 className="relative z-1 py-0.5 px-2 inline-block font-medium text-default-600 bg-card">
                  Sign In With
                </h4>
              </div>

              <div className="flex w-full justify-center items-center gap-2">
                <Link
                  to="#"
                  className="btn border border-default-200 flex-grow hover:bg-default-150 shadow-sm hover:text-default-800"
                >
                  <IconifyIcon icon={'logos:google-icon'} />
                  Use Google
                </Link>

                <Link
                  to="#"
                  className="btn border border-default-200 flex-grow hover:bg-default-150 shadow-sm hover:text-default-800"
                >
                  <IconifyIcon icon={'logos:apple'} className="text-mono" />
                  Use Apple
                </Link>
              </div>

              <div className="mt-10 text-center">
                <p className="text-base text-default-500">
                  Don't have an Account ?{' '}
                  <Link
                    to="/basic-register"
                    className="font-semibold underline hover:text-primary transition duration-200"
                  >
                    SignUp
                  </Link>
                </p>
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
              <pattern
                id="authPattern"
                width="56"
                height="56"
                patternUnits="userSpaceOnUse"
                x="50%"
                y="16"
              >
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
