import { useState } from 'react';
import axios from '@/lib/axios';
import PageMeta from '@/components/PageMeta';
import { useAuth } from '@/context/AuthContext';

// UI Components
import ModuleHeader from '@/components/ui/ModuleHeader';
import { Card, CardHeader, CardTitle, CardContent, CardFooter, CardDescription } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { LuUser, LuKey, LuSave, LuShieldCheck, LuSmartphone } from 'react-icons/lu';

const Perfil = () => {
  const { user } = useAuth();
  
  const [profileData, setProfileData] = useState({
    name: user?.name || '',
    email: user?.email || '',
  });

  const [passwordData, setPasswordData] = useState({
    current_password: '',
    password: '',
    password_confirmation: '',
  });

  const [isLoadingProfile, setIsLoadingProfile] = useState(false);
  const [isLoadingPassword, setIsLoadingPassword] = useState(false);

  const handleProfileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setProfileData({ ...profileData, [e.target.name]: e.target.value });
  };

  const handlePasswordChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setPasswordData({ ...passwordData, [e.target.name]: e.target.value });
  };

  const submitProfile = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoadingProfile(true);
    try {
      await axios.post('/api/profile/update', profileData);
      alert('Perfil actualizado con éxito');
    } catch (error) {
      console.error(error);
      alert('Error al actualizar perfil');
    } finally {
      setIsLoadingProfile(false);
    }
  };

  const submitPassword = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoadingPassword(true);
    try {
      await axios.post('/api/profile/password', passwordData);
      alert('Contraseña actualizada con éxito');
      setPasswordData({ current_password: '', password: '', password_confirmation: '' });
    } catch (error) {
      console.error(error);
      alert('Error al actualizar contraseña. Verifica tu contraseña actual.');
    } finally {
      setIsLoadingPassword(false);
    }
  };

  // 2FA States
  const [is2faEnabled, setIs2faEnabled] = useState(user?.google2fa_enabled || false);
  const [qrCodeSvg, setQrCodeSvg] = useState('');
  const [secret2fa, setSecret2fa] = useState('');
  const [code2fa, setCode2fa] = useState('');
  const [disablePassword, setDisablePassword] = useState('');
  const [isGenerating2fa, setIsGenerating2fa] = useState(false);
  const [isEnabling2fa, setIsEnabling2fa] = useState(false);
  const [isDisabling2fa, setIsDisabling2fa] = useState(false);

  const generate2fa = async () => {
    setIsGenerating2fa(true);
    try {
      const res = await axios.get('/api/profile/2fa/generate');
      setQrCodeSvg(res.data.qr_code_svg);
      setSecret2fa(res.data.secret);
    } catch (error) {
      alert('Error al generar código QR.');
    } finally {
      setIsGenerating2fa(false);
    }
  };

  const enable2fa = async () => {
    setIsEnabling2fa(true);
    try {
      await axios.post('/api/profile/2fa/enable', { code: code2fa, secret: secret2fa });
      alert('Autenticación de Dos Pasos activada con éxito.');
      setIs2faEnabled(true);
      setQrCodeSvg('');
      setCode2fa('');
    } catch (error: any) {
      alert(error.response?.data?.message || 'Código inválido.');
    } finally {
      setIsEnabling2fa(false);
    }
  };

  const disable2fa = async () => {
    setIsDisabling2fa(true);
    try {
      await axios.post('/api/profile/2fa/disable', { password: disablePassword });
      alert('Autenticación de Dos Pasos desactivada.');
      setIs2faEnabled(false);
      setDisablePassword('');
    } catch (error: any) {
      alert(error.response?.data?.message || 'Contraseña inválida.');
    } finally {
      setIsDisabling2fa(false);
    }
  };

  return (
    <>
      <PageMeta title="Mi Perfil" description="Gestión de cuenta" />
      
      <main className="space-y-6">
        <ModuleHeader
          title="Mi Perfil"
          description="Gestión de tu información personal y opciones de seguridad de tu cuenta."
          icon={<LuUser className="size-8" />}
          breadcrumbs={[
            { label: 'Configuración', active: false },
            { label: 'Mi Perfil', active: true }
          ]}
        />

        <div className="grid lg:grid-cols-2 grid-cols-1 gap-6">
          {/* Tarjeta de Datos Personales */}
          <Card>
            <CardHeader className="border-b border-gray-100 bg-gray-50/50 pb-4">
              <div className="flex items-center gap-2">
                <div className="p-2 bg-primary/10 rounded-lg text-primary">
                  <LuUser className="size-5" />
                </div>
                <div>
                  <CardTitle className="text-lg">Datos Personales</CardTitle>
                  <CardDescription>Actualiza tu información básica de contacto.</CardDescription>
                </div>
              </div>
            </CardHeader>
            <form onSubmit={submitProfile}>
              <CardContent className="space-y-4 pt-6">
                <div className="space-y-2">
                  <label className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-gray-700">
                    Nombre Completo
                  </label>
                  <Input
                    type="text"
                    name="name"
                    value={profileData.name}
                    onChange={handleProfileChange}
                    required
                    placeholder="Tu nombre"
                  />
                </div>
                <div className="space-y-2">
                  <label className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-gray-700">
                    Correo Electrónico
                  </label>
                  <Input
                    type="email"
                    name="email"
                    value={profileData.email}
                    onChange={handleProfileChange}
                    required
                    placeholder="tucorreo@ejemplo.com"
                  />
                </div>
              </CardContent>
              <CardFooter className="border-t border-gray-100 pt-6">
                <Button 
                  type="submit" 
                  disabled={isLoadingProfile}
                  className="w-full sm:w-auto"
                >
                  {isLoadingProfile ? (
                    <div className="animate-spin size-4 border-[2px] border-white border-t-transparent rounded-full mr-2"></div>
                  ) : (
                    <LuSave className="mr-2 size-4" />
                  )}
                  Guardar Cambios
                </Button>
              </CardFooter>
            </form>
          </Card>

          {/* Tarjeta de Seguridad */}
          <Card>
            <CardHeader className="border-b border-gray-100 bg-gray-50/50 pb-4">
              <div className="flex items-center gap-2">
                <div className="p-2 bg-danger/10 rounded-lg text-danger">
                  <LuShieldCheck className="size-5" />
                </div>
                <div>
                  <CardTitle className="text-lg">Seguridad de la Cuenta</CardTitle>
                  <CardDescription>Asegúrate de usar una contraseña larga y segura.</CardDescription>
                </div>
              </div>
            </CardHeader>
            <form onSubmit={submitPassword}>
              <CardContent className="space-y-4 pt-6">
                <div className="space-y-2">
                  <label className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-gray-700">
                    Contraseña Actual
                  </label>
                  <div className="relative">
                    <LuKey className="absolute left-3 top-3 text-gray-400 size-4" />
                    <Input
                      type="password"
                      name="current_password"
                      value={passwordData.current_password}
                      onChange={handlePasswordChange}
                      required
                      placeholder="••••••••"
                      className="pl-9"
                    />
                  </div>
                </div>
                <div className="space-y-2">
                  <label className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-gray-700">
                    Nueva Contraseña
                  </label>
                  <Input
                    type="password"
                    name="password"
                    value={passwordData.password}
                    onChange={handlePasswordChange}
                    required
                    placeholder="••••••••"
                  />
                </div>
                <div className="space-y-2">
                  <label className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-gray-700">
                    Confirmar Nueva Contraseña
                  </label>
                  <Input
                    type="password"
                    name="password_confirmation"
                    value={passwordData.password_confirmation}
                    onChange={handlePasswordChange}
                    required
                    placeholder="••••••••"
                  />
                </div>
              </CardContent>
              <CardFooter className="border-t border-gray-100 pt-6">
                <Button 
                  type="submit" 
                  variant="destructive"
                  disabled={isLoadingPassword}
                  className="w-full sm:w-auto"
                >
                  {isLoadingPassword ? (
                    <div className="animate-spin size-4 border-[2px] border-white border-t-transparent rounded-full mr-2"></div>
                  ) : (
                    <LuShieldCheck className="mr-2 size-4" />
                  )}
                  Actualizar Contraseña
                </Button>
              </CardFooter>
            </form>
          </Card>
          
          {/* Tarjeta de 2FA */}
          <Card className="lg:col-span-2">
            <CardHeader className="border-b border-gray-100 bg-gray-50/50 pb-4">
              <div className="flex items-center gap-2">
                <div className="p-2 bg-indigo-500/10 rounded-lg text-indigo-500">
                  <LuSmartphone className="size-5" />
                </div>
                <div>
                  <CardTitle className="text-lg">Autenticación de Dos Pasos (2FA)</CardTitle>
                  <CardDescription>Añade una capa adicional de seguridad a tu cuenta usando una aplicación autenticadora (ej. Google Authenticator).</CardDescription>
                </div>
              </div>
            </CardHeader>
            <CardContent className="pt-6 space-y-4">
              {is2faEnabled ? (
                <div className="space-y-4">
                  <div className="p-4 bg-green-500/10 text-green-700 rounded-md border border-green-500/20">
                    <p className="font-semibold flex items-center gap-2"><LuShieldCheck className="size-5" /> Autenticación de Dos Pasos Activada</p>
                    <p className="text-sm mt-1">Tu cuenta está protegida actualmente.</p>
                  </div>
                  <div className="space-y-2 max-w-sm">
                    <label className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-gray-700">
                      Para desactivar el 2FA, ingresa tu contraseña actual:
                    </label>
                    <Input
                      type="password"
                      value={disablePassword}
                      onChange={(e) => setDisablePassword(e.target.value)}
                      placeholder="••••••••"
                    />
                    <Button 
                      variant="destructive" 
                      onClick={disable2fa} 
                      disabled={!disablePassword || isDisabling2fa}
                    >
                      {isDisabling2fa ? 'Desactivando...' : 'Desactivar 2FA'}
                    </Button>
                  </div>
                </div>
              ) : (
                <div className="space-y-4">
                  {!qrCodeSvg ? (
                    <div>
                      <p className="text-sm text-gray-600 mb-4">El 2FA no está habilitado. Al activarlo, requerirás un código generado por tu aplicación móvil para iniciar sesión.</p>
                      <Button onClick={generate2fa} disabled={isGenerating2fa} variant="default">
                        {isGenerating2fa ? 'Generando...' : 'Configurar 2FA'}
                      </Button>
                    </div>
                  ) : (
                    <div className="flex flex-col md:flex-row gap-6 items-start">
                      <div className="bg-white p-4 border rounded-md shadow-sm">
                        <div dangerouslySetInnerHTML={{ __html: qrCodeSvg }} className="size-40" />
                      </div>
                      <div className="space-y-4 flex-grow max-w-md">
                        <h4 className="font-semibold text-gray-800">1. Escanea el Código QR</h4>
                        <p className="text-sm text-gray-600">Abre tu aplicación de autenticación (Google Authenticator, Authy, etc) y escanea este código QR.</p>
                        
                        <div className="pt-2">
                          <h4 className="font-semibold text-gray-800 mb-2">2. Ingresa el código generado</h4>
                          <div className="flex gap-2">
                            <Input
                              type="text"
                              value={code2fa}
                              onChange={(e) => setCode2fa(e.target.value)}
                              placeholder="Ej. 123456"
                              className="max-w-[150px] text-center tracking-widest font-mono text-lg"
                              maxLength={6}
                            />
                            <Button onClick={enable2fa} disabled={code2fa.length < 6 || isEnabling2fa}>
                              {isEnabling2fa ? 'Verificando...' : 'Verificar y Activar'}
                            </Button>
                          </div>
                        </div>
                      </div>
                    </div>
                  )}
                </div>
              )}
            </CardContent>
          </Card>
        </div>
      </main>
    </>
  );
};

export default Perfil;
