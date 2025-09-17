import '@metafox/framework/Manager';
import Echo from "laravel-echo";


declare module '@metafox/framework/Manager' {
    interface Manager {
        echoBackend: Echo
    }
}
