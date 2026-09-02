import { ref } from 'vue';

const subscribed = ref(false);
const loading = ref(false);
const error = ref('');

function urlBase64ToArrayBuffer(base64String: string): ArrayBuffer {
  const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
  const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
  const rawData = atob(base64);
  const outputBuffer = new ArrayBuffer(rawData.length);
  const outputArray = new Uint8Array(outputBuffer);

  for (let i = 0; i < rawData.length; i++) {
    outputArray[i] = rawData.charCodeAt(i);
  }

  return outputBuffer;
}

export function usePushNotifications() {
  const isSupported =
    typeof navigator !== 'undefined' &&
    'serviceWorker' in navigator &&
    'PushManager' in window;

  async function fetchPublicKey(): Promise<string> {
    const response = await fetch('/api/vapid-public-key', {
      headers: { Accept: 'application/json' },
    });

    if (!response.ok) {
      throw new Error('No se pudo obtener la clave pública.');
    }

    const data = (await response.json()) as { public_key: string };

    return data.public_key;
  }

  async function checkSubscription(): Promise<boolean> {
    if (!isSupported) {
      return false;
    }

    const registration = await navigator.serviceWorker.ready;
    const subscription = await registration.pushManager.getSubscription();

    subscribed.value = subscription !== null;

    return subscribed.value;
  }

  async function subscribe(): Promise<void> {
    if (!isSupported) {
      error.value = 'Las notificaciones push no están soportadas.';

      return;
    }

    loading.value = true;
    error.value = '';

    try {
      const permission = await Notification.requestPermission();

      if (permission !== 'granted') {
        throw new Error('Permiso de notificaciones denegado.');
      }

      const publicKey = await fetchPublicKey();
      const registration = await navigator.serviceWorker.ready;

      const subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToArrayBuffer(publicKey),
      });

      const token = localStorage.getItem('token') ?? '';
      const response = await fetch('/api/push/subscriptions', {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify(subscription.toJSON()),
      });

      if (!response.ok) {
        throw new Error('No se pudo guardar la suscripción.');
      }

      subscribed.value = true;
    } catch (exception) {
      error.value =
        exception instanceof Error ? exception.message : 'Error desconocido.';
    } finally {
      loading.value = false;
    }
  }

  async function unsubscribe(): Promise<void> {
    if (!isSupported) {
      return;
    }

    loading.value = true;
    error.value = '';

    try {
      const registration = await navigator.serviceWorker.ready;
      const subscription = await registration.pushManager.getSubscription();

      if (subscription) {
        await subscription.unsubscribe();

        const token = localStorage.getItem('token') ?? '';
        await fetch('/api/push/subscriptions', {
          method: 'DELETE',
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json',
            Accept: 'application/json',
          },
          body: JSON.stringify({ endpoint: subscription.endpoint }),
        });
      }

      subscribed.value = false;
    } catch (exception) {
      error.value =
        exception instanceof Error ? exception.message : 'Error desconocido.';
    } finally {
      loading.value = false;
    }
  }

  return {
    isSupported,
    subscribed,
    loading,
    error,
    subscribe,
    unsubscribe,
    checkSubscription,
  };
}
