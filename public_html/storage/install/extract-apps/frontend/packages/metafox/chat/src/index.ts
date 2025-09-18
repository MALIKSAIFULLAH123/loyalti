import LaravelEcho from 'laravel-echo';

const Echo = new LaravelEcho({
  broadcaster: 'pusher'
});

Echo.channel('chat').listen('messages', e => {
  console.log('echo', e);
});
