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

  // Voice UI
  const btnEnableAudio = document.getElementById('btnEnableAudio');
  const audioStatusEl = document.getElementById('audioStatus');
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
  };

  let voiceCheckTimer = null;

  function mkCallId(){
    if(window.crypto && crypto.randomUUID) return crypto.randomUUID();
    return String(Date.now()) + '-' + Math.random().toString(16).slice(2);
  }

  function wantVoice(){
    return !!(state.mySpeakerOn || state.myMicOn);
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
    if(remoteAudio) remoteAudio.muted = !state.mySpeakerOn;
    if(audioPool){
      audioPool.querySelectorAll('audio').forEach(a=>{ a.muted = !state.mySpeakerOn; });
    }
    applySpeakerDevice();
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
            setAudioStatus('Ada audio masuk. Klik "Aktifkan Speaker" dulu.');
          }
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
  }

  function closeAllPeers(sendSignal){
    for(const key of Array.from(rtc.peers.keys())){
      closePeer(key, sendSignal);
    }
    if(!state.myMicOn){
      stopLocalStream();
    }
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
      if(!(p.mic_on || p.speaker_on)) continue;
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

  async function unlockAudio(){
    state.audioUnlocked = true;
    if(btnEnableAudio) btnEnableAudio.classList.add('ok');

    if(!state.devicePermissionAsked){
      await requestDeviceAccess();
    }

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
    if(audioPool){
      audioPool.querySelectorAll('audio').forEach(a=> a.play().catch(()=>{}));
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

          applyMicState();
          applySpeakerState();
          syncMicBtn();
          syncSpkBtn();
        }
      }
      renderPeers();
      scheduleVoiceCheck(300);
    }

    if(snap && snap.state && broadcastBox){
      broadcastBox.textContent = snap.state.broadcast_text || '';
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
        if(broadcastBox) broadcastBox.textContent = p.broadcast_text || '';
      }

      if(t === 'material_changed'){
        refreshMaterial();
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
  scheduleVoiceCheck(400);
  if(navigator.mediaDevices && navigator.mediaDevices.addEventListener){
    navigator.mediaDevices.addEventListener('devicechange', refreshDevices);
  }

})();
