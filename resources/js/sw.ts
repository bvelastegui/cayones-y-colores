/// <reference lib="webworker" />
import { cleanupOutdatedCaches, precacheAndRoute } from 'workbox-precaching';
import { clientsClaim } from 'workbox-core';

declare const self: ServiceWorkerGlobalScope;

self.skipWaiting();
clientsClaim();
cleanupOutdatedCaches();
precacheAndRoute(self.__WB_MANIFEST);

interface PushPayload {
    title?: string;
    body?: string;
    data?: {
        url?: string;
        [key: string]: unknown;
    };
}

self.addEventListener('push', (event: PushEvent) => {
    let payload: PushPayload = {};

    try {
        payload = event.data?.json() as PushPayload;
    } catch {
        payload = { body: event.data?.text() };
    }

    const title = payload.title ?? 'Crayones y Colores';
    const options: NotificationOptions = {
        body: payload.body ?? 'Tienes una nueva notificación.',
        icon: '/icon.svg',
        badge: '/icon.svg',
        data: payload.data ?? {},
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event: NotificationEvent) => {
    event.notification.close();

    const url = (event.notification.data?.url as string) ?? '/';

    event.waitUntil(self.clients.openWindow(url));
});
