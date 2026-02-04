(function(){
  if(window.__LAB_ROLE__ !== 'admin') return;

  const BASE = (window.__LAB_BASE__ || '').toString().replace(/\/+$/,'');
  const API = (path)=> `${BASE}${path}`;

  const state = {
    participants: new Map(),
    presence: new Map(),
    selectedPrivateTarget: null,
    chatMode: 'public',
    currentMaterial: null,
    broadcastText: '',
    selectedMicId: '',
    selectedSpkId: '',
    devicePermissionAsked: false,
    autoInitDone: false,
    devices: { inputs: [], outputs: [] },
    audioUnlocked: false,
    allowStudentMic: true,
    allowStudentSpeaker: true,
    adminSpeakerOn: true,
  };

  const grid = document.getElementById('participantsGrid');
  const chatLog = document.getElementById('chatLog');
  const chatInput = document.getElementById('chatInput');
  const btnSendChat = document.getElementById('btnSendChat');
  const chatModeSel = document.getElementById('chatMode');
  const privateTargetSel = document.getElementById('privateTarget');
  const broadcastInput = document.getElementById('broadcastText');
  const btnBroadcast = document.getElementById('btnBroadcastText');
  const chkAllowStudentMic = document.getElementById('chkAllowStudentMic');
  const chkAllowStudentSpk = document.getElementById('chkAllowStudentSpk');
  const matBox = document.getElementById('currentMaterialBox');
  const btnRefreshMaterial = document.getElementById('btnRefreshMaterial');

  // Voice (WebRTC)
  const callStatusEl = document.getElementById('callStatus');
  const btnEnableAdminAudio = document.getElementById('btnEnableAdminAudio');
  const btnAdminSpk = document.getElementById('btnAdminSpk');
  const btnAdminMic = document.getElementById('btnAdminMic');
  const btnHangupCall = document.getElementById('btnHangupCall');
  const adminRemoteAudio = document.getElementById('adminRemoteAudio');
  const selAdminMic = document.getElementById('selAdminMic');
  const selAdminSpk = document.getElementById('selAdminSpk');
  const adminAudioIndicator = document.getElementById('adminAudioIndicator');

  const audioPool = (function(){
    const existing = document.getElementById('adminAudioPool');
    if(existing) return existing;
    const el = document.createElement('div');
    el.id = 'adminAudioPool';
    el.style.display = 'none';
    document.body.appendChild(el);
    return el;
  })();

  const rtc = {
    peers: new Map(),
    localStream: null,
    localTrack: null,
    primaryAudioPid: null,
    micOn: true,
  };

  const MAX_PENDING_CANDIDATES = 160; // opsional: batasi memori
  const IS_SECURE_CONTEXT =
    (location.protocol === 'https:' ||
     location.hostname === 'localhost' ||
     location.hostname === '127.0.0.1');
  const ALLOW_INSECURE_MEDIA = !!window.__LAB_ALLOW_INSECURE_MEDIA__;
  const STORAGE = {
    mic: 'lab_admin_mic_id',
    spk: 'lab_admin_spk_id',
    micLabel: 'lab_admin_mic_label',
    spkLabel: 'lab_admin_spk_label',
  };

  let _renderScheduled = false;

  function esc(s){ return (s??'').toString().replace(/[&<>"]/g, c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;' }[c])); }
  function nowTime(){ return new Date().toLocaleTimeString(); }

  function setCallStatus(text){
    if(callStatusEl) callStatusEl.textContent = text || '';
  }

  function setAudioIndicator(kind, text){
    if(!adminAudioIndicator) return;
    adminAudioIndicator.classList.toggle('active', kind === 'active');
    adminAudioIndicator.classList.toggle('off', kind === 'off');
    adminAudioIndicator.classList.toggle('idle', kind === 'idle');
    const textEl = adminAudioIndicator.querySelector('.text');
    if(textEl) textEl.textContent = text || '';
  }

  function hasAnyAudioStream(){
    if(adminRemoteAudio && adminRemoteAudio.srcObject) return true;
    if(audioPool){
      const items = audioPool.querySelectorAll('audio');
      for(const a of items){
        if(a && a.srcObject) return true;
      }
    }
    return false;
  }

  function syncAudioIndicator(){
    if(!adminAudioIndicator) return;
    if(!hasAnyAudioStream()){
      setAudioIndicator('idle', 'Audio: standby');
      return;
    }
    if(!state.adminSpeakerOn){
      setAudioIndicator('off', 'Audio: dimute');
      return;
    }
    if(state.audioUnlocked){
      setAudioIndicator('active', 'Audio: aktif');
    }else{
      setAudioIndicator('off', 'Audio: perlu izin');
    }
  }

  function syncLockUI(){
    if(chkAllowStudentMic) chkAllowStudentMic.checked = !!state.allowStudentMic;
    if(chkAllowStudentSpk) chkAllowStudentSpk.checked = !!state.allowStudentSpeaker;
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

  function saveLabel(key, value){
    const v = (value || '').toString().trim();
    if(v) save(key, v);
  }

  function findByLabel(list, label){
    const target = (label || '').toString().trim().toLowerCase();
    if(!target) return null;
    return list.find(d=> (d && d.label ? d.label.toLowerCase() : '') === target) || null;
  }

  function labelForDevice(d, idx){
    const name = (d && d.label) ? d.label : '';
    if(name) return name;
    if(d.kind === 'audioinput') return `Microphone ${idx+1}`;
    if(d.kind === 'audiooutput') return `Speaker ${idx+1}`;
    return `Device ${idx+1}`;
  }

  function pidOf(v){
    const n = Number(v);
    return Number.isFinite(n) ? n : 0;
  }

  function applySpeakerDevice(){
    if(!selAdminSpk) return;
    const id = state.selectedSpkId || '';
    if(!id) return;

    const applyTo = (el)=>{
      if(!el || typeof el.setSinkId !== 'function') return;
      el.setSinkId(id).catch(()=>{});
    };

    applyTo(adminRemoteAudio);
    if(audioPool){
      audioPool.querySelectorAll('audio').forEach(applyTo);
    }
  }

  async function unlockAdminAudio(auto){
    let ok = true;

    if(!state.devicePermissionAsked){
      await requestDeviceAccess();
    }

    try{
      const AC = window.AudioContext || window.webkitAudioContext;
      if(AC){
        const ctx = new AC();
        await ctx.resume().catch(()=>{});
        if(ctx.state !== 'running') ok = false;
      }
    }catch(e){
      ok = false;
    }

    if(state.adminSpeakerOn && adminRemoteAudio && adminRemoteAudio.srcObject){
      try{
        await adminRemoteAudio.play();
      }catch(e){
        ok = false;
      }
    }
    if(audioPool){
      audioPool.querySelectorAll('audio').forEach(a=> a.play().catch(()=>{ ok = false; }));
    }

    state.audioUnlocked = ok;
    if(btnEnableAdminAudio) btnEnableAdminAudio.classList.toggle('ok', ok);
    if(!ok && auto){
      setCallStatus('Klik "Aktifkan Speaker" jika audio belum keluar.');
    }

    syncAudioIndicator();
    updateVoiceStatus();
  }

  if(btnEnableAdminAudio){
    btnEnableAdminAudio.addEventListener('click', ()=> unlockAdminAudio(false));
  }

  async function refreshDevices(){
    if(!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices){
      if(selAdminMic){
        selAdminMic.innerHTML = '<option value="">Browser tidak mendukung pemilihan mic</option>';
        selAdminMic.disabled = true;
      }
      if(selAdminSpk){
        selAdminSpk.innerHTML = '<option value="">Browser tidak mendukung pemilihan speaker</option>';
        selAdminSpk.disabled = true;
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
    state.devices.inputs = inputs;
    state.devices.outputs = outputs;

    const needPermissionHint = !state.devicePermissionAsked;
    if(selAdminMic){
      const emptyMicText = needPermissionHint ? 'Klik dropdown untuk meminta izin mic' : 'Tidak ada microphone';
      selAdminMic.innerHTML = inputs.length
        ? inputs.map((d,i)=> `<option value="${esc(d.deviceId)}">${esc(labelForDevice(d,i))}</option>`).join('')
        : `<option value="">${emptyMicText}</option>`;
      selAdminMic.disabled = inputs.length === 0;
    }

    const canPickOutput = !!(adminRemoteAudio && typeof adminRemoteAudio.setSinkId === 'function');
    if(selAdminSpk){
      const emptySpkText = needPermissionHint ? 'Klik dropdown untuk meminta izin mic' : 'Tidak ada speaker';
      if(!canPickOutput){
        selAdminSpk.innerHTML = '<option value="">Browser tidak mendukung pemilihan speaker</option>';
        selAdminSpk.disabled = true;
      }else{
        selAdminSpk.innerHTML = outputs.length
          ? outputs.map((d,i)=> `<option value="${esc(d.deviceId)}">${esc(labelForDevice(d,i))}</option>`).join('')
          : `<option value="">${emptySpkText}</option>`;
        selAdminSpk.disabled = outputs.length === 0;
      }
    }

    if(selAdminMic){
      const savedMic = state.selectedMicId || getSaved(STORAGE.mic);
      const savedMicLabel = getSaved(STORAGE.micLabel);
      if(savedMic && inputs.some(d=> d.deviceId === savedMic)){
        selAdminMic.value = savedMic;
        state.selectedMicId = savedMic;
      }else{
        const byLabel = findByLabel(inputs, savedMicLabel);
        if(byLabel){
          selAdminMic.value = byLabel.deviceId;
          state.selectedMicId = byLabel.deviceId;
        }else if(inputs[0]){
          selAdminMic.value = inputs[0].deviceId;
          state.selectedMicId = inputs[0].deviceId;
        }
      }
      const picked = inputs.find(d=> d.deviceId === state.selectedMicId);
      if(picked && picked.label) saveLabel(STORAGE.micLabel, picked.label);
    }

    if(selAdminSpk){
      const savedSpk = state.selectedSpkId || getSaved(STORAGE.spk);
      const savedSpkLabel = getSaved(STORAGE.spkLabel);
      if(savedSpk && outputs.some(d=> d.deviceId === savedSpk)){
        selAdminSpk.value = savedSpk;
        state.selectedSpkId = savedSpk;
      }else{
        const byLabel = findByLabel(outputs, savedSpkLabel);
        if(byLabel){
          selAdminSpk.value = byLabel.deviceId;
          state.selectedSpkId = byLabel.deviceId;
        }else if(outputs[0] && !selAdminSpk.disabled){
          selAdminSpk.value = outputs[0].deviceId;
          state.selectedSpkId = outputs[0].deviceId;
        }
      }
      const picked = outputs.find(d=> d.deviceId === state.selectedSpkId);
      if(picked && picked.label) saveLabel(STORAGE.spkLabel, picked.label);
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

  async function requestDeviceAccess(){
    if(!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) return;
    state.devicePermissionAsked = true;
    try{
      const tmp = await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
      if(tmp) tmp.getTracks().forEach(t=>{ try{ t.stop(); }catch(e){} });
    }catch(err){
      appendChat('System', `Izin mic diperlukan untuk memuat perangkat: ${err.message||err}`);
    }
    refreshDevices();
  }

  async function ensureLocalStream(){
    if(rtc.localStream) return rtc.localStream;

    if(!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia){
      throw new Error('Browser tidak mendukung getUserMedia');
    }
    if(!IS_SECURE_CONTEXT && !ALLOW_INSECURE_MEDIA){
      throw new Error('Mic admin butuh HTTPS (atau localhost).');
    }

    rtc.localStream = await getUserMediaWithSelectedMic();

    rtc.localTrack = rtc.localStream.getAudioTracks()[0] || null;
    if(rtc.localTrack) rtc.localTrack.enabled = rtc.micOn;

    return rtc.localStream;
  }

  function stopLocalStream(){
    if(rtc.localStream){
      rtc.localStream.getTracks().forEach(t=>{ try{ t.stop(); }catch(e){} });
    }
    rtc.localStream = null;
    rtc.localTrack = null;
  }

  function ensurePeerAudio(pid){
    if(adminRemoteAudio && (rtc.primaryAudioPid === null || rtc.primaryAudioPid === pid)){
      if(rtc.primaryAudioPid === null) rtc.primaryAudioPid = pid;
      return adminRemoteAudio;
    }
    if(!audioPool) return null;
    let el = audioPool.querySelector(`audio[data-pid="${pid}"]`);
    if(!el){
      el = document.createElement('audio');
      el.dataset.pid = String(pid);
      el.autoplay = true;
      el.playsInline = true;
      el.className = 'audioEl';
      audioPool.appendChild(el);
    }
    return el;
  }

  function updateVoiceStatus(){
    if(!callStatusEl) return;
    const total = rtc.peers.size;
    let connected = 0;
    for(const peer of rtc.peers.values()){
      if(peer.pc && peer.pc.connectionState === 'connected') connected++;
    }
    setCallStatus(total ? `Voice room: ${connected}/${total} tersambung` : 'Voice room: idle');
    if(btnHangupCall) btnHangupCall.disabled = (total === 0);
  }

  function createPeer(pid, callId){
    const pc = new RTCPeerConnection(getRtcConfig());
    const peer = { pid, callId, pc, pendingCandidates: [], audioEl: null };
    rtc.peers.set(pid, peer);

    pc.onicecandidate = (ev)=>{
      if(ev.candidate){
        const cand = (ev.candidate.toJSON ? ev.candidate.toJSON() : ev.candidate);
        sendRtc(pid, 'candidate', { candidate: cand }, callId).catch(()=>{});
      }
    };

    pc.ontrack = (ev)=>{
      const stream = (ev.streams && ev.streams[0]) ? ev.streams[0] : null;
      if(stream){
        const audioEl = ensurePeerAudio(pid);
        peer.audioEl = audioEl;
        if(audioEl){
          audioEl.srcObject = stream;
          audioEl.muted = !state.adminSpeakerOn;
          applySpeakerDevice();

          if(state.audioUnlocked && state.adminSpeakerOn){
            audioEl.play().catch(()=>{});
          }else{
            setCallStatus('Ada audio masuk. Klik "Aktifkan Speaker".');
          }
          syncAudioIndicator();
        }
      }
    };

    pc.onconnectionstatechange = ()=>{
      if(!pc.connectionState) return;
      updateVoiceStatus();
      if(pc.connectionState === 'failed' || pc.connectionState === 'closed'){
        closePeer(pid, false);
      }
    };

    if(rtc.localStream && rtc.localStream.getTracks().length){
      rtc.localStream.getTracks().forEach(t=>{
        try{ pc.addTrack(t, rtc.localStream); }catch(e){}
      });
      if(rtc.localTrack) rtc.localTrack.enabled = rtc.micOn;
    }

    updateVoiceStatus();
    return peer;
  }

  function getOrCreatePeer(pid, callId){
    let peer = rtc.peers.get(pid);
    if(peer && callId && peer.callId !== callId){
      closePeer(pid, false);
      peer = null;
    }
    if(!peer){
      peer = createPeer(pid, callId || '');
    }else if(callId){
      peer.callId = callId;
    }
    return peer;
  }

  function attachLocalTrackToAll(){
    if(!rtc.localStream || !rtc.localTrack) return;
    for(const peer of rtc.peers.values()){
      const sender = peer.pc.getSenders ? peer.pc.getSenders().find(s=> s.track && s.track.kind === 'audio') : null;
      if(sender){
        sender.replaceTrack(rtc.localTrack).catch(()=>{});
      }else{
        try{ peer.pc.addTrack(rtc.localTrack, rtc.localStream); }catch(e){}
      }
    }
  }

  function closePeer(pid, sendSignal){
    const peer = rtc.peers.get(pid);
    if(!peer) return;

    if(sendSignal && peer.callId){
      post('/api/rtc/signal', {
        to_type: 'participant',
        to_participant_id: pid,
        signal_type: 'hangup',
        call_id: peer.callId,
        data: JSON.stringify({}),
      }).catch(()=>{});
    }

    if(peer.pc){
      try{ peer.pc.onicecandidate = null; peer.pc.ontrack = null; }catch(e){}
      try{ peer.pc.close(); }catch(e){}
    }

    if(peer.audioEl){
      try{ peer.audioEl.srcObject = null; }catch(e){}
      if(peer.audioEl !== adminRemoteAudio){
        try{ peer.audioEl.remove(); }catch(e){}
      }else{
        rtc.primaryAudioPid = null;
      }
    }

    rtc.peers.delete(pid);
    updateVoiceStatus();
    syncAudioIndicator();
  }

  function closeAllPeers(sendSignal){
    for(const pid of Array.from(rtc.peers.keys())){
      closePeer(pid, sendSignal);
    }
  }

  function sendBeaconHangupAll(){
    try{
      if(!navigator.sendBeacon) return;
      for(const [pid, peer] of rtc.peers.entries()){
        if(!peer.callId) continue;
        const body = new URLSearchParams();
        body.append('to_type', 'participant');
        body.append('to_participant_id', String(pid));
        body.append('signal_type', 'hangup');
        body.append('call_id', String(peer.callId));
        body.append('data', '{}');
        navigator.sendBeacon(API('/api/rtc/signal'), body);
      }
    }catch(e){}
  }

  async function post(url, data){
    const form = new URLSearchParams();
    Object.keys(data||{}).forEach(k=> form.append(k, data[k]));
    const res = await fetch(API(url), {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body:form.toString()
    });
    return res.json().catch(()=>({ok:false}));
  }

  function scheduleRenderParticipants(){
    if(_renderScheduled) return;
    _renderScheduled = true;
    requestAnimationFrame(()=>{
      _renderScheduled = false;
      renderParticipants();
    });
  }

  async function hangupAll(sendSignal){
    closeAllPeers(sendSignal);
    if(adminRemoteAudio) adminRemoteAudio.srcObject = null;
    rtc.primaryAudioPid = null;
    if(!rtc.micOn) stopLocalStream();
    updateVoiceStatus();
    syncAudioIndicator();
    scheduleRenderParticipants();
  }

  function syncAdminMicBtn(){
    if(!btnAdminMic) return;
    btnAdminMic.classList.toggle('ok', rtc.micOn);
    btnAdminMic.textContent = rtc.micOn ? 'Mic Admin: ON' : 'Mic Admin: OFF';
  }

  function syncAdminSpkBtn(){
    if(!btnAdminSpk) return;
    btnAdminSpk.classList.toggle('ok', state.adminSpeakerOn);
    btnAdminSpk.textContent = state.adminSpeakerOn ? 'Speaker Admin: ON' : 'Speaker Admin: OFF';
  }

  function applyAdminSpeakerState(){
    if(adminRemoteAudio) adminRemoteAudio.muted = !state.adminSpeakerOn;
    if(audioPool){
      audioPool.querySelectorAll('audio').forEach(a=>{
        a.muted = !state.adminSpeakerOn;
        if(state.adminSpeakerOn && state.audioUnlocked){
          a.play().catch(()=>{});
        }
      });
    }
    if(state.adminSpeakerOn && state.audioUnlocked && adminRemoteAudio && adminRemoteAudio.srcObject){
      adminRemoteAudio.play().catch(()=>{});
    }
    syncAudioIndicator();
  }

  function applyAdminMic(){
    if(rtc.localTrack) rtc.localTrack.enabled = rtc.micOn;
  }

  if(btnAdminSpk){
    syncAdminSpkBtn();
    btnAdminSpk.addEventListener('click', ()=>{
      state.adminSpeakerOn = !state.adminSpeakerOn;
      syncAdminSpkBtn();
      applyAdminSpeakerState();
    });
  }

  if(btnAdminMic){
    syncAdminMicBtn();
    btnAdminMic.addEventListener('click', async ()=>{
      rtc.micOn = !rtc.micOn;
      syncAdminMicBtn();

      if(rtc.micOn){
        try{
          await ensureLocalStream();
          attachLocalTrackToAll();
          renegotiateAllPeers();
          applyAdminMic();
          refreshDevices();
        }catch(err){
          rtc.micOn = false;
          syncAdminMicBtn();
          appendChat('System', `Mic admin tidak bisa diakses: ${err.message||err}`);
        }
      }else{
        applyAdminMic();
      }
    });

    if(!IS_SECURE_CONTEXT && !ALLOW_INSECURE_MEDIA){
      btnAdminMic.title = 'Mic admin biasanya butuh HTTPS/localhost';
    }
  }

  if(btnHangupCall){
    btnHangupCall.addEventListener('click', ()=> hangupAll(true));
  }

  if(selAdminMic){
    state.selectedMicId = getSaved(STORAGE.mic);
    selAdminMic.addEventListener('mousedown', requestDeviceAccess);
    selAdminMic.addEventListener('focus', requestDeviceAccess);
    selAdminMic.addEventListener('change', async ()=>{
      state.selectedMicId = selAdminMic.value || '';
      save(STORAGE.mic, state.selectedMicId);
      const picked = state.devices.inputs.find(d=> d.deviceId === state.selectedMicId);
      if(picked && picked.label) saveLabel(STORAGE.micLabel, picked.label);
      if(rtc.localStream || rtc.peers.size){
        try{
          const oldStream = rtc.localStream;
          const newStream = await getUserMediaWithSelectedMic();
          const newTrack = newStream.getAudioTracks()[0] || null;
          if(newTrack){
            for(const peer of rtc.peers.values()){
              const sender = (peer.pc && peer.pc.getSenders) ? peer.pc.getSenders().find(s=> s.track && s.track.kind === 'audio') : null;
              if(sender){
                await sender.replaceTrack(newTrack);
              }else if(peer.pc){
                try{ peer.pc.addTrack(newTrack, newStream); }catch(e){}
              }
            }
          }
          rtc.localStream = newStream;
          rtc.localTrack = newTrack;
          if(rtc.localTrack) rtc.localTrack.enabled = rtc.micOn;
          if(oldStream) oldStream.getTracks().forEach(t=>{ try{ t.stop(); }catch(e){} });
          renegotiateAllPeers();
          refreshDevices();
        }catch(err){
          appendChat('System', `Gagal ganti mic admin: ${err.message||err}`);
        }
      }
    });
  }

  if(selAdminSpk){
    state.selectedSpkId = getSaved(STORAGE.spk);
    selAdminSpk.addEventListener('mousedown', requestDeviceAccess);
    selAdminSpk.addEventListener('focus', requestDeviceAccess);
    selAdminSpk.addEventListener('change', ()=>{
      state.selectedSpkId = selAdminSpk.value || '';
      save(STORAGE.spk, state.selectedSpkId);
      const picked = state.devices.outputs.find(d=> d.deviceId === state.selectedSpkId);
      if(picked && picked.label) saveLabel(STORAGE.spkLabel, picked.label);
      applySpeakerDevice();
    });
  }

  async function sendRtc(toPid, signalType, data, callIdOverride){
    if(!toPid) return;
    const peer = rtc.peers.get(toPid);
    const callId = callIdOverride || (peer ? peer.callId : '');
    if(!callId) return;
    await post('/api/rtc/signal', {
      to_type: 'participant',
      to_participant_id: toPid,
      signal_type: signalType,
      call_id: callId,
      data: JSON.stringify(data || {}),
    });
  }

  async function renegotiatePeer(peer){
    if(!peer || !peer.pc || !peer.callId) return;
    if(peer.pc.signalingState && peer.pc.signalingState !== 'stable') return;
    try{
      const offer = await peer.pc.createOffer({ offerToReceiveAudio: true });
      await peer.pc.setLocalDescription(offer);
      await sendRtc(peer.pid, 'offer', { type: offer.type, sdp: offer.sdp }, peer.callId);
    }catch(e){}
  }

  function renegotiateAllPeers(){
    for(const peer of rtc.peers.values()){
      renegotiatePeer(peer);
    }
  }

  async function handleRtcSignal(payload){
    if(!payload || payload.to_type !== 'admin') return;

    const fromPid = Number(payload.from_participant_id || 0);
    if(!fromPid) return;

    const st = payload.signal_type;
    const data = payload.data || {};
    const callId = payload.call_id;

    if(!callId) return;

    try{
      if(st === 'offer'){
        const peer = getOrCreatePeer(fromPid, callId);
        const pc = peer.pc;

        await pc.setRemoteDescription(new RTCSessionDescription({
          type: data.type || 'offer',
          sdp: data.sdp || ''
        }));

        if(rtc.localStream && rtc.localStream.getTracks().length){
          const hasSender = pc.getSenders && pc.getSenders().some(s=> s.track && s.track.kind === 'audio');
          if(!hasSender){
            rtc.localStream.getTracks().forEach(t=>{ try{ pc.addTrack(t, rtc.localStream); }catch(e){} });
          }
          if(rtc.localTrack) rtc.localTrack.enabled = rtc.micOn;
        }

        const answer = await pc.createAnswer();
        await pc.setLocalDescription(answer);
        await sendRtc(fromPid, 'answer', { type: answer.type, sdp: answer.sdp }, callId);

        for(const c of peer.pendingCandidates){
          await pc.addIceCandidate(new RTCIceCandidate(c));
        }
        peer.pendingCandidates = [];

      } else if(st === 'answer'){
        const peer = rtc.peers.get(fromPid);
        if(!peer || peer.callId !== callId) return;
        const pc = peer.pc;

        await pc.setRemoteDescription(new RTCSessionDescription({
          type: data.type || 'answer',
          sdp: data.sdp || ''
        }));

        for(const c of peer.pendingCandidates){
          await pc.addIceCandidate(new RTCIceCandidate(c));
        }
        peer.pendingCandidates = [];

      } else if(st === 'candidate'){
        const peer = rtc.peers.get(fromPid);
        if(!peer || peer.callId !== callId) return;
        const cand = data.candidate;
        if(!cand) return;

        if(peer.pc.remoteDescription && peer.pc.remoteDescription.type){
          await peer.pc.addIceCandidate(new RTCIceCandidate(cand));
        } else {
          peer.pendingCandidates.push(cand);
          if(peer.pendingCandidates.length > MAX_PENDING_CANDIDATES){
            peer.pendingCandidates = peer.pendingCandidates.slice(-MAX_PENDING_CANDIDATES);
          }
        }

      } else if(st === 'hangup'){
        closePeer(fromPid, false);
      }
    }catch(e){}
  }

  function renderParticipants(){
    if(!grid) return;

    const arr = Array.from(state.participants.values());

    grid.innerHTML = arr.map(p=>{
      const online = state.presence.get(p.id) ? 'online' : 'offline';
      const device = p.device_label ? esc(p.device_label) : ('PC-' + p.id);

      return `
        <div class="pcard" data-pid="${p.id}">
          <div class="top">
            <div>
              <div><b>${device}</b></div>
              <div class="badge">${esc(p.student_name)} (${esc(p.class_name)})</div>
              <div class="badge ${online}">${online.toUpperCase()} • ${esc(p.ip_address||'-')}</div>
            </div>
            <div class="row gap">
              <button class="btnMic ${p.mic_on? 'ok':''}" title="Mic" type="button">${p.mic_on? '🎙️':'🔇'}</button>
              <button class="btnSpk ${p.speaker_on? 'ok':''}" title="Speaker" type="button">${p.speaker_on? '🔊':'🔈'}</button>
            </div>
          </div>
          <div class="row gap wrap" style="margin-top:8px">
            <button class="btnPrivate" type="button">Private chat</button>
          </div>
        </div>`;
    }).join('');

    // Keep selection + rebuild private dropdown
    if(privateTargetSel){
      const old = String(state.selectedPrivateTarget || '');
      const opts = arr.map(p=>{
        return `<option value="${p.id}">${esc(p.student_name)} (${esc(p.class_name)})</option>`;
      }).join('');

      privateTargetSel.innerHTML = `<option value="">-- pilih siswa --</option>` + opts;
      privateTargetSel.value = old;
    }
  }

  function appendChat(meta, body){
    if(!chatLog) return;
    const div = document.createElement('div');
    div.className = 'msg';
    div.innerHTML = `<div class="meta">${esc(meta)}</div><div>${esc(body)}</div>`;
    chatLog.appendChild(div);
    chatLog.scrollTop = chatLog.scrollHeight;
  }

  function handleEvents(events){
    for(const e of events){
      const t = e.type;
      const p = e.payload || {};

      if(t === 'participant_joined'){
        const pid = pidOf(p.participant_id || p.id);
        if(!pid) continue;
        state.participants.delete(String(pid));
        state.participants.set(pid, {
          id: pid,
          student_name: p.student_name,
          class_name: p.class_name,
          device_label: p.device_label,
          ip_address: p.ip_address,
          mic_on: p.mic_on ? 1 : 0,
          speaker_on: p.speaker_on ? 1 : 0,
        });
        scheduleRenderParticipants();
      }

      if(t === 'mic_changed'){
        const x = state.participants.get(pidOf(p.participant_id));
        if(x){ x.mic_on = p.mic_on ? 1 : 0; scheduleRenderParticipants(); }
      }

      if(t === 'speaker_changed'){
        const x = state.participants.get(pidOf(p.participant_id));
        if(x){ x.speaker_on = p.speaker_on ? 1 : 0; scheduleRenderParticipants(); }
      }

      if(t === 'mic_all_changed'){
        for(const x of state.participants.values()) x.mic_on = p.mic_on ? 1 : 0;
        scheduleRenderParticipants();
      }

      if(t === 'speaker_all_changed'){
        for(const x of state.participants.values()) x.speaker_on = p.speaker_on ? 1 : 0;
        scheduleRenderParticipants();
      }

      if(t === 'message_sent'){
        appendChat(`[Public] ${p.sender_type} • ${p.created_at||nowTime()}`, p.body);
      }

      if(t === 'message_private_admin'){
        appendChat(`[Private->Admin] ${p.sender_type} • ${p.created_at||nowTime()}`, p.body);
      }

      if(t === 'message_private_student'){
        appendChat(`[Private] admin->student:${p.target_participant_id} • ${p.created_at||nowTime()}`, p.body);
      }

      if(t === 'broadcast_text_changed'){
        state.broadcastText = p.broadcast_text || '';
        if(broadcastInput) broadcastInput.value = state.broadcastText;
      }

      if(t === 'voice_lock_changed'){
        if(p.allow_student_mic !== undefined){
          state.allowStudentMic = !!p.allow_student_mic;
        }
        if(p.allow_student_speaker !== undefined){
          state.allowStudentSpeaker = !!p.allow_student_speaker;
        }
        syncLockUI();
      }

      if(t === 'material_changed'){
        refreshMaterial();
      }

      if(t === 'session_ended'){
        appendChat('System', 'Sesi ditutup.');
        hangupAll(false).catch(()=>{});
      }

      if(t === 'rtc_signal'){
        handleRtcSignal(p);
      }
    }
  }

  function handleSnapshot(snap){
    if(snap && Array.isArray(snap.participants)){
      state.participants.clear();
      for(const p of snap.participants){
        const pid = pidOf(p.id);
        if(!pid) continue;
        p.id = pid;
        state.participants.set(pid, p);
      }
      scheduleRenderParticipants();
    }

    if(snap && snap.state){
      state.broadcastText = snap.state.broadcast_text || '';
      if(broadcastInput) broadcastInput.value = state.broadcastText;
      if(snap.state.allow_student_mic !== undefined){
        state.allowStudentMic = !!snap.state.allow_student_mic;
      }
      if(snap.state.allow_student_speaker !== undefined){
        state.allowStudentSpeaker = !!snap.state.allow_student_speaker;
      }
      syncLockUI();
    }

    if(snap && snap.currentMaterial){
      renderMaterialBox(snap.currentMaterial);
    }
  }

  function handlePresence(list){
    state.presence.clear();
    for(const it of list){
      const pid = pidOf(it.id);
      if(!pid) continue;
      state.presence.set(pid, !!it.online);
    }
    scheduleRenderParticipants();
  }

  async function autoInitAudio(){
    if(state.autoInitDone) return;
    state.autoInitDone = true;

    if(!IS_SECURE_CONTEXT && !ALLOW_INSECURE_MEDIA){
      return;
    }

    try{
      await requestDeviceAccess();
      await refreshDevices();
    }catch(e){}

    if(rtc.micOn){
      try{
        await ensureLocalStream();
      }catch(err){
        appendChat('System', `Mic admin belum bisa diakses otomatis: ${err.message||err}`);
      }
    }

    try{
      await unlockAdminAudio(true);
    }catch(e){}
  }

  function renderMaterialBox(cm){
    if(!matBox) return;
    if(!cm || !cm.material){
      matBox.textContent = 'Belum ada materi.';
      matBox.classList.add('muted');
      return;
    }
    const m = cm.material;
    const f = cm.file;

    let html = `<div><b>${esc(m.title)}</b> <span class="muted">(${esc(m.type)})</span></div>`;

    if(m.type === 'text'){
      html += `<pre style="white-space:pre-wrap;margin:8px 0 0">${esc(m.text_content||'')}</pre>`;
    }else if(f && f.url_path){
      html += `<div style="margin-top:8px"><a href="${esc(f.url_path)}" target="_blank">Buka file: ${esc(f.filename||'file')}</a></div>`;
    }

    matBox.innerHTML = html;
    matBox.classList.remove('muted');
  }

  async function refreshMaterial(){
    const res = await fetch(API('/api/material/current'), {headers:{'Accept':'application/json'}});
    const data = await res.json().catch(()=>null);
    if(data && data.ok){
      renderMaterialBox(data.currentMaterial);
    }
  }

  // UI actions
  if(grid){
    grid.addEventListener('click', async (ev)=>{
      const card = ev.target.closest('.pcard');
      if(!card) return;

      const pid = Number(card.dataset.pid||0);

      if(ev.target.classList.contains('btnMic')){
        const cur = state.participants.get(pid);
        const next = cur && cur.mic_on ? 0 : 1;
        await post('/api/control/admin/mic', {participant_id: pid, mic_on: next});
      }

      if(ev.target.classList.contains('btnSpk')){
        const cur = state.participants.get(pid);
        const next = cur && cur.speaker_on ? 0 : 1;
        await post('/api/control/admin/speaker', {participant_id: pid, speaker_on: next});
      }

      if(ev.target.classList.contains('btnPrivate')){
        state.selectedPrivateTarget = pid;
        if(privateTargetSel) privateTargetSel.value = String(pid);

        if(chatModeSel){
          chatModeSel.value = 'private_student';
          chatModeSel.dispatchEvent(new Event('change'));
        }

        appendChat('System', `Private target set to participant:${pid}`);
      }
    });
  }

  const btnMuteAllMic = document.getElementById('btnMuteAllMic');
  const btnUnmuteAllMic = document.getElementById('btnUnmuteAllMic');
  const btnMuteAllSpk = document.getElementById('btnMuteAllSpk');
  const btnUnmuteAllSpk = document.getElementById('btnUnmuteAllSpk');

  if(btnMuteAllMic) btnMuteAllMic.onclick = ()=> post('/api/control/admin/all', {mic_on:0});
  if(btnUnmuteAllMic) btnUnmuteAllMic.onclick = ()=> post('/api/control/admin/all', {mic_on:1});
  if(btnMuteAllSpk) btnMuteAllSpk.onclick = ()=> post('/api/control/admin/all', {speaker_on:0});
  if(btnUnmuteAllSpk) btnUnmuteAllSpk.onclick = ()=> post('/api/control/admin/all', {speaker_on:1});

  if(chatModeSel){
    chatModeSel.addEventListener('change', ()=>{
      state.chatMode = chatModeSel.value;

      if(state.chatMode === 'private_student'){
        if(privateTargetSel) privateTargetSel.disabled = false;
      }else{
        if(privateTargetSel) privateTargetSel.disabled = true;
        // jangan paksa clear selection, biar kalau balik private masih tersimpan
      }
    });
  }

  if(privateTargetSel){
    privateTargetSel.addEventListener('change', ()=>{
      const v = Number(privateTargetSel.value||0);
      state.selectedPrivateTarget = v || null;
    });
  }

  async function sendChat(){
    const body = (chatInput.value||'').trim();
    if(!body) return;

    const target_type = state.chatMode;
    const payload = {body, target_type};

    if(target_type === 'private_student'){
      if(!state.selectedPrivateTarget){
        appendChat('System','Pilih siswa dulu.');
        return;
      }
      payload.target_participant_id = state.selectedPrivateTarget;
    }

    const r = await post('/api/chat/send', payload);
    if(r && r.ok){
      chatInput.value = '';
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

  if(btnBroadcast){
    btnBroadcast.onclick = ()=>{
      post('/api/control/admin/broadcast-text', {broadcast_text: (broadcastInput && broadcastInput.value) ? broadcastInput.value : ''});
    };
  }

  async function saveVoiceLock(){
    if(!chkAllowStudentMic && !chkAllowStudentSpk) return;
    const payload = {
      allow_student_mic: (chkAllowStudentMic && chkAllowStudentMic.checked) ? 1 : 0,
      allow_student_speaker: (chkAllowStudentSpk && chkAllowStudentSpk.checked) ? 1 : 0,
    };
    const r = await post('/api/control/admin/voice-lock', payload);
    if(r && r.ok){
      if(r.allow_student_mic !== undefined) state.allowStudentMic = !!r.allow_student_mic;
      if(r.allow_student_speaker !== undefined) state.allowStudentSpeaker = !!r.allow_student_speaker;
      syncLockUI();
    }else{
      const msg = (r && r.error) ? r.error : 'Gagal menyimpan kontrol mic/speaker siswa.';
      appendChat('System', msg);
      syncLockUI();
    }
  }

  if(chkAllowStudentMic) chkAllowStudentMic.addEventListener('change', saveVoiceLock);
  if(chkAllowStudentSpk) chkAllowStudentSpk.addEventListener('change', saveVoiceLock);

  if(btnRefreshMaterial){
    btnRefreshMaterial.onclick = refreshMaterial;
  }

  // When leaving page, try to hangup cleanly (opsional tapi membantu)
  window.addEventListener('pagehide', ()=>{
    sendBeaconHangupAll();
  });

  // Start polling
  if(!window.EventPoller){
    appendChat('System', 'EventPoller tidak ditemukan. Pastikan poll.js dimuat.');
    return;
  }

  const poller = new window.EventPoller({
    intervalMs: 1200,
    onSnapshot: handleSnapshot,
    onEvents: handleEvents,
    onPresence: handlePresence,
  });
  poller.start();

  refreshMaterial();
  updateVoiceStatus();
  syncLockUI();
  syncAdminSpkBtn();
  applyAdminSpeakerState();
  refreshDevices();
  setTimeout(()=>{ autoInitAudio().catch(()=>{}); }, 300);
  if(navigator.mediaDevices && navigator.mediaDevices.addEventListener){
    navigator.mediaDevices.addEventListener('devicechange', refreshDevices);
  }

})();
