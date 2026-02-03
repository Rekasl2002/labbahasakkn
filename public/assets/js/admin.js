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
  };

  const grid = document.getElementById('participantsGrid');
  const chatLog = document.getElementById('chatLog');
  const chatInput = document.getElementById('chatInput');
  const btnSendChat = document.getElementById('btnSendChat');
  const chatModeSel = document.getElementById('chatMode');
  const privateTargetSel = document.getElementById('privateTarget');
  const broadcastInput = document.getElementById('broadcastText');
  const btnBroadcast = document.getElementById('btnBroadcastText');
  const matBox = document.getElementById('currentMaterialBox');
  const btnRefreshMaterial = document.getElementById('btnRefreshMaterial');

  // Voice (WebRTC)
  const callStatusEl = document.getElementById('callStatus');
  const btnAdminMic = document.getElementById('btnAdminMic');
  const btnHangupCall = document.getElementById('btnHangupCall');
  const adminRemoteAudio = document.getElementById('adminRemoteAudio');
  const selAdminMic = document.getElementById('selAdminMic');
  const selAdminSpk = document.getElementById('selAdminSpk');

  const rtc = {
    pc: null,
    callId: null,
    targetPid: null,
    pendingCandidates: [],
    localStream: null,
    localTrack: null,
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
  };

  let _renderScheduled = false;

  function esc(s){ return (s??'').toString().replace(/[&<>"]/g, c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;' }[c])); }
  function nowTime(){ return new Date().toLocaleTimeString(); }

  function setCallStatus(text){
    if(callStatusEl) callStatusEl.textContent = text || '';
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
    if(!adminRemoteAudio || !selAdminSpk) return;
    if(typeof adminRemoteAudio.setSinkId !== 'function'){
      return;
    }
    const id = state.selectedSpkId || '';
    if(!id) return;
    adminRemoteAudio.setSinkId(id).catch(()=>{});
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
      if(savedMic && inputs.some(d=> d.deviceId === savedMic)){
        selAdminMic.value = savedMic;
        state.selectedMicId = savedMic;
      }else if(inputs[0]){
        selAdminMic.value = inputs[0].deviceId;
        state.selectedMicId = inputs[0].deviceId;
      }
    }

    if(selAdminSpk){
      const savedSpk = state.selectedSpkId || getSaved(STORAGE.spk);
      if(savedSpk && outputs.some(d=> d.deviceId === savedSpk)){
        selAdminSpk.value = savedSpk;
        state.selectedSpkId = savedSpk;
      }else if(outputs[0] && !selAdminSpk.disabled){
        selAdminSpk.value = outputs[0].deviceId;
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

  function closePeerOnly(){
    if(rtc.pc){
      try{ rtc.pc.onicecandidate = null; rtc.pc.ontrack = null; }catch(e){}
      try{ rtc.pc.close(); }catch(e){}
    }
    rtc.pc = null;
    rtc.pendingCandidates = [];
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

  function sendBeaconHangup(){
    try{
      const pid = rtc.targetPid;
      const cid = rtc.callId;
      if(!pid || !cid) return;

      const body = new URLSearchParams();
      body.append('to_type', 'participant');
      body.append('to_participant_id', String(pid));
      body.append('signal_type', 'hangup');
      body.append('call_id', String(cid));
      body.append('data', '{}');

      if(navigator.sendBeacon){
        navigator.sendBeacon(API('/api/rtc/signal'), body);
      }
    }catch(e){}
  }

  async function hangup(sendSignal){
    const pid = rtc.targetPid;
    const cid = rtc.callId;

    if(sendSignal && pid && cid){
      try{
        await post('/api/rtc/signal', {
          to_type: 'participant',
          to_participant_id: pid,
          signal_type: 'hangup',
          call_id: cid,
          data: JSON.stringify({}),
        });
      }catch(e){}
    }

    closePeerOnly();
    if(adminRemoteAudio) adminRemoteAudio.srcObject = null;

    rtc.callId = null;
    rtc.targetPid = null;

    stopLocalStream();
    setCallStatus('Idle');
    if(btnHangupCall) btnHangupCall.disabled = true;

    scheduleRenderParticipants();
  }

  function syncAdminMicBtn(){
    if(!btnAdminMic) return;
    btnAdminMic.classList.toggle('ok', rtc.micOn);
    btnAdminMic.textContent = rtc.micOn ? 'Mic Admin: ON' : 'Mic Admin: OFF';
  }

  function applyAdminMic(){
    if(rtc.localTrack) rtc.localTrack.enabled = rtc.micOn;
  }

  if(btnAdminMic){
    syncAdminMicBtn();
    btnAdminMic.addEventListener('click', ()=>{
      rtc.micOn = !rtc.micOn;
      syncAdminMicBtn();
      applyAdminMic();
    });

    if(!IS_SECURE_CONTEXT && !ALLOW_INSECURE_MEDIA){
      btnAdminMic.title = 'Mic admin biasanya butuh HTTPS/localhost';
    }
  }

  if(btnHangupCall){
    btnHangupCall.addEventListener('click', ()=> hangup(true));
  }

  if(selAdminMic){
    state.selectedMicId = getSaved(STORAGE.mic);
    selAdminMic.addEventListener('mousedown', requestDeviceAccess);
    selAdminMic.addEventListener('focus', requestDeviceAccess);
    selAdminMic.addEventListener('change', async ()=>{
      state.selectedMicId = selAdminMic.value || '';
      save(STORAGE.mic, state.selectedMicId);
      if(rtc.localStream || rtc.pc){
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
          if(rtc.localTrack) rtc.localTrack.enabled = rtc.micOn;
          if(oldStream) oldStream.getTracks().forEach(t=>{ try{ t.stop(); }catch(e){} });
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
      applySpeakerDevice();
    });
  }

  async function sendRtc(toPid, signalType, data){
    if(!rtc.callId || !toPid) return;
    await post('/api/rtc/signal', {
      to_type: 'participant',
      to_participant_id: toPid,
      signal_type: signalType,
      call_id: rtc.callId,
      data: JSON.stringify(data || {}),
    });
  }

  function mkCallId(){
    if(window.crypto && crypto.randomUUID) return crypto.randomUUID();
    return String(Date.now()) + '-' + Math.random().toString(16).slice(2);
  }

  async function startCall(pid){
    const target = state.participants.get(pid);
    if(!target){
      appendChat('System', 'Peserta tidak ditemukan.');
      return;
    }

    // toggle off if clicking same target
    if(rtc.pc && rtc.targetPid === pid){
      await hangup(true);
      return;
    }

    // close any existing call
    if(rtc.pc){
      await hangup(true);
    }

    rtc.targetPid = pid;
    rtc.callId = mkCallId();
    rtc.pendingCandidates = [];

    setCallStatus(`Calling ${target.student_name}...`);
    if(btnHangupCall) btnHangupCall.disabled = false;

    const pc = new RTCPeerConnection(getRtcConfig());
    rtc.pc = pc;

    pc.onicecandidate = (ev)=>{
      if(ev.candidate){
        const cand = (ev.candidate.toJSON ? ev.candidate.toJSON() : ev.candidate);
        sendRtc(pid, 'candidate', { candidate: cand }).catch(()=>{});
      }
    };

    pc.ontrack = (ev)=>{
      const stream = (ev.streams && ev.streams[0]) ? ev.streams[0] : null;
      if(stream && adminRemoteAudio){
        adminRemoteAudio.srcObject = stream;
        adminRemoteAudio.muted = false;
        applySpeakerDevice();

        // StartCall dipicu klik user, jadi play biasanya aman
        adminRemoteAudio.play().catch(()=>{});
      }
    };

    pc.onconnectionstatechange = ()=>{
      if(!pc.connectionState) return;
      setCallStatus(`RTC: ${pc.connectionState}`);
      if(pc.connectionState === 'failed' || pc.connectionState === 'closed'){
        hangup(false).catch(()=>{});
      }
    };

    // Add local track (admin mic). If permission denied / not secure, still proceed receive-only.
    try{
      const ls = await ensureLocalStream();
      ls.getTracks().forEach(t=>{ try{ pc.addTrack(t, ls); }catch(e){} });
      applyAdminMic();
      refreshDevices();
    }catch(err){
      appendChat('System', `Mic admin tidak bisa diakses (receive-only): ${err.message||err}`);
    }

    const offer = await pc.createOffer({ offerToReceiveAudio: true });
    await pc.setLocalDescription(offer);

    await sendRtc(pid, 'offer', { type: offer.type, sdp: offer.sdp });

    scheduleRenderParticipants();
  }

  async function handleRtcSignal(payload){
    if(!payload || payload.to_type !== 'admin') return;

    const fromPid = Number(payload.from_participant_id || 0);
    if(!fromPid) return;

    // only accept signals for active call
    if(!rtc.pc || rtc.targetPid !== fromPid) return;
    if(payload.call_id !== rtc.callId) return;

    const st = payload.signal_type;
    const data = payload.data || {};

    try{
      if(st === 'answer'){
        if(!data.sdp) return;

        await rtc.pc.setRemoteDescription(new RTCSessionDescription({
          type: data.type || 'answer',
          sdp: data.sdp
        }));

        for(const c of rtc.pendingCandidates){
          await rtc.pc.addIceCandidate(new RTCIceCandidate(c));
        }
        rtc.pendingCandidates = [];

      } else if(st === 'candidate'){
        const cand = data.candidate;
        if(!cand) return;

        if(rtc.pc.remoteDescription && rtc.pc.remoteDescription.type){
          await rtc.pc.addIceCandidate(new RTCIceCandidate(cand));
        } else {
          rtc.pendingCandidates.push(cand);
          if(rtc.pendingCandidates.length > MAX_PENDING_CANDIDATES){
            rtc.pendingCandidates = rtc.pendingCandidates.slice(-MAX_PENDING_CANDIDATES);
          }
        }

      } else if(st === 'hangup'){
        await hangup(false);
      }
    }catch(e){}
  }

  function renderParticipants(){
    if(!grid) return;

    const arr = Array.from(state.participants.values());

    grid.innerHTML = arr.map(p=>{
      const online = state.presence.get(p.id) ? 'online' : 'offline';
      const device = p.device_label ? esc(p.device_label) : ('PC-' + p.id);

      const inCall = (rtc.targetPid === p.id && !!rtc.pc);
      const callLabel = inCall ? 'End Call' : 'Call';
      const callClass = inCall ? 'danger' : '';

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
            <button class="btnCall ${callClass}" type="button" title="Voice">${callLabel}</button>
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
        state.participants.set(p.participant_id, {
          id: p.participant_id,
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
        const x = state.participants.get(p.participant_id);
        if(x){ x.mic_on = p.mic_on ? 1 : 0; scheduleRenderParticipants(); }
      }

      if(t === 'speaker_changed'){
        const x = state.participants.get(p.participant_id);
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

      if(t === 'material_changed'){
        refreshMaterial();
      }

      if(t === 'session_ended'){
        appendChat('System', 'Sesi ditutup.');
        hangup(false).catch(()=>{});
      }

      if(t === 'rtc_signal'){
        handleRtcSignal(p);
      }
    }
  }

  function handleSnapshot(snap){
    if(snap && Array.isArray(snap.participants)){
      for(const p of snap.participants){
        state.participants.set(p.id, p);
      }
      scheduleRenderParticipants();
    }

    if(snap && snap.state){
      state.broadcastText = snap.state.broadcast_text || '';
      if(broadcastInput) broadcastInput.value = state.broadcastText;
    }

    if(snap && snap.currentMaterial){
      renderMaterialBox(snap.currentMaterial);
    }
  }

  function handlePresence(list){
    state.presence.clear();
    for(const it of list){
      state.presence.set(it.id, !!it.online);
    }
    scheduleRenderParticipants();
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

      if(ev.target.classList.contains('btnCall')){
        startCall(pid).catch(err=>{
          appendChat('System', `Call error: ${err.message||err}`);
        });
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

  if(btnRefreshMaterial){
    btnRefreshMaterial.onclick = refreshMaterial;
  }

  // When leaving page, try to hangup cleanly (opsional tapi membantu)
  window.addEventListener('pagehide', ()=>{
    sendBeaconHangup();
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
  setCallStatus('Idle');
  refreshDevices();
  if(navigator.mediaDevices && navigator.mediaDevices.addEventListener){
    navigator.mediaDevices.addEventListener('devicechange', refreshDevices);
  }

})();
