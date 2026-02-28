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
    broadcastEnabled: false,
    selectedMicId: '',
    selectedSpkId: '',
    devicePermissionAsked: false,
    autoInitDone: false,
    devices: { inputs: [], outputs: [] },
    audioUnlocked: false,
    allowStudentMic: true,
    allowStudentSpeaker: true,
    adminSpeakerOn: true,
    micVolume: 1,
    speakerVolume: 1,
  };
  let sessionReloadQueued = false;
  let materialRenderSig = '';
  let materialRefreshBusy = false;
  let materialRefreshQueued = false;
  let materialRefreshToken = 0;
  let materialRefreshAppliedToken = 0;

  const grid = document.getElementById('participantsGrid');
  const chatLog = document.getElementById('chatLog');
  const chatInput = document.getElementById('chatInput');
  const btnSendChat = document.getElementById('btnSendChat');
  const chatModeSel = document.getElementById('chatMode');
    const privateTargetSel = document.getElementById('privateTarget');
    const broadcastInput = document.getElementById('broadcastText');
    const btnBroadcast = document.getElementById('btnBroadcastText');
    const btnClearBroadcast = document.getElementById('btnClearBroadcastText');
    const btnAllowStudentMic = document.getElementById('btnAllowStudentMic');
    const btnAllowStudentSpk = document.getElementById('btnAllowStudentSpk');
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
  const rngAdminMicVol = document.getElementById('rngAdminMicVol');
  const txtAdminMicVol = document.getElementById('txtAdminMicVol');
  const rngAdminSpkVol = document.getElementById('rngAdminSpkVol');
  const txtAdminSpkVol = document.getElementById('txtAdminSpkVol');

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
  const DISCONNECTED_RECOVER_MS = 1400;
  const DISCONNECTED_FORCE_CLOSE_MS = 5200;
  const ADMIN_RECOVERY_DELAY_MS = 320;
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
    micVolume: 'lab_admin_mic_volume',
    spkVolume: 'lab_admin_spk_volume',
  };

  let _renderScheduled = false;
  let adminRecoveryTimer = null;

  function esc(s){ return (s??'').toString().replace(/[&<>"]/g, c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;' }[c])); }
  function clamp01(v){
    const n = Number(v);
    if(!Number.isFinite(n)) return 1;
    return Math.min(1, Math.max(0, n));
  }
  function toPercentText(v){
    return `${Math.round(clamp01(v) * 100)}%`;
  }

  function setCallStatus(text){
    if(callStatusEl) callStatusEl.textContent = text || '';
  }

  function clearPeerRecoveryTimers(peer){
    if(!peer) return;
    if(peer.reconnectTimer){
      clearTimeout(peer.reconnectTimer);
      peer.reconnectTimer = null;
    }
    if(peer.forceCloseTimer){
      clearTimeout(peer.forceCloseTimer);
      peer.forceCloseTimer = null;
    }
  }

  function scheduleAdminVoiceRecovery(delayMs){
    const d = Number(delayMs || ADMIN_RECOVERY_DELAY_MS);
    if(adminRecoveryTimer) clearTimeout(adminRecoveryTimer);
    adminRecoveryTimer = setTimeout(()=>{ recoverAdminAudioAndVoice().catch(()=>{}); }, d);
  }

  async function recoverAdminAudioAndVoice(){
    if(!IS_SECURE_CONTEXT && !ALLOW_INSECURE_MEDIA){
      return;
    }

    try{
      await refreshDevices();
    }catch(e){}

    if(rtc.micOn){
      try{
        await ensureLocalStream();
        attachLocalTrackToAll();
        renegotiateAllPeers(true);
        applyAdminMic();
      }catch(e){}
    }else{
      renegotiateAllPeers(true);
    }

    if(state.adminSpeakerOn){
      unlockAdminAudio(true).catch(()=>{});
    }
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
      setAudioIndicator('idle', 'Audio: menunggu/standby');
      return;
    }
    if(!state.adminSpeakerOn){
      setAudioIndicator('off', 'Audio: dimatikan');
      return;
    }
    if(state.audioUnlocked){
      setAudioIndicator('active', 'Audio: aktif');
    }else{
      setAudioIndicator('off', 'Audio: perlu izin');
    }
  }

  function syncLockUI(){
    if(btnAllowStudentMic){
      const micAllowed = !!state.allowStudentMic;
      btnAllowStudentMic.classList.toggle('ok', micAllowed);
      btnAllowStudentMic.classList.toggle('danger', !micAllowed);
      btnAllowStudentMic.textContent = micAllowed ? '🎙️ Mikrofon Siswa: Boleh' : '🎙️ Mikrofon Siswa: Dikunci';
      btnAllowStudentMic.title = micAllowed
        ? 'Klik untuk mengunci pengaturan mikrofon siswa'
        : 'Klik untuk membuka pengaturan mikrofon siswa';
      btnAllowStudentMic.setAttribute('aria-pressed', micAllowed ? 'true' : 'false');
    }

    if(btnAllowStudentSpk){
      const speakerAllowed = !!state.allowStudentSpeaker;
      btnAllowStudentSpk.classList.toggle('ok', speakerAllowed);
      btnAllowStudentSpk.classList.toggle('danger', !speakerAllowed);
      btnAllowStudentSpk.textContent = speakerAllowed ? '🔊 Speaker Siswa: Boleh' : '🔊 Speaker Siswa: Dikunci';
      btnAllowStudentSpk.title = speakerAllowed
        ? 'Klik untuk mengunci pengaturan speaker siswa'
        : 'Klik untuk membuka pengaturan speaker siswa';
      btnAllowStudentSpk.setAttribute('aria-pressed', speakerAllowed ? 'true' : 'false');
    }
  }

  function normalizeBroadcastEnabled(textValue, enabledValue){
    const text = (textValue || '').toString().trim();
    if(enabledValue === undefined || enabledValue === null || enabledValue === ''){
      return text !== '';
    }
    const n = Number(enabledValue);
    if(Number.isFinite(n)) return n === 1;
    return !!enabledValue;
  }

  function syncTeacherTextInputState(){
    if(!broadcastInput) return;
    broadcastInput.classList.toggle('teacherTextActive', !!state.broadcastEnabled);
  }

  function setTeacherTextState(textValue, enabledValue){
    state.broadcastText = (textValue || '').toString();
    state.broadcastEnabled = normalizeBroadcastEnabled(state.broadcastText, enabledValue);
    if(broadcastInput) broadcastInput.value = state.broadcastText;
    syncTeacherTextInputState();
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
  function readSavedVolume(key, fallback){
    const raw = Number(getSaved(key));
    if(!Number.isFinite(raw)) return fallback;
    return clamp01(raw);
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
    if(d.kind === 'audioinput') return `Mikrofon ${idx+1}`;
    if(d.kind === 'audiooutput') return `Speaker ${idx+1}`;
    return `Perangkat ${idx+1}`;
  }

  function pidOf(v){
    const n = Number(v);
    return Number.isFinite(n) ? n : 0;
  }

  function studentNameByPid(pid){
    const id = pidOf(pid);
    if(!id) return 'Siswa';
    const participant = state.participants.get(id);
    const name = (participant && participant.student_name ? participant.student_name : '').toString().trim();
    return name || `Siswa ${id}`;
  }

  function chatSenderLabel(payload){
    const senderType = (payload && payload.sender_type ? payload.sender_type : '').toString().toLowerCase();
    if(senderType === 'admin') return 'Guru';
    if(senderType === 'student') return studentNameByPid(payload ? payload.sender_participant_id : 0);
    return senderType || 'Pengguna';
  }

  function chatMetaForGuru(eventType, payload){
    const p = payload || {};
    const senderType = (p.sender_type || '').toString().toLowerCase();
    const senderName = chatSenderLabel(p);
    const targetName = studentNameByPid(p.target_participant_id);

    if(eventType === 'message_sent'){
      if(senderType === 'admin'){
        return { meta: 'Mengirim ke Semua Siswa', emphasis: false, danger: false };
      }
      return { meta: `Pesan dari ${senderName}`, emphasis: false, danger: false };
    }

    if(eventType === 'message_private_admin'){
      if(senderType === 'admin'){
        return { meta: 'Pesan untuk Guru', emphasis: false, danger: false };
      }
      return { meta: `Pesan dari ${senderName}`, emphasis: true, danger: true };
    }

    if(eventType === 'message_private_student'){
      if(senderType === 'admin'){
        return { meta: `Mengirimkan pesan ke ${targetName}`, emphasis: true, danger: false };
      }
      return { meta: `Pesan dari ${senderName}`, emphasis: false, danger: false };
    }

    return { meta: 'Pesan', emphasis: false, danger: false };
  }

  function normalizePresenceState(raw){
    const data = raw || {};
    const online = !!data.online;
    const stateRaw = (data.state || (online ? 'online' : 'offline')).toString().toLowerCase();
    const pageRaw = (data.page || 'other').toString().toLowerCase();
    const reasonRaw = (data.reason || '').toString().toLowerCase();

    return {
      online,
      state: stateRaw,
      page: pageRaw,
      reason: reasonRaw,
      lastSeenAt: (data.last_seen_at || '').toString(),
      updatedAt: (data.presence_updated_at || '').toString(),
    };
  }

  function presenceReasonLabel(presence){
    const page = (presence && presence.page ? presence.page : 'other').toString().toLowerCase();
    const reason = (presence && presence.reason ? presence.reason : '').toString().toLowerCase();
    const state = (presence && presence.state ? presence.state : 'offline').toString().toLowerCase();

    if(state === 'online') return 'Sedang aktif di halaman sesi';

    if(reason === 'outside_session_page') return 'Tidak berada di halaman sesi';
    if(reason === 'tab_hidden') return 'Membuka halaman lain / mengecilkan jendela';
    if(reason === 'browser_closed' || reason === 'pagehide') return 'Halaman ditutup / keluar dari sesi';
    if(reason === 'heartbeat_timeout') return 'Koneksi ke sesi terputus';

    if(page === 'settings') return 'Sedang di halaman pengaturan';
    if(page === 'about') return 'Sedang di halaman tentang';
    if(page === 'session') return 'Tidak fokus di halaman sesi';
    return 'Status tidak aktif';
  }

  function warningMessageForParticipant(pid){
    const presence = normalizePresenceState(state.presence.get(pid));
    const reason = presence.reason;
    const page = presence.page;

    if(reason === 'outside_session_page' || page === 'other'){
      return 'Peringatan guru: kamu tidak berada di halaman sesi. Segera kembali ke halaman sesi.';
    }
    if(reason === 'tab_hidden'){
      return 'Peringatan guru: kamu membuka halaman lain. Segera kembali ke halaman sesi.';
    }
    if(reason === 'browser_closed' || reason === 'pagehide' || reason === 'heartbeat_timeout'){
      return 'Peringatan guru: koneksi sesi kamu terputus. Buka kembali halaman sesi.';
    }
    return 'Peringatan guru: tetap fokus di halaman sesi.';
  }

  state.micVolume = readSavedVolume(STORAGE.micVolume, 1);
  state.speakerVolume = readSavedVolume(STORAGE.spkVolume, 1);

  function syncAdminVolumeUi(){
    const micPct = toPercentText(state.micVolume);
    const spkPct = toPercentText(state.speakerVolume);
    if(rngAdminMicVol) rngAdminMicVol.value = String(Math.round(clamp01(state.micVolume) * 100));
    if(txtAdminMicVol) txtAdminMicVol.textContent = micPct;
    if(rngAdminSpkVol) rngAdminSpkVol.value = String(Math.round(clamp01(state.speakerVolume) * 100));
    if(txtAdminSpkVol) txtAdminSpkVol.textContent = spkPct;
  }

  function applyAdminSpeakerVolume(){
    const vol = clamp01(state.speakerVolume);
    if(adminRemoteAudio) adminRemoteAudio.volume = vol;
    if(audioPool){
      audioPool.querySelectorAll('audio').forEach(a=>{ a.volume = vol; });
    }
  }

  function applyAdminMicVolume(){
    const vol = clamp01(state.micVolume);
    if(!rtc.localTrack) return;
    if(typeof rtc.localTrack.applyConstraints === 'function'){
      rtc.localTrack.applyConstraints({ advanced: [{ volume: vol }] }).catch(()=>{});
    }
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
    applyAdminSpeakerVolume();

    state.audioUnlocked = ok;
    if(btnEnableAdminAudio) btnEnableAdminAudio.classList.toggle('ok', ok);
    if(!ok && auto){
      setCallStatus('Klik "Aktifkan Suara" jika suara belum keluar.');
    }

    syncAudioIndicator();
    updateVoiceStatus();
  }

  if(btnEnableAdminAudio){
    btnEnableAdminAudio.addEventListener('click', ()=> unlockAdminAudio(false));
  }

  function bindAutoAudioUnlockByGesture(){
    const events = ['pointerdown', 'keydown', 'touchstart'];
    const handler = ()=>{
      if(state.audioUnlocked){
        cleanup();
        return;
      }
      unlockAdminAudio(true).catch(()=>{}).finally(()=>{
        if(state.audioUnlocked){
          cleanup();
        }
      });
    };
    const cleanup = ()=>{
      events.forEach((evt)=> document.removeEventListener(evt, handler, true));
    };
    events.forEach((evt)=> document.addEventListener(evt, handler, true));
  }

  async function refreshDevices(){
    if(!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices){
      if(selAdminMic){
        selAdminMic.innerHTML = '<option value="">Peramban tidak mendukung pemilihan mikrofon</option>';
        selAdminMic.disabled = true;
      }
      if(selAdminSpk){
        selAdminSpk.innerHTML = '<option value="">Peramban tidak mendukung pemilihan keluaran suara</option>';
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
      const emptyMicText = needPermissionHint ? 'Klik daftar pilihan untuk meminta izin mikrofon' : 'Tidak ada mikrofon';
      selAdminMic.innerHTML = inputs.length
        ? inputs.map((d,i)=> `<option value="${esc(d.deviceId)}">${esc(labelForDevice(d,i))}</option>`).join('')
        : `<option value="">${emptyMicText}</option>`;
      selAdminMic.disabled = inputs.length === 0;
    }

    const canPickOutput = !!(adminRemoteAudio && typeof adminRemoteAudio.setSinkId === 'function');
    if(selAdminSpk){
      const emptySpkText = needPermissionHint ? 'Klik daftar pilihan untuk meminta izin mikrofon' : 'Tidak ada speaker';
      if(!canPickOutput){
        selAdminSpk.innerHTML = '<option value="">Perangkat ini belum bisa memilih speaker secara langsung</option>';
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
      appendChat('Sistem', `Izin mikrofon diperlukan untuk memuat perangkat: ${err.message||err}`);
    }
    refreshDevices();
  }

  async function ensureLocalStream(){
    if(rtc.localStream) return rtc.localStream;

    if(!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia){
      throw new Error('Perangkat ini tidak mendukung akses mikrofon');
    }
    if(!IS_SECURE_CONTEXT && !ALLOW_INSECURE_MEDIA){
      throw new Error('Mikrofon guru memerlukan alamat situs yang aman.');
    }

    rtc.localStream = await getUserMediaWithSelectedMic();

    rtc.localTrack = rtc.localStream.getAudioTracks()[0] || null;
    applyAdminMic();

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
    setCallStatus(total ? `Voice Room: ${connected}/${total} tersambung` : 'Voice Room: menunggu/standby');
    if(btnHangupCall) btnHangupCall.disabled = (total === 0);
  }

  function createPeer(pid, callId){
    const pc = new RTCPeerConnection(getRtcConfig());
    const peer = {
      pid,
      callId,
      pc,
      pendingCandidates: [],
      audioEl: null,
      reconnectTimer: null,
      forceCloseTimer: null,
    };
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
          applyAdminSpeakerVolume();
          applySpeakerDevice();

          if(state.audioUnlocked && state.adminSpeakerOn){
            audioEl.play().catch(()=>{});
          }else{
            setCallStatus('Ada suara masuk. Klik "Aktifkan Suara".');
          }
          syncAudioIndicator();
        }
      }
    };

    pc.onconnectionstatechange = ()=>{
      if(!pc.connectionState) return;
      const connState = pc.connectionState;

      if(connState === 'connected'){
        clearPeerRecoveryTimers(peer);
      }

      updateVoiceStatus();
      if(connState === 'failed' || connState === 'closed'){
        clearPeerRecoveryTimers(peer);
        closePeer(pid, false);
        return;
      }

      if(connState === 'disconnected'){
        clearPeerRecoveryTimers(peer);
        peer.reconnectTimer = setTimeout(()=>{
          const current = rtc.peers.get(pid);
          if(!current || current !== peer || !current.pc) return;
          if(current.pc.connectionState !== 'disconnected') return;

          renegotiatePeer(current, true);
          current.forceCloseTimer = setTimeout(()=>{
            const latest = rtc.peers.get(pid);
            if(!latest || latest !== current || !latest.pc) return;
            const latestState = latest.pc.connectionState;
            if(latestState === 'disconnected' || latestState === 'failed'){
              closePeer(pid, false);
            }
          }, DISCONNECTED_FORCE_CLOSE_MS);
        }, DISCONNECTED_RECOVER_MS);
      }
    };

    if(rtc.localTrack){
      try{
        const streamForTrack = rtc.localStream || new MediaStream([rtc.localTrack]);
        pc.addTrack(rtc.localTrack, streamForTrack);
      }catch(e){}
      applyAdminMic();
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
    if(!rtc.localTrack) return;
    for(const peer of rtc.peers.values()){
      const sender = peer.pc.getSenders ? peer.pc.getSenders().find(s=> s.track && s.track.kind === 'audio') : null;
      if(sender){
        sender.replaceTrack(rtc.localTrack).catch(()=>{});
      }else{
        try{
          const streamForTrack = rtc.localStream || new MediaStream([rtc.localTrack]);
          peer.pc.addTrack(rtc.localTrack, streamForTrack);
        }catch(e){}
      }
    }
  }

  function closePeer(pid, sendSignal){
    const peer = rtc.peers.get(pid);
    if(!peer) return;
    clearPeerRecoveryTimers(peer);

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
    btnAdminMic.textContent = rtc.micOn ? 'Mikrofon Guru: Hidup' : 'Mikrofon Guru: Mati';
  }

  function syncAdminSpkBtn(){
    if(!btnAdminSpk) return;
    btnAdminSpk.classList.toggle('ok', state.adminSpeakerOn);
    btnAdminSpk.textContent = state.adminSpeakerOn ? 'Speaker Guru: Hidup' : 'Speaker Guru: Mati';
  }

  function applyAdminSpeakerState(){
    if(adminRemoteAudio) adminRemoteAudio.muted = !state.adminSpeakerOn;
    applyAdminSpeakerVolume();
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
    applyAdminMicVolume();
    if(rtc.localTrack) rtc.localTrack.enabled = rtc.micOn && state.micVolume > 0;
  }

  if(btnAdminSpk){
    syncAdminSpkBtn();
    btnAdminSpk.addEventListener('click', ()=>{
      state.adminSpeakerOn = !state.adminSpeakerOn;
      if(state.adminSpeakerOn){
        unlockAdminAudio(true).catch(()=>{});
      }
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
          appendChat('Sistem', `Mikrofon guru tidak bisa diakses: ${err.message||err}`);
        }
      }else{
        applyAdminMic();
      }
    });

    if(!IS_SECURE_CONTEXT && !ALLOW_INSECURE_MEDIA){
      btnAdminMic.title = 'Mikrofon guru biasanya memerlukan alamat situs yang aman';
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
          applyAdminMic();
          if(oldStream) oldStream.getTracks().forEach(t=>{ try{ t.stop(); }catch(e){} });
          renegotiateAllPeers();
          refreshDevices();
        }catch(err){
          appendChat('Sistem', `Gagal mengganti mikrofon guru: ${err.message||err}`);
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

  if(rngAdminMicVol){
    rngAdminMicVol.addEventListener('input', ()=>{
      state.micVolume = clamp01(Number(rngAdminMicVol.value) / 100);
      save(STORAGE.micVolume, state.micVolume.toFixed(2));
      syncAdminVolumeUi();
      applyAdminMic();
    });
  }

  if(rngAdminSpkVol){
    rngAdminSpkVol.addEventListener('input', ()=>{
      state.speakerVolume = clamp01(Number(rngAdminSpkVol.value) / 100);
      save(STORAGE.spkVolume, state.speakerVolume.toFixed(2));
      syncAdminVolumeUi();
      applyAdminSpeakerState();
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

  async function renegotiatePeer(peer, forceIceRestart){
    if(!peer || !peer.pc || !peer.callId) return;
    const pc = peer.pc;
    if(pc.connectionState === 'closed' || pc.connectionState === 'failed') return;
    if(pc.signalingState && pc.signalingState !== 'stable') return;
    try{
      const offer = await pc.createOffer({
        offerToReceiveAudio: true,
        iceRestart: !!forceIceRestart,
      });
      await pc.setLocalDescription(offer);
      await sendRtc(peer.pid, 'offer', { type: offer.type, sdp: offer.sdp }, peer.callId);
    }catch(e){}
  }

  function renegotiateAllPeers(forceIceRestart){
    for(const peer of rtc.peers.values()){
      renegotiatePeer(peer, forceIceRestart);
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

        if(rtc.localTrack){
          const hasSender = pc.getSenders && pc.getSenders().some(s=> s.track && s.track.kind === 'audio');
          if(!hasSender){
            try{
              const streamForTrack = rtc.localStream || new MediaStream([rtc.localTrack]);
              pc.addTrack(rtc.localTrack, streamForTrack);
            }catch(e){}
          }
          applyAdminMic();
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
      const presence = normalizePresenceState(state.presence.get(p.id));
      const online = presence.online ? 'online' : 'offline';
      const device = p.device_label ? esc(p.device_label) : ('Komputer-' + p.id);
      const presenceDetail = presenceReasonLabel(presence);
      const presenceStateLabel = presence.online ? 'AKTIF' : 'TIDAK AKTIF';

      return `
        <div class="pcard" data-pid="${p.id}">
          <div class="top">
            <div class="participantMeta">
              <div class="participantDevice">${device}</div>
              <div class="badge participantIdentity">${esc(p.student_name)} (${esc(p.class_name)})</div>
              <div class="badge ${online} participantState">${presenceStateLabel} • ${esc(p.ip_address||'-')}</div>
              <div class="muted tiny participantPresence">${esc(presenceDetail)}</div>
            </div>
            <div class="participantActions">
              <div class="row gap participantPrimaryActions">
                <button class="btnMic ${p.mic_on? 'ok':''}" title="Mikrofon" type="button">${p.mic_on? '🎙️':'🔇'}</button>
                <button class="btnSpk ${p.speaker_on? 'ok':''}" title="Speaker" type="button">${p.speaker_on? '🔊':'🔈'}</button>
              </div>
              <div class="row gap participantQuickActions">
                <button class="btnPrivate participantIconBtn" title="Pesan ke siswa ini" aria-label="Pesan ke siswa ini" type="button">✉️</button>
                <button class="btnWarn participantIconBtn danger" title="Kirim peringatan + suara" aria-label="Kirim peringatan + suara" type="button">🚨</button>
              </div>
            </div>
          </div>
        </div>`;
    }).join('');

    // Pertahankan pilihan + susun ulang daftar penerima privat
    if(privateTargetSel){
      const old = String(state.selectedPrivateTarget || '');
      const opts = arr.map(p=>{
        return `<option value="${p.id}">${esc(p.student_name)} (${esc(p.class_name)})</option>`;
      }).join('');

      privateTargetSel.innerHTML = `<option value="">-- pilih nama siswa --</option>` + opts;
      privateTargetSel.value = old;
    }
  }

  function appendChat(meta, body, options){
    if(!chatLog) return;
    const div = document.createElement('div');
    div.className = 'msg';

    const metaEl = document.createElement('div');
    metaEl.className = 'meta';
    if(options && options.emphasis) metaEl.classList.add('emphasis');
    if(options && options.danger) metaEl.classList.add('danger');
    metaEl.textContent = (meta || '').toString();

    const bodyEl = document.createElement('div');
    bodyEl.textContent = (body || '').toString();

    div.appendChild(metaEl);
    div.appendChild(bodyEl);
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

      if(t === 'participant_left'){
        const pid = pidOf(p.participant_id || p.id);
        if(!pid) continue;
        state.participants.delete(pid);
        scheduleRenderParticipants();
      }

      if(t === 'participant_updated'){
        const pid = pidOf(p.participant_id || p.id);
        if(!pid) continue;
        const current = state.participants.get(pid) || { id: pid };
        if(p.student_name !== undefined) current.student_name = p.student_name;
        if(p.class_name !== undefined) current.class_name = p.class_name;
        if(p.device_label !== undefined) current.device_label = p.device_label;
        if(p.ip_address !== undefined) current.ip_address = p.ip_address;
        if(p.mic_on !== undefined) current.mic_on = p.mic_on ? 1 : 0;
        if(p.speaker_on !== undefined) current.speaker_on = p.speaker_on ? 1 : 0;
        if(current.student_name === undefined) current.student_name = 'Siswa ' + pid;
        if(current.class_name === undefined) current.class_name = '-';
        if(current.mic_on === undefined) current.mic_on = 0;
        if(current.speaker_on === undefined) current.speaker_on = 1;
        state.participants.set(pid, current);
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
        const info = chatMetaForGuru(t, p);
        appendChat(info.meta, p.body, { emphasis: info.emphasis, danger: info.danger });
      }

      if(t === 'message_private_admin'){
        const info = chatMetaForGuru(t, p);
        appendChat(info.meta, p.body, { emphasis: info.emphasis, danger: info.danger });
      }

      if(t === 'message_private_student'){
        const info = chatMetaForGuru(t, p);
        appendChat(info.meta, p.body, { emphasis: info.emphasis, danger: info.danger });
      }

      if(t === 'broadcast_text_changed'){
        setTeacherTextState(p.broadcast_text || '', p.broadcast_enabled);
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
        appendChat('Sistem', 'Sesi ditutup.');
        hangupAll(false).catch(()=>{});
        if(!sessionReloadQueued){
          sessionReloadQueued = true;
          setTimeout(()=> window.location.reload(), 1200);
        }
      }

      if(t === 'session_extended'){
        appendChat('Sistem', 'Batas sesi diperpanjang 30 menit.');
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
      setTeacherTextState(snap.state.broadcast_text || '', snap.state.broadcast_enabled);
      if(snap.state.allow_student_mic !== undefined){
        state.allowStudentMic = !!snap.state.allow_student_mic;
      }
      if(snap.state.allow_student_speaker !== undefined){
        state.allowStudentSpeaker = !!snap.state.allow_student_speaker;
      }
      syncLockUI();
    }

    if(snap && Object.prototype.hasOwnProperty.call(snap, 'currentMaterial')){
      renderMaterialBox(snap.currentMaterial || null);
    }
  }

  function handlePresence(list){
    state.presence.clear();
    for(const it of list){
      const pid = pidOf(it.id);
      if(!pid) continue;
      state.presence.set(pid, normalizePresenceState(it));
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
        appendChat('Sistem', `Mikrofon guru belum bisa diakses otomatis: ${err.message||err}`);
      }
    }

    try{
      await unlockAdminAudio(true);
    }catch(e){}
  }

  function selectedTextFromCurrentMaterial(cm){
    const legacy = cm && cm.selected ? cm.selected : null;
    if(cm && cm.selected_text && typeof cm.selected_text === 'object'){
      return cm.selected_text;
    }
    if(legacy && legacy.type === 'text'){
      return { index: legacy.index, text: legacy.text || '' };
    }
    return null;
  }

  function selectedFileFromCurrentMaterial(cm){
    const legacy = cm && cm.selected ? cm.selected : null;
    if(cm && cm.selected_file && typeof cm.selected_file === 'object'){
      return cm.selected_file;
    }
    if(legacy && legacy.type === 'file' && legacy.file){
      return legacy.file;
    }
    return null;
  }

  function getMaterialRenderSignature(cm){
    if(!cm || !cm.material) return 'none';
    const m = cm.material || {};
    const files = Array.isArray(cm.files) ? cm.files : (cm.file ? [cm.file] : []);
    const textItems = Array.isArray(cm.text_items) ? cm.text_items : [];
    const selectedText = selectedTextFromCurrentMaterial(cm);
    const selectedFile = selectedFileFromCurrentMaterial(cm);

    const selectedTextSig = selectedText
      ? `${selectedText.index ?? ''}:${selectedText.text ?? ''}`
      : '';
    const selectedFileSig = selectedFile
      ? [
          selectedFile.id || '',
          selectedFile.filename || '',
          selectedFile.url_path || '',
          selectedFile.preview_url_path || '',
          selectedFile.cover_url_path || '',
          selectedFile.mime || '',
        ].join('|')
      : '';

    const filesSig = files
      .map((f)=> [f.id || '', f.filename || '', f.url_path || ''].join(':'))
      .join(',');
    const textsSig = textItems.join('\n');

    return [
      m.id || '',
      m.title || '',
      m.type || '',
      selectedTextSig,
      selectedFileSig,
      filesSig,
      textsSig,
    ].join('||');
  }

  function renderMaterialBox(cm){
    if(!matBox) return;
    const nextSig = getMaterialRenderSignature(cm);
    if(nextSig === materialRenderSig) return;
    materialRenderSig = nextSig;

    if(!cm || !cm.material){
      matBox.textContent = 'Belum ada materi.';
      matBox.classList.add('muted');
      _boundAdminMedia = null;
      return;
    }

    const m = cm.material || {};
    const files = Array.isArray(cm.files) ? cm.files : (cm.file ? [cm.file] : []);
    const textItems = Array.isArray(cm.text_items) ? cm.text_items : [];
    const selectedText = selectedTextFromCurrentMaterial(cm);
    const selectedFile = selectedFileFromCurrentMaterial(cm);

    const isOfficeDoc = (name)=> /\.(docx?|xlsx?|pptx?)$/i.test(name||'');
    const isPdf = (name)=> /\.pdf$/i.test(name||'');
    const getCoverUrl = (file)=>{
      if(!file) return '';
      return file.cover_url_path || file.poster_url_path || file.thumbnail_url_path || '';
    };
    const renderFileNameBelow = (filename)=>{
      if(!filename) return '';
      return `<div class="fileTitle materialFileName">${esc(filename)}</div>`;
    };
    const renderFilePreview = (file)=>{
      if(!file || !file.url_path) return '';
      const mime = (file.mime||'').toLowerCase();
      const url = esc(file.url_path);
      const previewUrl = file.preview_url_path ? esc(file.preview_url_path) : '';
      const filename = file.filename || 'berkas';
      const fileIdAttr = file.id ? ` data-file-id="${file.id}"` : '';
      const coverUrl = getCoverUrl(file);

      if(mime.startsWith('audio/')){
        return `<div class="mediaBlock">
          ${coverUrl ? `<img class="mediaCover" src="${esc(coverUrl)}" alt="Sampul ${esc(filename)}">` : ''}
          <audio data-admin-media="1"${fileIdAttr} controls src="${url}" style="width:100%"></audio>
          ${renderFileNameBelow(filename)}
        </div>`;
      }
      if(mime.startsWith('video/')){
        const poster = coverUrl ? ` poster="${esc(coverUrl)}"` : '';
        return `<div class="mediaBlock">
          ${coverUrl ? `<img class="mediaCover" src="${esc(coverUrl)}" alt="Sampul ${esc(filename)}">` : ''}
          <video data-admin-media="1"${fileIdAttr}${poster} controls src="${url}" style="max-width:100%"></video>
          ${renderFileNameBelow(filename)}
        </div>`;
      }
      if(mime.startsWith('image/')){
        return `<div class="mediaBlock">
          <div class="row between wrap gap" style="align-items:center; margin-bottom:6px">
            <button class="btn tiny" type="button" data-preview-url="${url}" data-preview-title="${esc(filename)}">Perbesar</button>
          </div>
          <img src="${url}" alt="${esc(filename)}" style="max-width:100%;height:auto">
          ${renderFileNameBelow(filename)}
        </div>`;
      }
      if(mime === 'application/pdf' || isPdf(filename) || isPdf(url) || previewUrl){
        const pdfUrl = previewUrl || url;
        return `<div class="docPreview">
          <div class="docPreviewBody">
            <iframe class="docFrame small" src="${pdfUrl}"></iframe>
          </div>
          <div class="row gap" style="margin-top:8px">
            <button class="btn tiny" type="button" data-preview-url="${pdfUrl}" data-preview-title="${esc(filename)}">Perbesar</button>
            <a class="btn tiny" href="${pdfUrl}" target="_blank">Buka</a>
          </div>
          ${renderFileNameBelow(filename)}
        </div>`;
      }
      if(isOfficeDoc(filename) || isOfficeDoc(url)){
        return `<div class="docPreview">
          <div class="muted tiny" style="margin-top:8px">
            Pratinjau lokal belum tersedia untuk berkas ini. Buka berkas langsung di halaman baru.
          </div>
          <div class="row gap" style="margin-top:8px">
            <button class="btn tiny" type="button" data-preview-url="${url}" data-preview-title="${esc(filename)}">Perbesar</button>
            <a class="btn tiny" href="${url}" target="_blank">Buka</a>
          </div>
          ${renderFileNameBelow(filename)}
        </div>`;
      }
      return `<div class="mediaBlock">
        <div><a href="${url}" target="_blank">Buka berkas</a></div>
        ${renderFileNameBelow(filename)}
      </div>`;
    };

    let html = `<div><b>${esc(m.title||'')}</b></div>`;
    html += `<div class="materialPreviewCard" style="margin-top:10px">`;
    html += `<div class="muted tiny">Pratinjau yang sedang ditampilkan ke siswa</div>`;

    let hasPreview = false;
    if(selectedText){
      hasPreview = true;
      html += `<div class="muted tiny" style="margin-top:8px">Teks yang ditampilkan (Daftar Teks)</div>`;
      html += `<div class="materialText">${esc(selectedText.text||'')}</div>`;
    }
    if(selectedFile){
      hasPreview = true;
      html += `<div class="muted tiny" style="margin-top:10px">Berkas yang ditampilkan</div>`;
      html += renderFilePreview(selectedFile);
    }
    if(!hasPreview){
      html += `<div class="muted" style="margin-top:8px">Belum ada item yang ditampilkan.</div>`;
    }
    html += `</div>`;

    html += `<div class="materialManageCard" style="margin-top:10px">`;
    if(textItems.length){
      html += `<div class="row between wrap gap" style="align-items:center">
        <div class="muted tiny">Daftar Teks</div>
        <button class="btn tiny iconBtn" type="button" data-mat-action="hide-text" title="Tutup teks yang sedang ditampilkan" aria-label="Tutup teks yang sedang ditampilkan">&times;</button>
      </div>`;
      html += `<ol class="materialList">`;
      textItems.forEach((t, i)=>{
        const active = !!(selectedText && selectedText.index !== null && selectedText.index !== undefined && Number(selectedText.index) === i);
        html += `<li class="materialItem ${active ? 'active' : ''}">
          <div class="label"><span class="muted">${i+1}.</span> ${esc(t)}</div>
          <button class="btn tiny iconBtn materialActionBtn" type="button" data-mat-action="pick-text" data-text-index="${i}" title="Tampilkan teks ini" aria-label="Tampilkan teks ini">&#9654;</button>
        </li>`;
      });
      html += `</ol>`;
    }

    if(files.length){
      html += `<div class="row between wrap gap" style="margin-top:10px;align-items:center">
        <div class="muted tiny">Daftar Berkas</div>
        <button class="btn tiny iconBtn" type="button" data-mat-action="hide-file" title="Tutup berkas materi yang sedang ditampilkan" aria-label="Tutup berkas materi yang sedang ditampilkan">&times;</button>
      </div>`;
      html += `<ul class="materialList">`;
      files.forEach((f)=>{
        const active = !!(selectedFile && Number(selectedFile.id) === Number(f.id));
        html += `<li class="materialItem ${active ? 'active' : ''}">
          <div class="label">${esc(f.filename||'berkas')}</div>
          <button class="btn tiny iconBtn materialActionBtn" type="button" data-mat-action="pick-file" data-file-id="${f.id}" title="Tampilkan berkas ini" aria-label="Tampilkan berkas ini">&#9654;</button>
        </li>`;
      });
      html += `</ul>`;
    }
    if(!textItems.length && !files.length){
      html += `<div class="muted">Belum ada teks atau berkas dalam materi ini.</div>`;
    }
    html += `</div>`;

    matBox.innerHTML = html;
    matBox.classList.remove('muted');
    _boundAdminMedia = null;
    bindAdminMediaControls();
  }

  let _boundAdminMedia = null;
  function bindAdminMediaControls(){
    if(!matBox) return;
    const media = matBox.querySelector('[data-admin-media]');
    if(!media || media === _boundAdminMedia) return;
    _boundAdminMedia = media;

    const lastSent = { volume: 0, seek: 0, sync: 0 };
    const now = ()=> Date.now();
    const toFixed = (n, d)=> {
      const num = Number(n);
      if(!Number.isFinite(num)) return 0;
      const p = Math.pow(10, d);
      return Math.round(num * p) / p;
    };

    const send = (action)=>{
      const fileId = media.dataset.fileId || '';
      post('/api/material/media-control', {
        action,
        file_id: fileId,
        current_time: toFixed(media.currentTime || 0, 3),
        volume: toFixed(media.volume ?? 1, 2),
        muted: media.muted ? 1 : 0,
        paused: media.paused ? 1 : 0,
        playback_rate: toFixed(media.playbackRate || 1, 2),
      });
    };

    media.addEventListener('play', ()=> send('play'));
    media.addEventListener('pause', ()=> send('pause'));
    media.addEventListener('ratechange', ()=> send('rate'));
    media.addEventListener('loadedmetadata', ()=>{
      const t = now();
      if(t - lastSent.sync > 400){
        lastSent.sync = t;
        send('sync');
      }
    });
    media.addEventListener('seeked', ()=>{
      const t = now();
      if(t - lastSent.seek > 200){
        lastSent.seek = t;
        send('seek');
      }
    });
    media.addEventListener('volumechange', ()=>{
      const t = now();
      if(t - lastSent.volume > 200){
        lastSent.volume = t;
        send('volume');
      }
    });
  }

  async function refreshMaterial(){
    if(materialRefreshBusy){
      materialRefreshQueued = true;
      return;
    }
    materialRefreshBusy = true;
    const token = ++materialRefreshToken;
    try{
      const res = await fetch(API('/api/material/current'), {headers:{'Accept':'application/json'}});
      const data = await res.json().catch(()=>null);
      if(token < materialRefreshAppliedToken) return;
      materialRefreshAppliedToken = token;
      if(data && data.ok){
        renderMaterialBox(data.currentMaterial || null);
      }
    }catch(e){
      // ignore network error, poller akan coba lagi
    }finally{
      materialRefreshBusy = false;
      if(materialRefreshQueued){
        materialRefreshQueued = false;
        refreshMaterial();
      }
    }
  }

  // UI actions
  if(grid){
    grid.addEventListener('click', async (ev)=>{
      const card = ev.target.closest('.pcard');
      if(!card) return;

      const pid = Number(card.dataset.pid||0);
      if(!pid) return;

      const btnMicEl = ev.target.closest('.btnMic');
      if(btnMicEl && card.contains(btnMicEl)){
        const cur = state.participants.get(pid);
        const next = cur && cur.mic_on ? 0 : 1;
        await post('/api/control/admin/mic', {participant_id: pid, mic_on: next});
      }

      const btnSpkEl = ev.target.closest('.btnSpk');
      if(btnSpkEl && card.contains(btnSpkEl)){
        const cur = state.participants.get(pid);
        const next = cur && cur.speaker_on ? 0 : 1;
        await post('/api/control/admin/speaker', {participant_id: pid, speaker_on: next});
      }

      const btnPrivateEl = ev.target.closest('.btnPrivate');
      if(btnPrivateEl && card.contains(btnPrivateEl)){
        state.selectedPrivateTarget = pid;
        if(privateTargetSel) privateTargetSel.value = String(pid);

        if(chatModeSel){
          chatModeSel.value = 'private_student';
          chatModeSel.dispatchEvent(new Event('change'));
        }

        appendChat('Informasi', `Tujuan pesan diatur ke ${studentNameByPid(pid)}.`);
      }

      const btnWarnEl = ev.target.closest('.btnWarn');
      if(btnWarnEl && card.contains(btnWarnEl)){
        const message = warningMessageForParticipant(pid);
        const r = await post('/api/control/admin/warn', {
          participant_id: pid,
          warning_type: 'presence',
          message,
        });
        if(r && r.ok){
          const label = `${r.student_name || 'Siswa'}${r.class_name ? ' (' + r.class_name + ')' : ''}`;
          appendChat('Sistem', `Peringatan + suara dikirim ke ${label}.`);
        }else{
          appendChat('Sistem', (r && r.error) ? r.error : 'Gagal mengirim peringatan.');
        }
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
        appendChat('Sistem','Pilih siswa dulu.');
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

  async function applyTeacherText(value){
    const text = (value || '').toString();
    const r = await post('/api/control/admin/broadcast-text', {broadcast_text: text});
    if(r && r.ok){
      setTeacherTextState(r.broadcast_text || '', r.broadcast_enabled);
      refreshMaterial();
    }
  }

  if(btnBroadcast){
    btnBroadcast.onclick = ()=> applyTeacherText((broadcastInput && broadcastInput.value) ? broadcastInput.value : '');
  }

  if(btnClearBroadcast){
    btnClearBroadcast.onclick = ()=> applyTeacherText('');
  }

  if(matBox){
    matBox.addEventListener('click', async (ev)=>{
      const btn = ev.target.closest('[data-mat-action]');
      if(!btn) return;
      const action = btn.dataset.matAction || '';
      if(action === 'pick-file'){
        const fileId = btn.dataset.fileId || '';
        const r = await post('/api/material/select', {item_type:'file', file_id: fileId});
        if(r && r.ok) refreshMaterial();
      }
      if(action === 'pick-text'){
        const textIndex = btn.dataset.textIndex || '';
        const r = await post('/api/material/select', {item_type:'text', text_index: textIndex});
        if(r && r.ok){
          if(state.broadcastEnabled){
            setTeacherTextState(state.broadcastText, 0);
          }
          refreshMaterial();
        }
      }
      if(action === 'hide-text'){
        const r = await post('/api/material/select', {item_type:'clear_text'});
        if(r && r.ok) refreshMaterial();
      }
      if(action === 'hide-file'){
        const r = await post('/api/material/select', {item_type:'clear_file'});
        if(r && r.ok) refreshMaterial();
      }
    });
  }

  async function saveVoiceLock(nextMicAllowed, nextSpeakerAllowed){
    if(!btnAllowStudentMic && !btnAllowStudentSpk) return;

    const prevMicAllowed = !!state.allowStudentMic;
    const prevSpeakerAllowed = !!state.allowStudentSpeaker;
    const targetMicAllowed = (nextMicAllowed === undefined) ? prevMicAllowed : !!nextMicAllowed;
    const targetSpeakerAllowed = (nextSpeakerAllowed === undefined) ? prevSpeakerAllowed : !!nextSpeakerAllowed;

    state.allowStudentMic = targetMicAllowed;
    state.allowStudentSpeaker = targetSpeakerAllowed;
    syncLockUI();

    if(btnAllowStudentMic) btnAllowStudentMic.disabled = true;
    if(btnAllowStudentSpk) btnAllowStudentSpk.disabled = true;

    const payload = {
      allow_student_mic: targetMicAllowed ? 1 : 0,
      allow_student_speaker: targetSpeakerAllowed ? 1 : 0,
    };
    const r = await post('/api/control/admin/voice-lock', payload);
    if(r && r.ok){
      state.allowStudentMic = (r.allow_student_mic !== undefined) ? !!r.allow_student_mic : targetMicAllowed;
      state.allowStudentSpeaker = (r.allow_student_speaker !== undefined) ? !!r.allow_student_speaker : targetSpeakerAllowed;
    }else{
      const msg = (r && r.error) ? r.error : 'Gagal menyimpan kontrol mikrofon/speaker siswa.';
      appendChat('Sistem', msg);
      state.allowStudentMic = prevMicAllowed;
      state.allowStudentSpeaker = prevSpeakerAllowed;
    }

    if(btnAllowStudentMic) btnAllowStudentMic.disabled = false;
    if(btnAllowStudentSpk) btnAllowStudentSpk.disabled = false;
    syncLockUI();
  }

  if(btnAllowStudentMic){
    btnAllowStudentMic.addEventListener('click', ()=>{
      saveVoiceLock(!state.allowStudentMic, state.allowStudentSpeaker);
    });
  }
  if(btnAllowStudentSpk){
    btnAllowStudentSpk.addEventListener('click', ()=>{
      saveVoiceLock(state.allowStudentMic, !state.allowStudentSpeaker);
    });
  }

  if(btnRefreshMaterial){
    btnRefreshMaterial.onclick = refreshMaterial;
  }

  // When leaving page, try to hangup cleanly (opsional tapi membantu)
  window.addEventListener('pagehide', ()=>{
    sendBeaconHangupAll();
  });

  // Start polling
  if(!window.EventPoller){
    appendChat('Sistem', 'EventPoller tidak ditemukan. Pastikan poll.js dimuat.');
    return;
  }

  const poller = new window.EventPoller({
    intervalMs: 1200,
    onSnapshot: handleSnapshot,
    onEvents: handleEvents,
    onPresence: handlePresence,
  });
  poller.start();

  setTeacherTextState(
    (broadcastInput && broadcastInput.value) ? broadcastInput.value : '',
    (broadcastInput && Object.prototype.hasOwnProperty.call(broadcastInput.dataset, 'enabled'))
      ? broadcastInput.dataset.enabled
      : null
  );
  refreshMaterial();
  updateVoiceStatus();
  syncLockUI();
  syncAdminSpkBtn();
  syncAdminVolumeUi();
  applyAdminSpeakerState();
  refreshDevices();
  setTimeout(()=>{ autoInitAudio().catch(()=>{}); }, 300);
  bindAutoAudioUnlockByGesture();
  if(navigator.mediaDevices && navigator.mediaDevices.addEventListener){
    navigator.mediaDevices.addEventListener('devicechange', refreshDevices);
  }
  window.addEventListener('pageshow', ()=> scheduleAdminVoiceRecovery(140));
  window.addEventListener('online', ()=> scheduleAdminVoiceRecovery(120));
  window.addEventListener('focus', ()=> scheduleAdminVoiceRecovery(120));
  document.addEventListener('visibilitychange', ()=>{
    if(!document.hidden) scheduleAdminVoiceRecovery(120);
  });

})();

