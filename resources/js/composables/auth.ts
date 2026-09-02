import type { Router } from 'vue-router';
import { computed, ref } from 'vue';

export interface User {
  id: number;
  name: string;
  email: string;
  role: 'admin' | 'teacher' | 'representative';
  avatar?: string;
}

const token = ref<string | null>(localStorage.getItem('token'));
const user = ref<User | null>(null);
const loading = ref(false);

export function useAuth(router?: Router) {
  const isAuthenticated = computed(() => !!token.value && !!user.value);

  function setSession(newToken: string, newUser: User): void {
    token.value = newToken;
    user.value = newUser;
    localStorage.setItem('token', newToken);
  }

  async function fetchUser(): Promise<void> {
    if (!token.value) {
      return;
    }

    loading.value = true;

    try {
      const response = await fetch('/api/user', {
        headers: {
          Authorization: `Bearer ${token.value}`,
          Accept: 'application/json',
        },
      });

      if (!response.ok) {
        throw new Error('Sesión inválida.');
      }

      user.value = (await response.json()) as User;
    } catch {
      await logout();
    } finally {
      loading.value = false;
    }
  }

  async function logout(): Promise<void> {
    try {
      if (token.value) {
        await fetch('/api/logout', {
          method: 'POST',
          headers: {
            Authorization: `Bearer ${token.value}`,
            Accept: 'application/json',
          },
        });
      }
    } finally {
      token.value = null;
      user.value = null;
      localStorage.removeItem('token');

      if (router) {
        await router.push('/login');
      }
    }
  }

  function hasRole(roles: string[]): boolean {
    return !!user.value && roles.includes(user.value.role);
  }

  return {
    token,
    user,
    loading,
    isAuthenticated,
    setSession,
    fetchUser,
    logout,
    hasRole,
  };
}
