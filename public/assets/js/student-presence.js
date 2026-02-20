(function(){
  const cfg = window.__LAB_STUDENT_PRESENCE__ || null;
  if(!cfg || !cfg.enabled) return;

  const endpoint = '/api/session/heartbeat';
  const ACTIVE_PING_MS = 2000;
  const AWAY_REFRESH_MS = 12000;

  let timer = null;
  let loopMs = 0;
  let lastKey = '';
  let lastSentAt = 0;

  function pageType(){
    const path = (location.pathname || '').toLowerCase().replace(/\/+$/, '');
    if(path.endsWith('/student')) return 'session';
    if(path.endsWith('/student/settings')) return 'settings';
    if(path.endsWith('/about')) return 'about';
    return 'other';
  }

  function buildPresence(offlineReason){
    const page = pageType();
    const allowedPage = (page === 'session' || page === 'settings' || page === 'about');

    if(offlineReason){
      return { presence: 'offline', page, reason: offlineReason };
    }

    if(!allowedPage){
      return { presence: 'away', page, reason: 'outside_session_page' };
    }

    if(document.hidden || document.visibilityState === 'hidden'){
      return { presence: 'away', page, reason: 'tab_hidden' };
    }

    return { presence: 'active', page, reason: 'active' };
  }

  function serialize(data){
    const params = new URLSearchParams();
    params.append('presence', data.presence);
    params.append('page', data.page);
    params.append('reason', data.reason);
    return params;
  }

  function toFormData(data){
    const form = new FormData();
    form.append('presence', data.presence);
    form.append('page', data.page);
    form.append('reason', data.reason);
    return form;
  }

  function send(data, useBeacon, force){
    const now = Date.now();
    const key = `${data.presence}|${data.page}|${data.reason}`;

    if(!force){
      const isDuplicate = key === lastKey;
      const tooSoon = (now - lastSentAt) < 600;
      if(isDuplicate && tooSoon) return;
    }

    lastKey = key;
    lastSentAt = now;

    if(useBeacon && navigator.sendBeacon){
      try{
        navigator.sendBeacon(endpoint, toFormData(data));
        return;
      }catch(e){}
    }

    const body = serialize(data);
    fetch(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body.toString(),
      credentials: 'same-origin',
      keepalive: !!useBeacon,
    }).catch(()=>{});
  }

  function pulse(force){
    const data = buildPresence('');
    send(data, false, !!force);
    const waitMs = data.presence === 'active' ? ACTIVE_PING_MS : AWAY_REFRESH_MS;
    if(waitMs !== loopMs){
      startLoop(waitMs);
    }
  }

  function onVisibilityChanged(){
    pulse(true);
  }

  function onPageHide(){
    send(buildPresence('pagehide'), true, true);
  }

  function onBeforeUnload(){
    send(buildPresence('browser_closed'), true, true);
  }

  function startLoop(intervalMs){
    if(timer) clearInterval(timer);
    loopMs = intervalMs;
    timer = setInterval(()=>{
      const data = buildPresence('');
      const waitMs = data.presence === 'active' ? ACTIVE_PING_MS : AWAY_REFRESH_MS;

      if(waitMs !== loopMs){
        startLoop(waitMs);
        return;
      }

      send(data, false, false);
    }, loopMs);
  }

  document.addEventListener('visibilitychange', onVisibilityChanged, { passive: true });
  window.addEventListener('pageshow', ()=> pulse(true), { passive: true });
  window.addEventListener('focus', ()=> pulse(true), { passive: true });
  window.addEventListener('pagehide', onPageHide, { passive: true });
  window.addEventListener('beforeunload', onBeforeUnload, { passive: true });

  pulse(true);
})();
