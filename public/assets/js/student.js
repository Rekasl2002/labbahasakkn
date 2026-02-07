(function(){
  if(window.__LAB_ROLE__ !== 'student') return;

  const myId = Number(window.__LAB_PARTICIPANT_ID__||0);
  const peersList = document.getElementById('peersList');
  const btnMic = document.getElementById('btnMic');
  const btnSpk = document.getElementById('btnSpk');
  const chatLog = document.getElementById('chatLog');
  const chatInput = document.getElementById('chatInput');
  const btnSendChat = document.getElementById('btnSendChat');
  const materialViewer = document.getElementById('materialViewer');
  const btnRefreshMaterial = document.getElementById('btnRefreshMaterial');
  const broadcastBox = document.getElementById('broadcastBox');
  const teacherTextBox = document.getElementById('teacherTextBox');

  // Voice UI
  const btnEnableAudio = document.getElementById('btnEnableAudio');
  const audioStatusEl = document.getElementById('audioStatus');
  const audioIndicatorEl = document.getElementById('studentAudioIndicator');
  const remoteAudio = document.getElementById('remoteAudio');
  const selMic = document.getElementById('selMic');
  const selSpk = document.getElementById('selSpk');
  const audioPool = (function(){
    const existing = document.getElementById('studentAudioPool');
    if(existing) return existing;
    const el = document.createElement('div');
    el.id = 'studentAudioPool';
    el.style.display = 'none';
    document.body.appendChild(el);
    return el;
  })();

  const state = {
    peers: new Map(),
    myMicOn: false,
    mySpeakerOn: true,
    audioUnlocked: false,
    allowToggleMic: true,
    allowToggleSpeaker: true,
    selectedMicId: '',
    selectedSpkId: '',
    devicePermissionAsked: false,
    autoInitDone: false,
    devices: { inputs: [], outputs: [] },
    broadcastText: '',
    materialText: '',
    activeTextSource: '',
    currentMaterialId: 0,
    lastFile: null,
    materialChangeFromEvent: false,
    renderedMaterialId: 0,
    renderedFileSig: '',
  };

  const rtc = {
    peers: new Map(),
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
    micLabel: 'lab_student_mic_label',
    spkLabel: 'lab_student_spk_label',
  };

  const ALWAYS_JOIN_VOICE = true;

  let voiceCheckTimer = null;

  function mkCallId(){
    if(window.crypto && crypto.randomUUID) return crypto.randomUUID();
    return String(Date.now()) + '-' + Math.random().toString(16).slice(2);
  }

  function wantVoice(){
    return ALWAYS_JOIN_VOICE || !!(state.mySpeakerOn || state.myMicOn);
  }

  function scheduleVoiceCheck(delayMs){
    const d = Number(delayMs || 250);
    if(voiceCheckTimer) clearTimeout(voiceCheckTimer);
    voiceCheckTimer = setTimeout(()=>{ ensureMeshConnections().catch(()=>{}); }, d);
  }

  function peerKeyAdmin(){ return 'admin'; }
  function peerKeyParticipant(pid){ return 'p' + String(pid); }
  function isOfferer(pid){ return !!(myId && pid && myId < pid); }

  function esc(s){
    return (s??'').toString().replace(/[&<>"]/g, c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;' }[c]));
  }

  function setAudioStatus(text){
    if(audioStatusEl) audioStatusEl.textContent = text || '';
  }

  function setAudioIndicator(kind, text){
    if(!audioIndicatorEl) return;
    audioIndicatorEl.classList.toggle('active', kind === 'active');
    audioIndicatorEl.classList.toggle('off', kind === 'off');
    audioIndicatorEl.classList.toggle('idle', kind === 'idle');
    const textEl = audioIndicatorEl.querySelector('.text');
    if(textEl) textEl.textContent = text || '';
  }

  function hasAnyAudioStream(){
    if(remoteAudio && remoteAudio.srcObject) return true;
    if(audioPool){
      const items = audioPool.querySelectorAll('audio');
      for(const a of items){
        if(a && a.srcObject) return true;
      }
    }
    return false;
  }

  function syncAudioIndicator(){
    if(!audioIndicatorEl) return;
    if(!hasAnyAudioStream()){
      setAudioIndicator('idle', 'Audio: standby');
      return;
    }
    if(!state.mySpeakerOn){
      setAudioIndicator('off', 'Audio: dimute');
      return;
    }
    if(state.audioUnlocked){
      setAudioIndicator('active', 'Audio: aktif');
    }else{
      setAudioIndicator('off', 'Audio: perlu izin');
    }
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

  function applySpeakerDevice(){
    if(!selSpk) return;
    const id = state.selectedSpkId || '';
    if(!id) return;

    const applyTo = (el)=>{
      if(!el || typeof el.setSinkId !== 'function') return;
      el.setSinkId(id).catch(()=>{});
    };

    applyTo(remoteAudio);
    if(audioPool){
      audioPool.querySelectorAll('audio').forEach(applyTo);
    }
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
    state.devices.inputs = inputs;
    state.devices.outputs = outputs;

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
      const savedMicLabel = getSaved(STORAGE.micLabel);
      if(savedMic && inputs.some(d=> d.deviceId === savedMic)){
        selMic.value = savedMic;
        state.selectedMicId = savedMic;
      }else{
        const byLabel = findByLabel(inputs, savedMicLabel);
        if(byLabel){
          selMic.value = byLabel.deviceId;
          state.selectedMicId = byLabel.deviceId;
        }else if(inputs[0]){
          selMic.value = inputs[0].deviceId;
          state.selectedMicId = inputs[0].deviceId;
        }
      }
      const picked = inputs.find(d=> d.deviceId === state.selectedMicId);
      if(picked && picked.label) saveLabel(STORAGE.micLabel, picked.label);
    }

    if(selSpk){
      const savedSpk = state.selectedSpkId || getSaved(STORAGE.spk);
      const savedSpkLabel = getSaved(STORAGE.spkLabel);
      if(savedSpk && outputs.some(d=> d.deviceId === savedSpk)){
        selSpk.value = savedSpk;
        state.selectedSpkId = savedSpk;
      }else{
        const byLabel = findByLabel(outputs, savedSpkLabel);
        if(byLabel){
          selSpk.value = byLabel.deviceId;
          state.selectedSpkId = byLabel.deviceId;
        }else if(outputs[0] && !selSpk.disabled){
          selSpk.value = outputs[0].deviceId;
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
    if(remoteAudio) remoteAudio.muted = !state.mySpeakerOn;
    if(audioPool){
      audioPool.querySelectorAll('audio').forEach(a=>{ a.muted = !state.mySpeakerOn; });
    }
    applySpeakerDevice();
    syncAudioIndicator();
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
    if(rtc.localTrack){
      attachLocalTrackToAllPeers();
      renegotiateAllPeers();
    }

    return rtc.localStream;
  }

  function applyMicState(){
    if(rtc.localTrack) rtc.localTrack.enabled = state.myMicOn;
  }

  function syncMicBtn(){
    if(!btnMic) return;
    btnMic.classList.toggle('ok', state.myMicOn);
    btnMic.textContent = state.myMicOn ? 'Mic: ON' : 'Mic: OFF';
    const locked = !state.allowToggleMic;
    btnMic.disabled = locked;
    btnMic.title = locked ? 'Mic dikunci admin' : 'Aktif/nonaktif mic kamu';
  }

  function syncSpkBtn(){
    if(!btnSpk) return;
    btnSpk.classList.toggle('ok', state.mySpeakerOn);
    btnSpk.textContent = state.mySpeakerOn ? 'Speaker: ON' : 'Speaker: OFF';
    const locked = !state.allowToggleSpeaker;
    btnSpk.disabled = locked;
    btnSpk.title = locked ? 'Speaker dikunci admin' : 'Aktif/nonaktif speaker kamu';
  }

  function ensurePeerAudio(kind, pid){
    if(kind === 'admin') return remoteAudio;
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

  async function sendRtcAdmin(signalType, data, callIdOverride){
    const callId = callIdOverride || (rtc.peers.get(peerKeyAdmin()) ? rtc.peers.get(peerKeyAdmin()).callId : '');
    if(!callId) return;
    await post('/api/rtc/signal', {
      to_type: 'admin',
      signal_type: signalType,
      call_id: callId,
      data: JSON.stringify(data || {}),
    });
  }

  async function sendRtcParticipant(pid, signalType, data, callIdOverride){
    if(!pid) return;
    const key = peerKeyParticipant(pid);
    const callId = callIdOverride || (rtc.peers.get(key) ? rtc.peers.get(key).callId : '');
    if(!callId) return;
    await post('/api/rtc/signal', {
      to_type: 'participant',
      to_participant_id: pid,
      signal_type: signalType,
      call_id: callId,
      data: JSON.stringify(data || {}),
    });
  }

  function attachLocalTrackToAllPeers(){
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

  async function renegotiatePeer(peer){
    if(!peer || !peer.pc || !peer.callId) return;
    if(peer.pc.signalingState && peer.pc.signalingState !== 'stable') return;
    try{
      const offer = await peer.pc.createOffer({ offerToReceiveAudio: true });
      await peer.pc.setLocalDescription(offer);
      if(peer.kind === 'admin'){
        await sendRtcAdmin('offer', { type: offer.type, sdp: offer.sdp }, peer.callId);
      }else{
        await sendRtcParticipant(peer.pid, 'offer', { type: offer.type, sdp: offer.sdp }, peer.callId);
      }
    }catch(e){}
  }

  function renegotiateAllPeers(){
    for(const peer of rtc.peers.values()){
      renegotiatePeer(peer);
    }
  }

  function createPeer(kind, pid, callId){
    const pc = new RTCPeerConnection(getRtcConfig());
    const key = (kind === 'admin') ? peerKeyAdmin() : peerKeyParticipant(pid);
    const peer = { key, kind, pid, callId, pc, pendingCandidates: [], audioEl: null };
    rtc.peers.set(key, peer);

    pc.onicecandidate = (ev)=>{
      if(ev.candidate){
        const cand = (ev.candidate.toJSON ? ev.candidate.toJSON() : ev.candidate);
        if(kind === 'admin'){
          sendRtcAdmin('candidate', { candidate: cand }, peer.callId).catch(()=>{});
        }else{
          sendRtcParticipant(pid, 'candidate', { candidate: cand }, peer.callId).catch(()=>{});
        }
      }
    };

    pc.ontrack = (ev)=>{
      const stream = (ev.streams && ev.streams[0]) ? ev.streams[0] : null;
      if(stream){
        const audioEl = ensurePeerAudio(kind, pid);
        peer.audioEl = audioEl;
        if(audioEl){
          audioEl.srcObject = stream;
          audioEl.muted = !state.mySpeakerOn;
          applySpeakerDevice();

          if(state.audioUnlocked && state.mySpeakerOn){
            audioEl.play().catch(()=>{});
            setAudioStatus('Terhubung (audio aktif).');
          }else{
            setAudioStatus('Ada audio masuk. Klik "Aktifkan Audio" dulu.');
          }
          syncAudioIndicator();
        }
      }
    };

    pc.onconnectionstatechange = ()=>{
      if(!pc.connectionState) return;
      if(pc.connectionState === 'failed' || pc.connectionState === 'closed'){
        closePeer(key, false);
        if(wantVoice()) scheduleVoiceCheck(800);
      } else if(pc.connectionState === 'disconnected'){
        if(wantVoice()) scheduleVoiceCheck(1200);
      }
    };

    if(rtc.localStream && rtc.localStream.getTracks().length){
      rtc.localStream.getTracks().forEach(t=>{
        try{ pc.addTrack(t, rtc.localStream); }catch(e){}
      });
      if(rtc.localTrack) rtc.localTrack.enabled = state.myMicOn;
    }

    return peer;
  }

  function getOrCreatePeer(kind, pid, callId){
    const key = (kind === 'admin') ? peerKeyAdmin() : peerKeyParticipant(pid);
    let peer = rtc.peers.get(key);
    if(peer && callId && peer.callId !== callId){
      closePeer(key, false);
      peer = null;
    }
    if(!peer){
      peer = createPeer(kind, pid, callId || '');
    }else if(callId){
      peer.callId = callId;
    }
    return peer;
  }

  function closePeer(key, sendSignal){
    const peer = rtc.peers.get(key);
    if(!peer) return;

    if(sendSignal && peer.callId){
      if(peer.kind === 'admin'){
        sendRtcAdmin('hangup', {}, peer.callId).catch(()=>{});
      }else{
        sendRtcParticipant(peer.pid, 'hangup', {}, peer.callId).catch(()=>{});
      }
    }

    try{ peer.pc.onicecandidate = null; peer.pc.ontrack = null; }catch(e){}
    try{ peer.pc.close(); }catch(e){}

    if(peer.audioEl && peer.audioEl !== remoteAudio){
      try{ peer.audioEl.srcObject = null; }catch(e){}
      try{ peer.audioEl.remove(); }catch(e){}
    }else if(peer.audioEl === remoteAudio && remoteAudio){
      try{ remoteAudio.srcObject = null; }catch(e){}
    }

    rtc.peers.delete(key);
    syncAudioIndicator();
  }

  function closeAllPeers(sendSignal){
    for(const key of Array.from(rtc.peers.keys())){
      closePeer(key, sendSignal);
    }
    if(!state.myMicOn){
      stopLocalStream();
    }
    syncAudioIndicator();
  }

  async function startOfferToAdmin(){
    const callId = mkCallId();
    const peer = getOrCreatePeer('admin', null, callId);
    const offer = await peer.pc.createOffer({ offerToReceiveAudio: true });
    await peer.pc.setLocalDescription(offer);
    await sendRtcAdmin('offer', { type: offer.type, sdp: offer.sdp }, callId);
  }

  async function startOfferToParticipant(pid){
    const callId = mkCallId();
    const peer = getOrCreatePeer('participant', pid, callId);
    const offer = await peer.pc.createOffer({ offerToReceiveAudio: true });
    await peer.pc.setLocalDescription(offer);
    await sendRtcParticipant(pid, 'offer', { type: offer.type, sdp: offer.sdp }, callId);
  }

  async function ensureMeshConnections(){
    if(!wantVoice()){
      closeAllPeers(true);
      return;
    }

    // Admin connection (selalu coba jika voice aktif)
    if(!rtc.peers.get(peerKeyAdmin())){
      try{ await startOfferToAdmin(); }catch(e){}
    }

    // Close peers not in current list
    for(const key of Array.from(rtc.peers.keys())){
      if(key === peerKeyAdmin()) continue;
      const pid = Number(String(key).replace(/^p/, ''));
      if(!state.peers.has(pid)){
        closePeer(key, true);
      }
    }

    // Participant mesh: hanya offerer (ID lebih kecil) yang start
    for(const p of state.peers.values()){
      if(!p || p.id === myId) continue;
      if(!isOfferer(p.id)) continue;

      const key = peerKeyParticipant(p.id);
      if(!rtc.peers.get(key)){
        try{ await startOfferToParticipant(p.id); }catch(e){}
      }
    }
  }

  // Opsional: kirim hangup cepat saat tab ditutup
  function sendBeaconHangup(){
    try{
      if(!navigator.sendBeacon) return;
      for(const peer of rtc.peers.values()){
        if(!peer.callId) continue;
        const body = new URLSearchParams();
        if(peer.kind === 'admin'){
          body.append('to_type', 'admin');
        }else{
          body.append('to_type', 'participant');
          body.append('to_participant_id', String(peer.pid));
        }
        body.append('signal_type', 'hangup');
        body.append('call_id', peer.callId);
        body.append('data', '{}');
        navigator.sendBeacon('/api/rtc/signal', body);
      }
    }catch(e){}
  }

  async function handleRtcSignal(payload){
    if(!payload) return;

    const st = payload.signal_type;
    const callId = payload.call_id;
    const data = payload.data || {};
    const fromType = payload.from_type || '';

    if(!callId) return;

    const isAdmin = fromType === 'admin';
    const fromPid = isAdmin ? null : Number(payload.from_participant_id || 0);
    const kind = isAdmin ? 'admin' : 'participant';
    const key = isAdmin ? peerKeyAdmin() : peerKeyParticipant(fromPid);

    if(!isAdmin && !fromPid) return;

    try{
      if(st === 'offer'){
        const peer = getOrCreatePeer(kind, fromPid, callId);
        await peer.pc.setRemoteDescription(new RTCSessionDescription({
          type: data.type || 'offer',
          sdp: data.sdp || ''
        }));

        if(state.myMicOn && !rtc.localStream){
          setAudioStatus('Panggilan masuk. Mic ON tapi izin belum ada: klik tombol Mic untuk mengizinkan.');
        }

        const answer = await peer.pc.createAnswer();
        await peer.pc.setLocalDescription(answer);

        if(isAdmin){
          await sendRtcAdmin('answer', { type: answer.type, sdp: answer.sdp }, callId);
        }else{
          await sendRtcParticipant(fromPid, 'answer', { type: answer.type, sdp: answer.sdp }, callId);
        }

        for(const c of peer.pendingCandidates){
          await peer.pc.addIceCandidate(new RTCIceCandidate(c));
        }
        peer.pendingCandidates = [];

      } else if(st === 'answer'){
        const peer = rtc.peers.get(key);
        if(!peer || peer.callId !== callId) return;
        await peer.pc.setRemoteDescription(new RTCSessionDescription({
          type: data.type || 'answer',
          sdp: data.sdp || ''
        }));

        for(const c of peer.pendingCandidates){
          await peer.pc.addIceCandidate(new RTCIceCandidate(c));
        }
        peer.pendingCandidates = [];

      } else if(st === 'candidate'){
        const peer = rtc.peers.get(key);
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
        closePeer(key, false);
      }
    }catch(e){}
  }

  async function unlockAudio(auto){
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

    if(remoteAudio && remoteAudio.srcObject && state.mySpeakerOn){
      try{
        await remoteAudio.play();
      }catch(e){
        ok = false;
      }
    }
    if(audioPool){
      audioPool.querySelectorAll('audio').forEach(a=> a.play().catch(()=>{ ok = false; }));
    }
    applySpeakerDevice();

    state.audioUnlocked = ok;
    if(btnEnableAudio) btnEnableAudio.classList.toggle('ok', ok);
    if(ok){
      if(remoteAudio && remoteAudio.srcObject && state.mySpeakerOn){
        setAudioStatus('Speaker aktif.');
      }else{
        setAudioStatus('Speaker siap. (Jika ada audio masuk, bisa langsung terdengar.)');
      }
    }else{
      setAudioStatus('Klik "Aktifkan Audio" jika audio belum keluar.');
    }
    syncAudioIndicator();
  }

  if(btnEnableAudio){
    btnEnableAudio.addEventListener('click', ()=> unlockAudio(false));
  }

  if(selMic){
    state.selectedMicId = getSaved(STORAGE.mic);
    selMic.addEventListener('mousedown', requestDeviceAccess);
    selMic.addEventListener('focus', requestDeviceAccess);
    selMic.addEventListener('change', async ()=>{
      state.selectedMicId = selMic.value || '';
      save(STORAGE.mic, state.selectedMicId);
      const picked = state.devices.inputs.find(d=> d.deviceId === state.selectedMicId);
      if(picked && picked.label) saveLabel(STORAGE.micLabel, picked.label);
      if(state.myMicOn){
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
          if(rtc.localTrack) rtc.localTrack.enabled = state.myMicOn;
          if(oldStream) oldStream.getTracks().forEach(t=>{ try{ t.stop(); }catch(e){} });
          renegotiateAllPeers();
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
      const picked = state.devices.outputs.find(d=> d.deviceId === state.selectedSpkId);
      if(picked && picked.label) saveLabel(STORAGE.spkLabel, picked.label);
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

  function renderTeacherText(){
    if(!teacherTextBox) return;
    const b = (state.broadcastText || '').trim();
    const t = (state.materialText || '').trim();

    let text = '';
    if(state.activeTextSource === 'broadcast'){
      text = b;
    }else if(state.activeTextSource === 'material'){
      text = t;
    }else{
      text = b || t;
    }

    if(text){
      teacherTextBox.textContent = text;
      teacherTextBox.classList.remove('muted');
    }else{
      teacherTextBox.textContent = 'Belum ada teks.';
      teacherTextBox.classList.add('muted');
    }
  }

  function pickActiveText(source){
    const b = (state.broadcastText || '').trim();
    const t = (state.materialText || '').trim();
    if(source === 'broadcast' && b){
      state.activeTextSource = 'broadcast';
      return;
    }
    if(source === 'material' && t){
      state.activeTextSource = 'material';
      return;
    }
    if(b){
      state.activeTextSource = 'broadcast';
      return;
    }
    if(t){
      state.activeTextSource = 'material';
      return;
    }
    state.activeTextSource = '';
  }

  function renderMaterial(cm){
    if(!materialViewer) return;

    if(!cm || !cm.material){
      materialViewer.textContent = 'Belum ada materi.';
      materialViewer.classList.add('muted');
      state.materialText = '';
      state.currentMaterialId = 0;
      state.lastFile = null;
      state.renderedMaterialId = 0;
      state.renderedFileSig = '';
      pickActiveText();
      renderTeacherText();
      return;
    }

    const m = cm.material || {};
    const materialId = Number(m.id || 0);
    if(materialId && state.currentMaterialId !== materialId){
      state.currentMaterialId = materialId;
      state.materialText = '';
      state.lastFile = null;
      pickActiveText();
    }
    const files = Array.isArray(cm.files) ? cm.files : (cm.file ? [cm.file] : []);
    const selected = cm.selected || null;

    const isOfficeDoc = (name)=> /\.(docx?|xlsx?|pptx?)$/i.test(name||'');
    const isPdf = (name)=> /\.pdf$/i.test(name||'');
    const getCoverUrl = (file)=>{
      if(!file) return '';
      return file.cover_url_path || file.poster_url_path || file.thumbnail_url_path || '';
    };

    const renderFileTitle = (filename)=>{
      if(!filename) return '';
      return `<div class="fileTitle">${esc(filename)}</div>`;
    };

    const simplePdfUrl = (url)=>{
      if(!url) return url;
      const base = url.split('#')[0];
      return base + '#toolbar=0&navpanes=0&scrollbar=0';
    };

    const renderFilePreview = (file)=>{
      if(!file || !file.url_path) return '';
      const mime = (file.mime||'').toLowerCase();
      const rawUrl = (file.url_path || '').toString();
      const rawPreviewUrl = (file.preview_url_path || '').toString();
      const url = esc(rawUrl);
      const previewUrl = rawPreviewUrl ? esc(rawPreviewUrl) : '';
      const filename = file.filename || 'file';
      const fileIdAttr = file.id ? ` data-file-id="${file.id}"` : '';
      const coverUrl = getCoverUrl(file);

      if(mime.startsWith('audio/')){
        return `<div class="mediaBlock">
          ${renderFileTitle(filename)}
          ${coverUrl ? `<img class="mediaCover" src="${esc(coverUrl)}" alt="Cover ${esc(filename)}">` : ''}
          <audio class="mediaLocked" data-student-media="1"${fileIdAttr} src="${url}" style="width:100%" playsinline tabindex="-1"></audio>
          <div class="muted">Audio dikendalikan oleh admin.</div>
        </div>`;
      }
      if(mime.startsWith('video/')){
        const poster = coverUrl ? ` poster="${esc(coverUrl)}"` : '';
        return `<div class="mediaBlock">
          ${renderFileTitle(filename)}
          ${coverUrl ? `<img class="mediaCover" src="${esc(coverUrl)}" alt="Cover ${esc(filename)}">` : ''}
          <video class="mediaLocked" data-student-media="1"${fileIdAttr}${poster} src="${url}" style="max-width:100%" playsinline tabindex="-1"></video>
          <div class="muted">Video dikendalikan oleh admin.</div>
        </div>`;
      }
      if(mime.startsWith('image/')){
        return `<div class="mediaBlock">
          ${renderFileTitle(filename)}
          <img src="${url}" alt="${esc(filename)}" style="max-width:100%;height:auto">
        </div>`;
      }
      if(mime === 'application/pdf' || isPdf(filename) || isPdf(url) || previewUrl){
        const pdfUrl = esc(simplePdfUrl(rawPreviewUrl || rawUrl));
        return `<div class="docSimple">
          <div class="fileTitle">${esc(filename)}</div>
          <iframe class="docFrame simple" src="${pdfUrl}"></iframe>
        </div>`;
      }
      if(isOfficeDoc(filename) || isOfficeDoc(url)){
        return `<div class="docSimple">
          <div class="fileTitle">${esc(filename)}</div>
          <div class="muted tiny" style="margin-top:8px">
            Preview lokal belum tersedia untuk file ini. Buka file langsung di tab baru.
          </div>
          <div style="margin-top:8px">
            <a class="btn tiny" href="${url}" target="_blank">Buka</a>
          </div>
        </div>`;
      }
      return `<div class="mediaBlock">
        ${renderFileTitle(filename)}
        <div><a href="${url}" target="_blank">Buka file</a></div>
      </div>`;
    };

    let teacherText = '';
    if(selected && selected.type === 'text'){
      teacherText = selected.text || '';
    }else if(m.type === 'text' && m.text_content){
      teacherText = m.text_content || '';
    }
    if(teacherText){
      state.materialText = teacherText;
      const hasBroadcast = (state.broadcastText || '').trim() !== '';
      const allowOverride = state.materialChangeFromEvent || !hasBroadcast || state.activeTextSource !== 'broadcast';
      if(allowOverride){
        pickActiveText('material');
      }
    }
    renderTeacherText();

    let selectedFile = null;
    if(selected && selected.type === 'file' && selected.file){
      selectedFile = selected.file;
      state.lastFile = selected.file;
    }else if(state.lastFile){
      const stillExists = files.find(f=> Number(f.id) === Number(state.lastFile.id));
      if(stillExists) selectedFile = stillExists;
    }else if(files.length){
      selectedFile = files[0];
      state.lastFile = files[0];
    }

    const fileSig = selectedFile
      ? [
          selectedFile.id || '',
          selectedFile.url_path || '',
          selectedFile.preview_url_path || '',
          selectedFile.cover_url_path || '',
          selectedFile.mime || '',
        ].join('|')
      : '';
    const shouldUpdateViewer = (
      state.renderedMaterialId !== materialId ||
      state.renderedFileSig !== fileSig
    );

    if(shouldUpdateViewer){
      let html = `<div><b>${esc(m.title||'')}</b> <span class="muted">(${esc(m.type||'')})</span></div>`;
      html += `<div style="margin-top:8px">`;
      if(selectedFile){
        html += renderFilePreview(selectedFile);
      }else if(files.length){
        html += renderFilePreview(files[0]);
      }else{
        html += `<div class="muted">Belum ada file materi.</div>`;
      }
      html += `</div>`;

      materialViewer.innerHTML = html;
      clearMediaUnlock();
      materialViewer.classList.remove('muted');
      state.renderedMaterialId = materialId;
      state.renderedFileSig = fileSig;
    }
    state.materialChangeFromEvent = false;
  }

  function getStudentMedia(){
    if(!materialViewer) return null;
    return materialViewer.querySelector('[data-student-media]');
  }

  function clearMediaUnlock(){
    if(!materialViewer) return;
    const box = materialViewer.querySelector('.mediaUnlock');
    if(box) box.remove();
  }

  function showMediaUnlock(media){
    if(!materialViewer || !media) return;
    if(materialViewer.querySelector('.mediaUnlock')) return;
    const box = document.createElement('div');
    box.className = 'mediaUnlock';
    box.innerHTML = `<div class="muted">Browser membutuhkan izin untuk memutar audio/video.</div>
      <button class="btn" type="button">Aktifkan Audio/Video</button>`;
    const btn = box.querySelector('button');
    if(btn){
      btn.onclick = async ()=>{
        try{
          await media.play();
          clearMediaUnlock();
        }catch(e){}
      };
    }
    materialViewer.appendChild(box);
  }

  function applyMediaControl(p){
    const media = getStudentMedia();
    if(!media || !p) return;

    const mediaFileId = media.dataset.fileId || '';
    if(p.file_id && mediaFileId && String(p.file_id) !== String(mediaFileId)) return;

    const toNum = (v)=> {
      const n = Number(v);
      return Number.isFinite(n) ? n : null;
    };
    const clamp = (n, min, max)=> Math.min(max, Math.max(min, n));

    const vol = toNum(p.volume);
    if(vol !== null) media.volume = clamp(vol, 0, 1);

    if(p.muted !== undefined && p.muted !== null){
      media.muted = !!p.muted;
    }

    const rate = toNum(p.playback_rate);
    if(rate !== null && rate > 0){
      media.playbackRate = rate;
    }

    const t = toNum(p.current_time);
    if(t !== null && Math.abs((media.currentTime || 0) - t) > 0.3){
      try{ media.currentTime = Math.max(0, t); }catch(e){}
    }

    if(p.action === 'play'){
      media.play().catch(()=> showMediaUnlock(media));
    }else if(p.action === 'pause'){
      try{ media.pause(); }catch(e){}
    }else if(p.action === 'seek'){
      // already handled by current_time update
    }else if(p.action === 'sync'){
      if(p.paused === 0){
        media.play().catch(()=> showMediaUnlock(media));
      }else if(p.paused === 1){
        try{ media.pause(); }catch(e){}
      }
    }
  }

  async function refreshMaterial(){
    const res = await fetch('/api/material/current', {headers:{'Accept':'application/json'}});
    const data = await res.json().catch(()=>null);

    if(data && data.ok){
      renderMaterial(data.currentMaterial);

      if(data.state){
        const prevBroadcast = state.broadcastText;
        state.broadcastText = data.state.broadcast_text || '';
        if(broadcastBox) broadcastBox.textContent = state.broadcastText;
        if(prevBroadcast !== state.broadcastText){
          pickActiveText('broadcast');
        }else if(!state.activeTextSource){
          pickActiveText();
        }
        renderTeacherText();
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

          applyMicState();
          applySpeakerState();
          syncMicBtn();
          syncSpkBtn();
        }
      }
      renderPeers();
      scheduleVoiceCheck(300);
      autoInitAudio(true).catch(()=>{});
    }

    if(snap && snap.state){
      const prevBroadcast = state.broadcastText;
      state.broadcastText = snap.state.broadcast_text || '';
      if(broadcastBox) broadcastBox.textContent = state.broadcastText;
      if(prevBroadcast !== state.broadcastText){
        pickActiveText('broadcast');
      }else if(!state.activeTextSource){
        pickActiveText();
      }
      renderTeacherText();
    }

    if(snap && snap.state){
      if(snap.state.allow_student_mic !== undefined){
        state.allowToggleMic = !!snap.state.allow_student_mic;
      }
      if(snap.state.allow_student_speaker !== undefined){
        state.allowToggleSpeaker = !!snap.state.allow_student_speaker;
      }
      syncMicBtn();
      syncSpkBtn();
    }

    if(snap && snap.currentMaterial){
      renderMaterial(snap.currentMaterial);
    }
  }

  async function autoInitAudio(forceMic){
    if(!state.autoInitDone){
      state.autoInitDone = true;

      if(!IS_SECURE_CONTEXT && !ALLOW_INSECURE_MEDIA){
        return;
      }

      try{
        await requestDeviceAccess();
        await refreshDevices();
      }catch(e){}

      try{
        await unlockAudio(true);
      }catch(e){}
    }

    if(forceMic && state.myMicOn){
      try{
        await ensureMicStreamFromUserGesture();
        applyMicState();
        attachLocalTrackToAllPeers();
      }catch(err){
        setAudioStatus('Mic ON tapi izin gagal: ' + (err.message||err));
      }
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
        scheduleVoiceCheck(300);
      }

      if(t === 'mic_changed'){
        const x = state.peers.get(p.participant_id);
        if(x){ x.mic_on = p.mic_on ? 1 : 0; renderPeers(); }

        if(p.participant_id === myId){
          state.myMicOn = !!p.mic_on;
          syncMicBtn();
          applyMicState();

          if(!state.myMicOn){
            stopLocalStream();
          }
          scheduleVoiceCheck(200);
        }
      }

      if(t === 'speaker_changed'){
        const x = state.peers.get(p.participant_id);
        if(x){ x.speaker_on = p.speaker_on ? 1 : 0; }
        if(p.participant_id === myId){
          state.mySpeakerOn = !!p.speaker_on;
          applySpeakerState();
          setAudioStatus(state.mySpeakerOn ? 'Speaker diaktifkan.' : 'Speaker dimatikan admin.');
          syncSpkBtn();
          scheduleVoiceCheck(200);
        }
      }

      if(t === 'speaker_all_changed'){
        for(const x of state.peers.values()){ x.speaker_on = p.speaker_on ? 1 : 0; }
        state.mySpeakerOn = !!p.speaker_on;
        applySpeakerState();
        setAudioStatus(state.mySpeakerOn ? 'Speaker diaktifkan.' : 'Speaker dimatikan admin.');
        syncSpkBtn();
        scheduleVoiceCheck(200);
      }

      if(t === 'voice_lock_changed'){
        if(p.allow_student_mic !== undefined){
          state.allowToggleMic = !!p.allow_student_mic;
        }
        if(p.allow_student_speaker !== undefined){
          state.allowToggleSpeaker = !!p.allow_student_speaker;
        }
        syncMicBtn();
        syncSpkBtn();
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
        state.broadcastText = p.broadcast_text || '';
        if(broadcastBox) broadcastBox.textContent = state.broadcastText;
        pickActiveText('broadcast');
        renderTeacherText();
      }

      if(t === 'material_changed'){
        state.materialChangeFromEvent = true;
        refreshMaterial();
      }

      if(t === 'material_media_control'){
        applyMediaControl(p);
      }

      if(t === 'session_ended'){
        appendChat('System', 'Sesi ditutup oleh admin.');
        closeAllPeers(false);
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
      if(!state.allowToggleMic){
        setAudioStatus('Mic dikunci admin.');
        return;
      }
      const r = await post('/api/control/mic/toggle', {});
      if(r && r.ok){
        state.myMicOn = !!r.mic_on;
        syncMicBtn();

        if(state.myMicOn){
          try{
            await ensureMicStreamFromUserGesture();
            applyMicState();
            attachLocalTrackToAllPeers();
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
        scheduleVoiceCheck(200);
      }else if(r && r.error){
        setAudioStatus(r.error);
        syncMicBtn();
      }
    };

    // Hint jika bukan HTTPS
    if(!IS_SECURE_CONTEXT && !ALLOW_INSECURE_MEDIA){
      btnMic.title = 'Mic biasanya butuh HTTPS/localhost';
    }
  }

  if(btnSpk){
    btnSpk.onclick = async ()=>{
      if(!state.allowToggleSpeaker){
        setAudioStatus('Speaker dikunci admin.');
        return;
      }
      const r = await post('/api/control/speaker/toggle', {});
      if(r && r.ok){
        state.mySpeakerOn = !!r.speaker_on;
        applySpeakerState();
        syncSpkBtn();
        setAudioStatus(state.mySpeakerOn ? 'Speaker diaktifkan.' : 'Speaker dimatikan.');
        scheduleVoiceCheck(200);
      }else if(r && r.error){
        setAudioStatus(r.error);
        syncSpkBtn();
      }
    };
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
  syncMicBtn();
  syncSpkBtn();
  refreshDevices();
  setTimeout(()=>{ autoInitAudio(false).catch(()=>{}); }, 300);
  scheduleVoiceCheck(400);
  if(navigator.mediaDevices && navigator.mediaDevices.addEventListener){
    navigator.mediaDevices.addEventListener('devicechange', refreshDevices);
  }

})();
