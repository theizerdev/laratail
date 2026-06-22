import { useState, useEffect } from 'react';
import { Link } from 'react-router';
import axios from '@/lib/axios';
import {
  LuChevronLeft,
  LuChevronRight,
  LuCircleCheck,
  LuCircleX,
  LuDownload,
  LuEllipsis,
  LuEye,
  LuLoader,
  LuPlus,
  LuSearch,
  LuSlidersHorizontal,
  LuSquarePen,
  LuTrash2,
} from 'react-icons/lu';
import UserModal from './UserModal';

type User = {
  id: number;
  name: string;
  email: string;
  status: string;
  telefono: string | null;
  empresa?: { id: number; nombre: string };
  sucursal?: { id: number; nombre: string };
  roles?: { id: number; name: string }[];
  created_at: string;
};

const UserListTabel = () => {
  const [users, setUsers] = useState<User[]>([]);
  const [loading, setLoading] = useState(true);

  // Modal State
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingUser, setEditingUser] = useState<User | null>(null);

  // Dropdown state for actions
  const [activeDropdownId, setActiveDropdownId] = useState<number | null>(null);

  const fetchUsers = async () => {
    try {
      setLoading(true);
      const res = await axios.get('/api/admin/usuarios');
      setUsers(Array.isArray(res.data) ? res.data : []);
    } catch (error) {
      console.error('Error fetching users:', error);
      setUsers([]);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchUsers();
  }, []);

  // Close dropdown on outside click
  useEffect(() => {
    const handleClickOutside = () => setActiveDropdownId(null);
    window.addEventListener('click', handleClickOutside);
    return () => window.removeEventListener('click', handleClickOutside);
  }, []);

  const openCreateModal = () => {
    setEditingUser(null);
    setIsModalOpen(true);
  };

  const openEditModal = (user: User) => {
    setEditingUser(user);
    setIsModalOpen(true);
  };

  const handleDelete = async (id: number) => {
    if (!confirm('¿Estás seguro de que deseas eliminar este usuario?')) return;
    try {
      await axios.delete(`/api/admin/usuarios/${id}`);
      fetchUsers();
    } catch (error: any) {
      if (error.response?.status === 403) {
        alert(error.response.data.message || 'No tienes permisos.');
      } else {
        alert('Error eliminando el usuario.');
      }
    }
  };

  const getInitials = (name: string) => {
    const parts = name.split(' ');
    if (parts.length >= 2) {
      return parts[0].charAt(0).toUpperCase() + parts[1].charAt(0).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
  };

  return (
    <div className="card">
      <div className="card-header">
        <h6 className="card-title">Lista de Usuarios</h6>
        <button onClick={openCreateModal} className="btn btn-sm bg-primary text-white">
          <LuPlus className="size-4 me-1" />
          Añadir Usuario
        </button>
      </div>

      <div className="card-header">
        <div className="md:flex items-center md:space-y-0 space-y-4 gap-3">
          <div className="relative">
            <input
              type="text"
              className="form-input form-input-sm ps-9"
              placeholder="Buscar por nombre o correo"
            />
            <div className="absolute inset-y-0 start-0 flex items-center ps-3">
              <LuSearch className="size-3.5 flex items-center text-default-500 fill-default-100" />
            </div>
          </div>

          <select className="form-input form-input-sm">
            <option value="">Todos los Estados</option>
            <option value="activo">Activo</option>
            <option value="inactivo">Inactivo</option>
          </select>
        </div>

        <div className="flex gap-2 items-center flex-wrap">
          <button
            type="button"
            className="btn btn-sm bg-transparent border border-dashed border-primary text-primary hover:bg-primary/10"
          >
            <LuDownload className="size-4" />
            Exportar
          </button>
        </div>
      </div>

      <div className="flex flex-col">
        <div className="overflow-x-auto">
          <div className="min-w-full inline-block align-middle">
            <div className="overflow-visible">
              <table className="min-w-full divide-y divide-default-200">
                <thead className="bg-default-150">
                  <tr className="text-sm font-normal text-default-700 whitespace-nowrap">
                    <th className="px-3.5 py-3 text-start">Usuario</th>
                    <th className="px-3.5 py-3 text-start">Email</th>
                    <th className="px-3.5 py-3 text-start">Teléfono</th>
                    <th className="px-3.5 py-3 text-start">Empresa</th>
                    <th className="px-3.5 py-3 text-start">Sucursal</th>
                    <th className="px-3.5 py-3 text-start">Roles</th>
                    <th className="px-3.5 py-3 text-start">Estado</th>
                    <th className="px-3.5 py-3 text-start">Acciones</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-default-200">
                  {loading ? (
                    <tr>
                      <td colSpan={8} className="py-8 text-center text-default-500">
                        Cargando usuarios...
                      </td>
                    </tr>
                  ) : users.length === 0 ? (
                    <tr>
                      <td colSpan={8} className="py-8 text-center text-default-500">
                        No hay usuarios registrados.
                      </td>
                    </tr>
                  ) : (
                    users.map(user => (
                      <tr
                        key={user.id}
                        className="text-default-800 font-normal text-sm whitespace-nowrap"
                      >
                        <td className="flex py-3 px-3.5 items-center gap-3">
                          <div className="w-10 h-10 flex items-center justify-center rounded-full bg-primary/10 text-primary font-semibold">
                            {getInitials(user.name)}
                          </div>
                          <div>
                            <h6 className="mb-0.5 font-semibold text-default-800">
                              {user.name}
                            </h6>
                            <p className="text-default-500 text-xs">ID: {user.id}</p>
                          </div>
                        </td>
                        <td className="py-3 px-3.5">{user.email}</td>
                        <td className="py-3 px-3.5">{user.telefono || '-'}</td>
                        <td className="py-3 px-3.5">
                          {user.empresa?.map((e: any) => (
                            <span key={e.id} className="px-2 py-1 bg-default-100 text-xs rounded-md">
                              {e.razon_social}
                            </span>
                          )) || '-'}
                        </td>
                        <td className="py-3 px-3.5">
                          {user.sucursales?.map((s: any) => (
                            <span key={s.id} className="px-2 py-1 bg-default-100 text-xs rounded-md">
                              {s.nombre}
                            </span>
                          )) || '-'}
                        </td>
                        <td className="py-3 px-3.5">
                          <div className="flex gap-1 flex-wrap">
                            {user.roles && user.roles.length > 0 ? user.roles.map(r => (
                              <span key={r.id} className="px-2 py-1 bg-default-100 text-xs rounded-md">
                                {r.name}
                              </span>
                            )) : '-'}
                          </div>
                        </td>
                        <td className="px-3.5 py-3">
                          {user.status === 'activo' ? (
                            <span className="py-0.5 px-2.5 inline-flex items-center gap-x-1 text-xs font-medium bg-success/10 text-success rounded">
                              <LuCircleCheck className="size-3" />
                              Activo
                            </span>
                          ) : (
                            <span className="py-0.5 px-2.5 inline-flex items-center gap-x-1 text-xs font-medium bg-danger/10 text-danger rounded">
                              <LuCircleX className="size-3" />
                              Inactivo
                            </span>
                          )}
                        </td>
                        <td className="px-3.5 py-3">
                          <div className="relative inline-block text-left">
                            <button
                              onClick={(e) => {
                                e.stopPropagation();
                                setActiveDropdownId(activeDropdownId === user.id ? null : user.id);
                              }}
                              className="btn size-7.5 bg-default-200 hover:bg-default-300 text-default-500 rounded-md flex items-center justify-center"
                            >
                              <LuEllipsis className="size-4" />
                            </button>

                            {activeDropdownId === user.id && (
                              <div className="absolute right-0 mt-2 w-32 rounded-md shadow-lg bg-white dark:bg-default-50 ring-1 ring-black ring-opacity-5 z-10 border border-default-200">
                                <div className="py-1" role="menu">
                                  <button
                                    onClick={() => openEditModal(user)}
                                    className="w-full text-left px-4 py-2 text-sm text-default-700 hover:bg-default-100 flex items-center gap-2"
                                  >
                                    <LuSquarePen className="size-4" /> Editar
                                  </button>
                                  <button
                                    onClick={() => handleDelete(user.id)}
                                    className="w-full text-left px-4 py-2 text-sm text-danger hover:bg-danger/10 flex items-center gap-2"
                                  >
                                    <LuTrash2 className="size-4" /> Eliminar
                                  </button>
                                </div>
                              </div>
                            )}
                          </div>
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <UserModal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        user={editingUser}
        onSuccess={fetchUsers}
      />
    </div>
  );
};

export default UserListTabel;
