(function(){
  if(window.__LAB_ROLE__ !== 'student') return;

  const myId = Number(window.__LAB_PARTICIPANT_ID__||0);
  const peersList = document.getElementById('peersList');
  const btnMic = document.getElementById('btnMic');
  const chatLog = document.getElementById('chatLog');
  const chatInput = document.getElementById('chatInput');
  const btnSendChat = document.getElementById('btnSendChat');
  const materialViewer = document.getElementById('materialViewer');
  const btnRefreshMaterial = document.getElementById('btnRefreshMaterial');
  const broadcastBox = document.getElementById('broadcastBox');

  // Voice UI
  const btnEnableAudio = document.getElementById('btnEnableAudio');
  const audioStatusEl = document.getElementById('audioStatus');
  const remoteAudio = document.getElementById('remoteAudio');
  const selMic = document.getElementById('selMic');
  const selSpk = document.getElementById('selSpk');

  const state = {
    peers: new Map(),
    myMicOn: false,
    mySpeakerOn: true,
    audioUnlocked: false,
    selectedMicId: '',
    selectedSpkId: '',
    devicePermissionAsked: false,
  };

  const rtc = {
    pc: null,
    callId: null,
    pendingCandidates: [],
    localStream: null,
    localTrack: null,
  };

  const MAX_PENDING_CANDIDATES = 140; // opsional: cegah memori meledak
  const IS_SECURE_CONTEXT = (location.protocol === 'https:' ||
    location.hostname === 'localhost' ||
    location.hostname === '127.0.0.1');
  const ALLOW_INSECURE_MEDIA = !!window.__LAB_ALLOW_INSECURE_MEDIA__;
  const STORAGE = {
    mic: 'lab_student_mic_id',
    spk: 'lab_student_spk_id',
  };

  function esc(s){
    return (s??'').toString().replace(/[&<>"]/g, c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;' }[c]));
  }

  function setAudioStatus(text){
    if(audioStatusEl) audioStatusEl.textContent = text || '';
  }

  function getSaved(key){
    try{ return localStorage.getItem(key) || ''; }catch(e){ return ''; }
  }
  function save(key, value){
    try{
      if(value) localStorage.setItem(key, value);
      else localStorage.removeItem(key);
    }catch(e){}
  }

  function labelForDevice(d, idx){
    const name = (d && d.label) ? d.label : '';
    if(name) return name;
    if(d.kind === 'audioinput') return `Microphone ${idx+1}`;
    if(d.kind === 'audiooutput') return `Speaker ${idx+1}`;
    return `Device ${idx+1}`;
  }

  function applySpeakerDevice(){
    if(!remoteAudio || !selSpk) return;
    if(typeof remoteAudio.setSinkId !== 'function'){
      return;
    }
    const id = state.selectedSpkId || '';
    if(!id) return;
    remoteAudio.setSinkId(id).catch(()=>{});
  }

  async function refreshDevices(){
    if(!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices){
      if(selMic){
        selMic.innerHTML = '<option value="">Browser tidak mendukung pemilihan mic</option>';
        selMic.disabled = true;
      }
      if(selSpk){
        selSpk.innerHTML = '<option value="">Browser tidak mendukung pemilihan speaker</option>';
        selSpk.disabled = true;
      }
      return;
    }

    let devices = [];
    try{
      devices = await navigator.mediaDevices.enumerateDevices();
    }catch(e){
      devices = [];
    }

    const inputs = devices.filter(d=> d.kind === 'audioinput');
    const outputs = devices.filter(d=> d.kind === 'audiooutput');

    const needPermissionHint = !state.devicePermissionAsked;
    if(selMic){
      const emptyMicText = needPermissionHint ? 'Klik dropdown untuk meminta izin mic' : 'Tidak ada microphone';
      selMic.innerHTML = inputs.length
        ? inputs.map((d,i)=> `<option value="${esc(d.deviceId)}">${esc(labelForDevice(d,i))}</option>`).join('')
        : `<option value="">${emptyMicText}</option>`;
      selMic.disabled = inputs.length === 0;
    }

    const canPickOutput = !!(remoteAudio && typeof remoteAudio.setSinkId === 'function');
    if(selSpk){
      const emptySpkText = needPermissionHint ? 'Klik dropdown untuk meminta izin mic' : 'Tidak ada speaker';
      if(!canPickOutput){
        selSpk.innerHTML = '<option value="">Browser tidak mendukung pemilihan speaker</option>';
        selSpk.disabled = true;
      }else{
        selSpk.innerHTML = outputs.length
          ? outputs.map((d,i)=> `<option value="${esc(d.deviceId)}">${esc(labelForDevice(d,i))}</option>`).join('')
          : `<option value="">${emptySpkText}</option>`;
        selSpk.disabled = outputs.length === 0;
      }
    }

    if(selMic){
      const savedMic = state.selectedMicId || getSaved(STORAGE.mic);
      if(savedMic && inputs.some(d=> d.deviceId === savedMic)){
        selMic.value = savedMic;
        state.selectedMicId = savedMic;
      }else if(inputs[0]){
        selMic.value = inputs[0].deviceId;
        state.selectedMicId = inputs[0].deviceId;
      }
    }

    if(selSpk){
      const savedSpk = state.selectedSpkId || getSaved(STORAGE.spk);
      if(savedSpk && outputs.some(d=> d.deviceId === savedSpk)){
        selSpk.value = savedSpk;
        state.selectedSpkId = savedSpk;
      }else if(outputs[0] && !selSpk.disabled){
        selSpk.value = outputs[0].deviceId;
        state.selectedSpkId = outputs[0].deviceId;
      }
    }

    applySpeakerDevice();
  }

  async function getUserMediaWithSelectedMic(){
    const baseAudio = { echoCancellation: true, noiseSuppression: true, autoGainControl: true };
    let constraints = { audio: baseAudio, video: false };
    if(state.selectedMicId){
      constraints = {
        audio: { ...baseAudio, deviceId: { exact: state.selectedMicId } },
        video: false
      };
    }
    try{
      return await navigator.mediaDevices.getUserMedia(constraints);
    }catch(err){
      if(state.selectedMicId){
        return await navigator.mediaDevices.getUserMedia({ audio: baseAudio, video: false });
      }
      throw err;
    }
  }

  function getRtcConfig(){
    return window.__LAB_RTC_CONFIG__ || { iceServers: [{ urls: ['stun:stun.l.google.com:19302'] }] };
  }

  async function post(url, data){
    const form = new URLSearchParams();
    Object.keys(data||{}).forEach(k=> form.append(k, data[k]));
    const res = await fetch(url, {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body:form.toString()
    });
    return res.json().catch(()=>({ok:false}));
  }

  function applySpeakerState(){
    if(remoteAudio){
      remoteAudio.muted = !state.mySpeakerOn;
      applySpeakerDevice();
    }
  }

  function stopLocalStream(){
    if(rtc.localStream){
      rtc.localStream.getTracks().forEach(t=>{ try{ t.stop(); }catch(e){} });
    }
    rtc.localStream = null;
    rtc.localTrack = null;
  }

  async function requestDeviceAccess(){
    if(!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) return;
    state.devicePermissionAsked = true;
    try{
      const tmp = await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
      if(tmp) tmp.getTracks().forEach(t=>{ try{ t.stop(); }catch(e){} });
    }catch(err){
      setAudioStatus('Izin mic diperlukan untuk memuat perangkat: ' + (err.message||err));
    }
    refreshDevices();
  }

  async function ensureMicStreamFromUserGesture(){
    if(rtc.localStream) return rtc.localStream;

    if(!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia){
      throw new Error('Browser tidak mendukung getUserMedia');
    }

    if(!IS_SECURE_CONTEXT && !ALLOW_INSECURE_MEDIA){
      // getUserMedia umumnya ditolak jika bukan HTTPS (kecuali localhost)
      throw new Error('Mic butuh HTTPS (atau localhost).');
    }

    rtc.localStream = await getUserMediaWithSelectedMic();

    rtc.localTrack = rtc.localStream.getAudioTracks()[0] || null;
    if(rtc.localTrack) rtc.localTrack.enabled = state.myMicOn;

    // Jika peer sudah ada, tambahkan track sekarang
    if(rtc.pc && rtc.localTrack){
      try{ rtc.pc.addTrack(rtc.localTrack, rtc.localStream); }catch(e){}
    }

    return rtc.localStream;
  }

  function applyMicState(){
    if(rtc.localTrack) rtc.localTrack.enabled = state.myMicOn;
  }

  async function sendRtc(signalType, data){
    if(!rtc.callId) return;
    await post('/api/rtc/signal', {
      to_type: 'admin',
      signal_type: signalType,
      call_id: rtc.callId,
      data: JSON.stringify(data || {}),
    });
  }

  // Opsional: kirim hangup cepat saat tab ditutup
  function sendBeaconHangup(){
    try{
      if(!rtc.callId) return;
      const body = new URLSearchParams();
      body.append('to_type', 'admin');
      body.append('signal_type', 'hangup');
      body.append('call_id', rtc.callId);
      body.append('data', '{}');

      if(navigator.sendBeacon){
        navigator.sendBeacon('/api/rtc/signal', body);
      }
    }catch(e){}
  }

  async function closeCall(sendSignal){
    const cid = rtc.callId;

    if(sendSignal && cid){
      try{ await sendRtc('hangup', {}); }catch(e){}
    }

    if(rtc.pc){
      try{ rtc.pc.onicecandidate = null; rtc.pc.ontrack = null; }catch(e){}
      try{ rtc.pc.close(); }catch(e){}
    }

    rtc.pc = null;
    rtc.callId = null;
    rtc.pendingCandidates = [];

    if(remoteAudio) remoteAudio.srcObject = null;

    // kalau mic OFF, matikan stream biar hemat resource
    if(!state.myMicOn){
      stopLocalStream();
    }

    setAudioStatus('Tidak ada panggilan.');
  }

  async function ensurePeer(){
    if(rtc.pc) return rtc.pc;

    const pc = new RTCPeerConnection(getRtcConfig());
    rtc.pc = pc;

    pc.onicecandidate = (ev)=>{
      if(ev.candidate){
        const cand = (ev.candidate.toJSON ? ev.candidate.toJSON() : ev.candidate);
        sendRtc('candidate', { candidate: cand }).catch(()=>{});
      }
    };

    pc.ontrack = (ev)=>{
      const stream = (ev.streams && ev.streams[0]) ? ev.streams[0] : null;
      if(stream && remoteAudio){
        remoteAudio.srcObject = stream;
        applySpeakerState();

        if(state.audioUnlocked && state.mySpeakerOn){
          remoteAudio.play().catch(()=>{});
          setAudioStatus('Terhubung (audio aktif).');
        }else{
          setAudioStatus('Ada audio masuk. Klik "Aktifkan Speaker" dulu.');
        }
      }
    };

    pc.onconnectionstatechange = ()=>{
      if(!pc.connectionState) return;
      setAudioStatus('RTC: ' + pc.connectionState);

      // opsional: kalau gagal, reset call biar UI tidak stuck
      if(pc.connectionState === 'failed' || pc.connectionState === 'closed'){
        closeCall(false).catch(()=>{});
      }
    };

    // Jika mic sudah ON & stream sudah ada, attach track ke peer
    if(rtc.localStream && rtc.localStream.getTracks().length){
      rtc.localStream.getTracks().forEach(t=>{
        try{ pc.addTrack(t, rtc.localStream); }catch(e){}
      });
    }

    return pc;
  }

  async function handleRtcSignal(payload){
    if(!payload) return;

    const st = payload.signal_type;
    const callId = payload.call_id;
    const data = payload.data || {};

    if(!callId) return;

    // Penting: cegah signal call lama mengganggu call baru
    // - Offer: boleh mengganti call (akan menutup call lama)
    // - Selain offer: harus match callId aktif
    if(st !== 'offer'){
      if(!rtc.callId) return;
      if(rtc.callId !== callId) return;
    }

    try{
      if(st === 'offer'){
        // kalau ada call lain, tutup dulu
        if(rtc.callId && rtc.callId !== callId){
          await closeCall(false);
        }

        rtc.callId = callId;

        const pc = await ensurePeer();
        await pc.setRemoteDescription(new RTCSessionDescription({
          type: data.type || 'offer',
          sdp: data.sdp || ''
        }));

        if(state.myMicOn && !rtc.localStream){
          setAudioStatus('Panggilan masuk. Mic ON tapi izin belum ada: klik tombol Mic untuk mengizinkan.');
        }

        const answer = await pc.createAnswer();
        await pc.setLocalDescription(answer);
        await sendRtc('answer', { type: answer.type, sdp: answer.sdp });

        // apply queued candidates
        for(const c of rtc.pendingCandidates){
          await pc.addIceCandidate(new RTCIceCandidate(c));
        }
        rtc.pendingCandidates = [];

      } else if(st === 'candidate'){
        const cand = data.candidate;
        if(!cand) return;

        const pc = await ensurePeer();

        if(pc.remoteDescription && pc.remoteDescription.type){
          await pc.addIceCandidate(new RTCIceCandidate(cand));
        } else {
          rtc.pendingCandidates.push(cand);
          if(rtc.pendingCandidates.length > MAX_PENDING_CANDIDATES){
            rtc.pendingCandidates = rtc.pendingCandidates.slice(-MAX_PENDING_CANDIDATES);
          }
        }

      } else if(st === 'hangup'){
        await closeCall(false);
      }
    }catch(e){}
  }

  async function unlockAudio(){
    state.audioUnlocked = true;
    if(btnEnableAudio) btnEnableAudio.classList.add('ok');

    try{
      const AC = window.AudioContext || window.webkitAudioContext;
      if(AC){
        const ctx = new AC();
        await ctx.resume().catch(()=>{});
      }
    }catch(e){}

    if(remoteAudio && remoteAudio.srcObject && state.mySpeakerOn){
      remoteAudio.play().catch(()=>{});
      setAudioStatus('Speaker aktif.');
    }else{
      setAudioStatus('Speaker siap. (Jika ada audio masuk, bisa langsung terdengar.)');
    }
    applySpeakerDevice();
  }

  if(btnEnableAudio){
    btnEnableAudio.addEventListener('click', unlockAudio);
  }

  if(selMic){
    state.selectedMicId = getSaved(STORAGE.mic);
    selMic.addEventListener('mousedown', requestDeviceAccess);
    selMic.addEventListener('focus', requestDeviceAccess);
    selMic.addEventListener('change', async ()=>{
      state.selectedMicId = selMic.value || '';
      save(STORAGE.mic, state.selectedMicId);
      if(state.myMicOn){
        try{
          const oldStream = rtc.localStream;
          const newStream = await getUserMediaWithSelectedMic();
          const newTrack = newStream.getAudioTracks()[0] || null;
          if(newTrack){
            const sender = (rtc.pc && rtc.pc.getSenders) ? rtc.pc.getSenders().find(s=> s.track && s.track.kind === 'audio') : null;
            if(sender){
              await sender.replaceTrack(newTrack);
            }else if(rtc.pc){
              try{ rtc.pc.addTrack(newTrack, newStream); }catch(e){}
            }
          }
          rtc.localStream = newStream;
          rtc.localTrack = newTrack;
          if(rtc.localTrack) rtc.localTrack.enabled = state.myMicOn;
          if(oldStream) oldStream.getTracks().forEach(t=>{ try{ t.stop(); }catch(e){} });
          refreshDevices();
        }catch(err){
          setAudioStatus('Gagal ganti mic: ' + (err.message || err));
        }
      }
    });
  }

  if(selSpk){
    state.selectedSpkId = getSaved(STORAGE.spk);
    selSpk.addEventListener('mousedown', requestDeviceAccess);
    selSpk.addEventListener('focus', requestDeviceAccess);
    selSpk.addEventListener('change', ()=>{
      state.selectedSpkId = selSpk.value || '';
      save(STORAGE.spk, state.selectedSpkId);
      applySpeakerDevice();
    });
  }

  function renderPeers(){
    if(!peersList) return;
    const arr = Array.from(state.peers.values());
    peersList.innerHTML = arr.map(p=>{
      const isMe = p.id === myId;
      const tag = isMe ? ' (kamu)' : '';
      const mic = p.mic_on ? '🎙️' : '🔇';
      return `<li><b>${esc(p.student_name)}</b> ${esc(tag)} <span class="muted">(${esc(p.class_name)})</span> ${mic}</li>`;
    }).join('');
  }

  function appendChat(meta, body){
    if(!chatLog) return;
    const div = document.createElement('div');
    div.className = 'msg';
    div.innerHTML = `<div class="meta">${esc(meta)}</div><div>${esc(body)}</div>`;
    chatLog.appendChild(div);
    chatLog.scrollTop = chatLog.scrollHeight;
  }

  function renderMaterial(cm){
    if(!materialViewer) return;

    if(!cm || !cm.material){
      materialViewer.textContent = 'Belum ada materi.';
      materialViewer.classList.add('muted');
      return;
    }

    const m = cm.material;
    const f = cm.file;

    let html = `<div><b>${esc(m.title)}</b> <span class="muted">(${esc(m.type)})</span></div>`;

    if(m.type === 'text'){
      html += `<pre style="white-space:pre-wrap;margin:8px 0 0">${esc(m.text_content||'')}</pre>`;
    }else if(f && f.url_path){
      const mime = (f.mime||'').toLowerCase();
      const url = esc(f.url_path);

      if(mime.startsWith('audio/')){
        html += `<div style="margin-top:8px">
          <audio controls src="${url}"></audio>
          <div class="muted">Jika audio tidak bunyi: klik play (autoplay dibatasi browser).</div>
        </div>`;
      }else if(mime.startsWith('video/')){
        html += `<div style="margin-top:8px">
          <video controls src="${url}" style="max-width:100%"></video>
          <div class="muted">Klik play untuk mulai.</div>
        </div>`;
      }else{
        html += `<div style="margin-top:8px"><a href="${url}" target="_blank">Buka file: ${esc(f.filename||'file')}</a></div>`;
      }
    }

    materialViewer.innerHTML = html;
    materialViewer.classList.remove('muted');
  }

  async function refreshMaterial(){
    const res = await fetch('/api/material/current', {headers:{'Accept':'application/json'}});
    const data = await res.json().catch(()=>null);

    if(data && data.ok){
      renderMaterial(data.currentMaterial);

      if(data.state && broadcastBox){
        broadcastBox.textContent = data.state.broadcast_text || '';
      }
    }
  }

  function handleSnapshot(snap){
    if(snap && Array.isArray(snap.participants)){
      for(const p of snap.participants){
        state.peers.set(p.id, p);

        if(p.id === myId){
          state.myMicOn = !!p.mic_on;
          state.mySpeakerOn = (p.speaker_on === undefined) ? true : !!p.speaker_on;

          if(btnMic){
            btnMic.classList.toggle('ok', state.myMicOn);
            btnMic.textContent = state.myMicOn ? 'Mic: ON' : 'Mic: OFF';
          }

          applyMicState();
          applySpeakerState();
        }
      }
      renderPeers();
    }

    if(snap && snap.state && broadcastBox){
      broadcastBox.textContent = snap.state.broadcast_text || '';
    }

    if(snap && snap.currentMaterial){
      renderMaterial(snap.currentMaterial);
    }
  }

  async function handleEvents(events){
    for(const e of events){
      const t = e.type;
      const p = e.payload || {};

      if(t === 'participant_joined'){
        state.peers.set(p.participant_id, {
          id: p.participant_id,
          student_name: p.student_name,
          class_name: p.class_name,
          device_label: p.device_label,
          mic_on: p.mic_on ? 1 : 0,
          speaker_on: p.speaker_on ? 1 : 0,
        });
        renderPeers();
      }

      if(t === 'mic_changed'){
        const x = state.peers.get(p.participant_id);
        if(x){ x.mic_on = p.mic_on ? 1 : 0; renderPeers(); }

        if(p.participant_id === myId){
          state.myMicOn = !!p.mic_on;

          if(btnMic){
            btnMic.classList.toggle('ok', state.myMicOn);
            btnMic.textContent = state.myMicOn ? 'Mic: ON' : 'Mic: OFF';
          }

          applyMicState();

          if(!state.myMicOn){
            stopLocalStream();
          }
        }
      }

      if(t === 'speaker_changed'){
        if(p.participant_id === myId){
          state.mySpeakerOn = !!p.speaker_on;
          applySpeakerState();
          setAudioStatus(state.mySpeakerOn ? 'Speaker diaktifkan.' : 'Speaker dimatikan admin.');
        }
      }

      if(t === 'speaker_all_changed'){
        state.mySpeakerOn = !!p.speaker_on;
        applySpeakerState();
        setAudioStatus(state.mySpeakerOn ? 'Speaker diaktifkan.' : 'Speaker dimatikan admin.');
      }

      if(t === 'message_sent'){
        appendChat(`[Public] ${p.sender_type} • ${p.created_at}`, p.body);
      }
      if(t === 'message_private_admin'){
        appendChat(`[Private] ${p.sender_type} • ${p.created_at}`, p.body);
      }
      if(t === 'message_private_student'){
        appendChat(`[Private] admin • ${p.created_at}`, p.body);
      }

      if(t === 'broadcast_text_changed'){
        if(broadcastBox) broadcastBox.textContent = p.broadcast_text || '';
      }

      if(t === 'material_changed'){
        refreshMaterial();
      }

      if(t === 'session_ended'){
        appendChat('System', 'Sesi ditutup oleh admin.');
        await closeCall(false);
      }

      if(t === 'rtc_signal'){
        await handleRtcSignal(p);
      }
    }
  }

  async function sendChat(){
    const body = (chatInput.value||'').trim();
    if(!body) return;

    const r = await post('/api/chat/send', {body, target_type:'private_admin'});
    if(r && r.ok){
      chatInput.value='';
    }
  }

  if(btnMic){
    btnMic.onclick = async ()=>{
      const r = await post('/api/control/mic/toggle', {});
      if(r && r.ok){
        state.myMicOn = !!r.mic_on;

        btnMic.classList.toggle('ok', state.myMicOn);
        btnMic.textContent = state.myMicOn ? 'Mic: ON' : 'Mic: OFF';

      if(state.myMicOn){
        try{
          await ensureMicStreamFromUserGesture();
          applyMicState();
          setAudioStatus('Mic aktif.');
          refreshDevices();
        }catch(err){
          setAudioStatus('Mic ON tapi izin gagal: ' + (err.message||err));
        }
      }else{
          applyMicState();
          stopLocalStream();
          setAudioStatus('Mic nonaktif.');
        }
      }
    };

    // Hint jika bukan HTTPS
    if(!IS_SECURE_CONTEXT && !ALLOW_INSECURE_MEDIA){
      btnMic.title = 'Mic biasanya butuh HTTPS/localhost';
    }
  }

  if(btnSendChat){
    btnSendChat.onclick = sendChat;
  }

  if(chatInput){
    chatInput.addEventListener('keydown', (ev)=>{
      if(ev.key === 'Enter'){
        ev.preventDefault();
        sendChat();
      }
    });
  }

  if(btnRefreshMaterial) btnRefreshMaterial.onclick = refreshMaterial;

  // Saat tab ditutup, coba kirim hangup (opsional tapi membantu)
  window.addEventListener('pagehide', ()=>{
    sendBeaconHangup();
  });

  const poller = new window.EventPoller({
    intervalMs: 1200,
    onSnapshot: handleSnapshot,
    onEvents: handleEvents,
    onPresence: function(){},
  });
  poller.start();

  refreshMaterial();
  setAudioStatus('Tidak ada panggilan.');
  applySpeakerState();
  refreshDevices();
  if(navigator.mediaDevices && navigator.mediaDevices.addEventListener){
    navigator.mediaDevices.addEventListener('devicechange', refreshDevices);
  }

})();
