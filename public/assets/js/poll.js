(function(){
  function sleep(ms){ return new Promise(r=>setTimeout(r,ms)); }

  class EventPoller {
    constructor(opts){
      opts = opts || {};

      this.since = Number(opts.since || 0);
      if(!Number.isFinite(this.since) || this.since < 0) this.since = 0;

      this.running = false;
      this.intervalMs = Number(opts.intervalMs || 1200);
      if(!Number.isFinite(this.intervalMs) || this.intervalMs < 200) this.intervalMs = 1200;

      // Optional: base URL prefix (mis. kalau app di subfolder)
      this.base = (opts.base != null ? String(opts.base) : (window.__LAB_BASE__ || '')) || '';
      this.base = this.base.replace(/\/+$/,''); // trim trailing slash

      // Optional: endpoint override
      this.endpoint = opts.endpoint || '/api/events/poll';

      // callbacks
      this.onEvents = opts.onEvents || function(){};
      this.onSnapshot = opts.onSnapshot || function(){};
      this.onPresence = opts.onPresence || function(){};
      this.onError = opts.onError || function(){};

      // Optional: persist since to localStorage (berguna saat refresh halaman)
      // Contoh penggunaan:
      // new EventPoller({ persistKey: 'lab_since_student_123' })
      this.persistKey = opts.persistKey || null;
      if(this.persistKey){
        const saved = Number(localStorage.getItem(this.persistKey) || 0);
        if(Number.isFinite(saved) && saved > this.since) this.since = saved;
      }

      // Internal controls
      this._abort = null;
      this._backoff = 0;        // dynamic delay on error
      this._maxBackoff = 10000; // 10s cap
      this._jitter = 250;       // random jitter max (ms)
      this._inflight = false;
    }

    _url(){
      const u = `${this.base}${this.endpoint}?since=${encodeURIComponent(this.since)}`;
      return u;
    }

    _bumpSince(lastId){
      const n = Number(lastId);
      if(Number.isFinite(n) && n > this.since){
        this.since = n;
        if(this.persistKey){
          try{ localStorage.setItem(this.persistKey, String(this.since)); }catch(e){}
        }
      }
    }

    async _tick(){
      if(this._inflight) return;
      this._inflight = true;

      // Abort controller for stop()
      const ac = new AbortController();
      this._abort = ac;

      try{
        const res = await fetch(this._url(), {
          headers: {'Accept':'application/json'},
          signal: ac.signal
        });

        // If stopped while awaiting
        if(!this.running) return;

        if(!res.ok){
          // e.g. 401/403/500
          this.onError({ type:'http', status: res.status });
          throw new Error('Kode status ' + res.status);
        }

        const data = await res.json().catch(()=>null);

        if(data && data.ok){
          // Snapshot (optional)
          if(data.snapshot){
            // allow async callback
            await Promise.resolve(this.onSnapshot(data.snapshot));
          }

          // Presence (optional)
          if(Array.isArray(data.presence)){
            await Promise.resolve(this.onPresence(data.presence));
          }

          // Events (optional)
          if(Array.isArray(data.events) && data.events.length){
            await Promise.resolve(this.onEvents(data.events));
          }

          // last_id update
          this._bumpSince(data.last_id);

          // reset backoff on success
          this._backoff = 0;
        } else {
          // Server responded but not ok (still treat as soft error)
          this.onError({ type:'payload', data });
          // mild backoff
          this._backoff = Math.min(this._maxBackoff, Math.max(this._backoff || 800, 800));
        }
      } catch(e){
        // Abort is normal on stop()
        if(e && e.name === 'AbortError') {
          // ignore
        } else {
          // exponential backoff on network/server failure
          this._backoff = this._backoff ? Math.min(this._maxBackoff, this._backoff * 1.6) : 900;
          this.onError({ type:'exception', error: e });
        }
      } finally {
        this._inflight = false;
      }
    }

    async start(){
      // idempotent
      if(this.running) return;
      this.running = true;

      while(this.running){
        await this._tick();

        // Interval dynamic:
        // - base interval
        // - plus backoff if error
        // - plus small jitter to avoid thundering herd
        let delay = this.intervalMs + (this._backoff || 0) + Math.floor(Math.random() * this._jitter);

        // Optional optimization: when tab hidden, polling lebih jarang
        if(document && document.hidden){
          delay = Math.max(delay, 2500);
        }

        await sleep(delay);
      }
    }

    stop(){
      this.running = false;
      // cancel inflight request
      try{
        if(this._abort) this._abort.abort();
      }catch(e){}
      this._abort = null;
    }
  }

  window.EventPoller = EventPoller;
})();
