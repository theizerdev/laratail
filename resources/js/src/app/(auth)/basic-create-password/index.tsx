import logoDark from '@/assets/images/logo-dark.png';
import logoLight from '@/assets/images/logo-light.png';
import PageMeta from '@/components/PageMeta';
import { Link, useNavigate, useSearchParams } from 'react-router-dom';
import { useState } from 'react';
import axios from '@/lib/axios';

const Index = () => {
  const [searchParams] = useSearchParams();
  const token = searchParams.get('token') || '';
  const email = searchParams.get('email') || '';

  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [status, setStatus] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setStatus('');
    setError('');
    setLoading(true);

    try {
      await axios.get('/sanctum/csrf-cookie');
      const response = await axios.post('/api/reset-password', {
        token,
        email,
        password,
        password_confirmation: passwordConfirmation,
      });
      setStatus(response.data.status || 'Your password has been reset!');
      setTimeout(() => navigate('/basic-login'), 3000);
    } catch (err: any) {
      if (err.response?.status === 422) {
        setError(err.response.data.message || 'Invalid data.');
      } else {
        setError('An error occurred. Please try again.');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      <PageMeta title="Create Password" />
      <div className="relative min-h-screen w-full flex justify-center items-center py-16 md:py-10">
        <div className="card md:w-lg w-screen z-10">
          <div className="text-center px-10 py-12">
            <Link to="/index" className="flex justify-center">
              <img src={logoDark} alt="logo dark" className="h-6 flex dark:hidden" width={111} />
              <img src={logoLight} alt="logo light" className="h-6 hidden dark:flex" width={111} />
            </Link>

            <div className="mt-8">
              <h4 className="mb-2 text-primary text-xl font-semibold">Set a New Password</h4>
              <p className="text-base/normal mb-8 text-default-500">
                Your new password should be distinct from any of your prior passwords
              </p>
            </div>

            {status && (
              <div className="p-3 mb-6 text-sm rounded-md font-normal text-green-600 bg-green-100 border border-green-200">
                {status}
              </div>
            )}

            <form onSubmit={handleSubmit}>
              {error && (
                <div className="mb-4 text-sm text-red-600 bg-red-100 border border-red-200 p-2 rounded text-left">
                  {error}
                </div>
              )}

              <input type="hidden" name="token" value={token} />
              <input type="hidden" name="email" value={email} />

              <div className="text-start">
                <label
                  htmlFor="Password"
                  className="inline-block mb-2 text-sm text-default-800 font-medium"
                >
                  Password
                </label>
                <input 
                  type="password" 
                  id="Password" 
                  className="form-input" 
                  placeholder="Password" 
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  required
                />
              </div>

              <div className="text-start mt-4">
                <label
                  htmlFor="PasswordConfirm"
                  className="inline-block mb-2 text-sm text-default-800 font-medium"
                >
                  Confirm Password
                </label>
                <input
                  type="password"
                  id="PasswordConfirm"
                  className="form-input"
                  placeholder="Confirm Password"
                  value={passwordConfirmation}
                  onChange={(e) => setPasswordConfirmation(e.target.value)}
                  required
                />
              </div>

              <div className="mt-8">
                <button type="submit" disabled={loading} className="btn bg-primary text-white w-full">
                  {loading ? 'Resetting...' : 'Reset Password'}
                </button>
              </div>
              <div className="mt-4 text-center">
                <p className="text-base text-default-800">
                  Hold on, I've got my password...{' '}
                  <Link to="/basic-login" className="text-primary underline">
                    {' '}
                    Click here{' '}
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
