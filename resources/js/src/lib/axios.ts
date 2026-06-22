import Axios from 'axios';

const axios = Axios.create({
    baseURL: import.meta.env.VITE_API_URL || '',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
    },
    withCredentials: true,
});
axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response && error.response.status === 422) {
            const errors = error.response.data.errors;
            if (errors) {
                const firstError = Object.values(errors)[0];
                if (Array.isArray(firstError)) {
                    alert('Error de validación: ' + firstError[0]);
                } else {
                    alert('Error de validación: Datos incorrectos.');
                }
            } else if (error.response.data.message) {
                alert('Error de validación: ' + error.response.data.message);
            }
        }
        return Promise.reject(error);
    }
);
export default axios;
